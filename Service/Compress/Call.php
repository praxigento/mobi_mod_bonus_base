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
    extends \Praxigento\Core\App\Service\Base\Call
    implements \Praxigento\BonusBase\Service\ICompress
{
    /** @var  \Praxigento\Downline\Service\IMap */
    protected $callDownlineMap;
    /** @var   \Praxigento\Downline\Service\ISnap */
    protected $callDownlineSnap;
    /** @var  \Praxigento\Downline\Api\Helper\Downline */
    protected $hlpDownlineTree;
    /** @var \Praxigento\Core\Api\App\Logger\Main */
    protected $logger;
    /** @var  \Praxigento\Core\Api\App\Repo\Transaction\Manager */
    protected $manTrans;
    /** @var \Praxigento\BonusBase\Repo\Dao\Compress */
    protected $daoBonusCompress;

    public function __construct(
        \Praxigento\Core\Api\App\Logger\Main $logger,
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Api\App\Repo\Transaction\Manager $manTrans,
        \Praxigento\BonusBase\Repo\Dao\Compress $daoBonusCompress,
        \Praxigento\Downline\Service\IMap $daoDownlineMap,
        \Praxigento\Downline\Service\ISnap $callDownlineSnap,
        \Praxigento\Downline\Api\Helper\Downline $hlpDownlineTree
    ) {
        parent::__construct($logger, $manObj);
        $this->manTrans = $manTrans;
        $this->daoBonusCompress = $daoBonusCompress;
        $this->callDownlineMap = $daoDownlineMap;
        $this->callDownlineSnap = $callDownlineSnap;
        $this->hlpDownlineTree = $hlpDownlineTree;
    }

    private function _mapById($tree)
    {
        $req = new DownlineMapByIdRequest();
        $req->setDataToMap($tree);
        $req->setAsId(ESnap::A_CUSTOMER_ID);
        $resp = $this->callDownlineMap->byId($req);
        return $resp->getMapped();
    }

    private function _mapByTeams($tree)
    {
        $req = new DownlineMapTreeByTeamsRequest();
        $req->setAsCustomerId(ESnap::A_CUSTOMER_ID);
        $req->setAsParentId(ESnap::A_PARENT_ID);
        $req->setDataToMap($tree);
        $resp = $this->callDownlineMap->treeByTeams($req);
        return $resp->getMapped();
    }

    private function _mapByTreeDepthDesc($tree)
    {
        $req = new DownlineMapTreeByDepthRequest();
        $req->setDataToMap($tree);
        $req->setAsCustomerId(ESnap::A_CUSTOMER_ID);
        $req->setAsDepth(ESnap::A_DEPTH);
        $req->setShouldReversed(true);
        $resp = $this->callDownlineMap->treeByDepth($req);
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
            $treeExpanded = $this->hlpDownlineTree->expandMinimal($treeFlat, ESnap::A_PARENT_ID);
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
                        $parents = $this->hlpDownlineTree->getParentsFromPathReversed($path);
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