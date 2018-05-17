<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Compress;

use Praxigento\BonusBase\Repo\Data\Compress as ECompress;
use Praxigento\Downline\Repo\Data\Customer;
use Praxigento\Downline\Repo\Data\Snap as ESnap;
use Praxigento\Downline\Service\Map\Request\ById as DownlineMapByIdRequest;
use Praxigento\Downline\Service\Map\Request\TreeByDepth as DownlineMapTreeByDepthRequest;
use Praxigento\Downline\Service\Map\Request\TreeByTeams as DownlineMapTreeByTeamsRequest;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Call
    implements \Praxigento\BonusBase\Service\ICompress
{
    /** @var  \Praxigento\Downline\Service\IMap */
    private $daoDownlineMap;
    /** @var   \Praxigento\Downline\Service\ISnap */
    private $servDownlineSnap;
    /** @var  \Praxigento\Downline\Api\Helper\Tree */
    private $hlpTree;
    /** @var \Praxigento\Core\Api\App\Logger\Main */
    private $logger;
    /** @var  \Praxigento\Core\Api\App\Repo\Transaction\Manager */
    private $manTrans;
    /** @var \Praxigento\BonusBase\Repo\Dao\Compress */
    private $daoBonusCompress;

    public function __construct(
        \Praxigento\Core\Api\App\Logger\Main $logger,
        \Praxigento\Core\Api\App\Repo\Transaction\Manager $manTrans,
        \Praxigento\BonusBase\Repo\Dao\Compress $daoBonusCompress,
        \Praxigento\Downline\Service\IMap $daoDownlineMap,
        \Praxigento\Downline\Service\ISnap $servDownlineSnap,
        \Praxigento\Downline\Api\Helper\Tree $hlpTree
    ) {
        $this->logger = $logger;
        $this->manTrans = $manTrans;
        $this->daoBonusCompress = $daoBonusCompress;
        $this->daoDownlineMap = $daoDownlineMap;
        $this->servDownlineSnap = $servDownlineSnap;
        $this->hlpTree = $hlpTree;
    }

    private function _mapById($tree)
    {
        $req = new DownlineMapByIdRequest();
        $req->setDataToMap($tree);
        $req->setAsId(ESnap::A_CUSTOMER_ID);
        $resp = $this->daoDownlineMap->byId($req);
        return $resp->getMapped();
    }

    private function _mapByTeams($tree)
    {
        $req = new DownlineMapTreeByTeamsRequest();
        $req->setAsCustomerId(ESnap::A_CUSTOMER_ID);
        $req->setAsParentId(ESnap::A_PARENT_ID);
        $req->setDataToMap($tree);
        $resp = $this->daoDownlineMap->treeByTeams($req);
        return $resp->getMapped();
    }

    private function _mapByTreeDepthDesc($tree)
    {
        $req = new DownlineMapTreeByDepthRequest();
        $req->setDataToMap($tree);
        $req->setAsCustomerId(ESnap::A_CUSTOMER_ID);
        $req->setAsDepth(ESnap::A_DEPTH);
        $req->setShouldReversed(true);
        $resp = $this->daoDownlineMap->treeByDepth($req);
        return $resp->getMapped();
    }

    /**
     * @param Request\QualifyByUserData $req
     *
     * @return Response\QualifyByUserData
     */
    public function qualifyByUserData(Request\QualifyByUserData $req)
    {
        $result = new Response\QualifyByUserData();
        /* parse request */
        $calcId = $req->getCalcId();
        $treeFlat = $req->getFlatTree();
        $qualifier = $req->getQualifier();
        $skipExpand = (bool)$req->getSkipTreeExpand();
        $this->logger->info("'QualifyByUserData' operation is started.");
        $treeCompressed = [];
        if ($skipExpand) {
            $treeExpanded = $treeFlat;
        } else {
            $treeExpanded = $this->hlpTree->expandMinimal($treeFlat, ESnap::A_PARENT_ID);
        }
        $mapById = $this->_mapById($treeExpanded);
        $mapDepth = $this->_mapByTreeDepthDesc($treeExpanded);
        $mapTeams = $this->_mapByTeams($treeExpanded);

        foreach ($mapDepth as $depth => $levelCustomers) {
            foreach ($levelCustomers as $custId) {
                $custData = $mapById[$custId];
                $ref = isset($custData[Customer::A_MLM_ID]) ? $custData[Customer::A_MLM_ID] : '';
                if ($qualifier->isQualified($custData)) {
                    $this->logger->info("Customer #$custId ($ref) is qualified and added to compressed tree.");
                    $treeCompressed[$custId] = $custData;
                } else {
                    $this->logger->info("Customer #$custId ($ref) is not qualified.");
                    if (isset($mapTeams[$custId])) {
                        $this->logger->info("Customer #$custId ($ref) has own front team.");
                        /* Lookup for the closest qualified parent */
                        $path = $treeExpanded[$custId][ESnap::A_PATH];
                        $parents = $this->hlpTree->getParentsFromPathReversed($path);
                        $foundParentId = null;
                        foreach ($parents as $newParentId) {
                            $parentData = $mapById[$newParentId];
                            if ($qualifier->isQualified($parentData)) {
                                $foundParentId = $newParentId;
                                break;
                            }
                        }
                        /* Change parent for all siblings of the unqualified customer. */
                        $team = $mapTeams[$custId];
                        foreach ($team as $memberId) {
                            if (isset($treeCompressed[$memberId])) {
                                /* if null set customer own id to indicate root node */
                                $treeCompressed[$memberId][ESnap::A_PARENT_ID] = is_null($foundParentId)
                                    ? $memberId
                                    : $foundParentId;
                            }
                        }
                    }
                }
            }
        }
        unset($mapCustomer);
        unset($mapPv);
        unset($mapDepth);
        unset($mapTeams);

        /* save compressed tree */
        $def = $this->manTrans->begin();
        try {
            foreach ($treeCompressed as $custId => $item) {
                $data = [
                    ECompress::A_CALC_ID => $calcId,
                    ECompress::A_CUSTOMER_ID => $custId,
                    ECompress::A_PARENT_ID => $item[ESnap::A_PARENT_ID]
                ];
                $this->daoBonusCompress->create($data);
            }
            $this->manTrans->commit($def);
        } finally {
            $this->manTrans->end($def);
        }

        $result->markSucceed();
        $this->logger->info("'QualifyByUserData' operation is completed.");
        return $result;
    }
}