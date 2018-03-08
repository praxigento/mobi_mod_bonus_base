<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Compress;

use Praxigento\BonusBase\Repo\Entity\Data\Compress as ECompress;
use Praxigento\Downline\Repo\Entity\Data\Customer;
use Praxigento\Downline\Repo\Entity\Data\Snap as ESnap;
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
    /** @var \Praxigento\Core\App\Api\Logger\Main */
    protected $logger;
    /** @var  \Praxigento\Core\App\Api\Repo\Transaction\Manager */
    protected $manTrans;
    /** @var \Praxigento\BonusBase\Repo\Entity\Compress */
    protected $repoBonusCompress;

    public function __construct(
        \Praxigento\Core\App\Api\Logger\Main $logger,
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\App\Api\Repo\Transaction\Manager $manTrans,
        \Praxigento\BonusBase\Repo\Entity\Compress $repoBonusCompress,
        \Praxigento\Downline\Service\IMap $repoDownlineMap,
        \Praxigento\Downline\Service\ISnap $callDownlineSnap,
        \Praxigento\Downline\Api\Helper\Downline $hlpDownlineTree
    ) {
        parent::__construct($logger, $manObj);
        $this->manTrans = $manTrans;
        $this->repoBonusCompress = $repoBonusCompress;
        $this->callDownlineMap = $repoDownlineMap;
        $this->callDownlineSnap = $callDownlineSnap;
        $this->hlpDownlineTree = $hlpDownlineTree;
    }

    private function _mapById($tree)
    {
        $req = new DownlineMapByIdRequest();
        $req->setDataToMap($tree);
        $req->setAsId(ESnap::ATTR_CUSTOMER_ID);
        $resp = $this->callDownlineMap->byId($req);
        return $resp->getMapped();
    }

    private function _mapByTeams($tree)
    {
        $req = new DownlineMapTreeByTeamsRequest();
        $req->setAsCustomerId(ESnap::ATTR_CUSTOMER_ID);
        $req->setAsParentId(ESnap::ATTR_PARENT_ID);
        $req->setDataToMap($tree);
        $resp = $this->callDownlineMap->treeByTeams($req);
        return $resp->getMapped();
    }

    private function _mapByTreeDepthDesc($tree)
    {
        $req = new DownlineMapTreeByDepthRequest();
        $req->setDataToMap($tree);
        $req->setAsCustomerId(ESnap::ATTR_CUSTOMER_ID);
        $req->setAsDepth(ESnap::ATTR_DEPTH);
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
            $treeExpanded = $this->hlpDownlineTree->expandMinimal($treeFlat, ESnap::ATTR_PARENT_ID);
        }
        $mapById = $this->_mapById($treeExpanded);
        $mapDepth = $this->_mapByTreeDepthDesc($treeExpanded);
        $mapTeams = $this->_mapByTeams($treeExpanded);

        foreach ($mapDepth as $depth => $levelCustomers) {
            foreach ($levelCustomers as $custId) {
                $custData = $mapById[$custId];
                $ref = isset($custData[Customer::ATTR_MLM_ID]) ? $custData[Customer::ATTR_MLM_ID] : '';
                if ($qualifier->isQualified($custData)) {
                    $this->logger->info("Customer #$custId ($ref) is qualified and added to compressed tree.");
                    $treeCompressed[$custId] = $custData;
                } else {
                    $this->logger->info("Customer #$custId ($ref) is not qualified.");
                    if (isset($mapTeams[$custId])) {
                        $this->logger->info("Customer #$custId ($ref) has own front team.");
                        /* Lookup for the closest qualified parent */
                        $path = $treeExpanded[$custId][ESnap::ATTR_PATH];
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
                                $treeCompressed[$memberId][ESnap::ATTR_PARENT_ID] = is_null($foundParentId)
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
                    ECompress::ATTR_CALC_ID => $calcId,
                    ECompress::ATTR_CUSTOMER_ID => $custId,
                    ECompress::ATTR_PARENT_ID => $item[ESnap::ATTR_PARENT_ID]
                ];
                $this->repoBonusCompress->create($data);
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