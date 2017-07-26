<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Compress;

use Praxigento\BonusBase\Data\Entity\Compress as ECompress;
use Praxigento\Downline\Data\Entity\Customer;
use Praxigento\Downline\Data\Entity\Snap as ESnap;
use Praxigento\Downline\Service\Map\Request\ById as DownlineMapByIdRequest;
use Praxigento\Downline\Service\Map\Request\TreeByDepth as DownlineMapTreeByDepthRequest;
use Praxigento\Downline\Service\Map\Request\TreeByTeams as DownlineMapTreeByTeamsRequest;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Call
    extends \Praxigento\Core\Service\Base\Call
    implements \Praxigento\BonusBase\Service\ICompress
{
    /** @var  \Praxigento\Downline\Service\IMap */
    protected $_callDownlineMap;
    /** @var   \Praxigento\Downline\Service\ISnap */
    protected $_callDownlineSnap;
    /** @var  \Praxigento\Core\Transaction\Database\IManager */
    protected $_manTrans;
    /** @var \Praxigento\BonusBase\Repo\Entity\Def\Compress */
    protected $_repoBonusCompress;
    /** @var  \Praxigento\Downline\Tool\ITree */
    protected $_toolDownlineTree;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(
        \Praxigento\Core\Fw\Logger\App $logger,
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Transaction\Database\IManager $manTrans,
        \Praxigento\BonusBase\Repo\Entity\Def\Compress $repoBonusCompress,
        \Praxigento\Downline\Service\IMap $repoDownlineMap,
        \Praxigento\Downline\Service\ISnap $callDownlineSnap,
        \Praxigento\Downline\Tool\ITree $toolDownlineTree
    ) {
        parent::__construct($logger, $manObj);
        $this->_manTrans = $manTrans;
        $this->_repoBonusCompress = $repoBonusCompress;
        $this->_callDownlineMap = $repoDownlineMap;
        $this->_callDownlineSnap = $callDownlineSnap;
        $this->_toolDownlineTree = $toolDownlineTree;
    }

    private function _mapById($tree)
    {
        $req = new DownlineMapByIdRequest();
        $req->setDataToMap($tree);
        $req->setAsId(ESnap::ATTR_CUSTOMER_ID);
        $resp = $this->_callDownlineMap->byId($req);
        return $resp->getMapped();
    }

    private function _mapByTeams($tree)
    {
        $req = new DownlineMapTreeByTeamsRequest();
        $req->setAsCustomerId(ESnap::ATTR_CUSTOMER_ID);
        $req->setAsParentId(ESnap::ATTR_PARENT_ID);
        $req->setDataToMap($tree);
        $resp = $this->_callDownlineMap->treeByTeams($req);
        return $resp->getMapped();
    }

    private function _mapByTreeDepthDesc($tree)
    {
        $req = new DownlineMapTreeByDepthRequest();
        $req->setDataToMap($tree);
        $req->setAsCustomerId(ESnap::ATTR_CUSTOMER_ID);
        $req->setAsDepth(ESnap::ATTR_DEPTH);
        $req->setShouldReversed(true);
        $resp = $this->_callDownlineMap->treeByDepth($req);
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
            $treeExpanded = $this->_toolDownlineTree->expandMinimal($treeFlat, ESnap::ATTR_PARENT_ID);
        }
        $mapById = $this->_mapById($treeExpanded);
        $mapDepth = $this->_mapByTreeDepthDesc($treeExpanded);
        $mapTeams = $this->_mapByTeams($treeExpanded);

        foreach ($mapDepth as $depth => $levelCustomers) {
            foreach ($levelCustomers as $custId) {
                $custData = $mapById[$custId];
                $ref = isset($custData[Customer::ATTR_HUMAN_REF]) ? $custData[Customer::ATTR_HUMAN_REF] : '';
                if ($qualifier->isQualified($custData)) {
                    $this->logger->info("Customer #$custId ($ref) is qualified and added to compressed tree.");
                    $treeCompressed[$custId] = $custData;
                } else {
                    $this->logger->info("Customer #$custId ($ref) is not qualified.");
                    if (isset($mapTeams[$custId])) {
                        $this->logger->info("Customer #$custId ($ref) has own front team.");
                        /* Lookup for the closest qualified parent */
                        $path = $treeExpanded[$custId][ESnap::ATTR_PATH];
                        $parents = $this->_toolDownlineTree->getParentsFromPathReversed($path);
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
        $def = $this->_manTrans->begin();
        try {
            foreach ($treeCompressed as $custId => $item) {
                $data = [
                    ECompress::ATTR_CALC_ID => $calcId,
                    ECompress::ATTR_CUSTOMER_ID => $custId,
                    ECompress::ATTR_PARENT_ID => $item[ESnap::ATTR_PARENT_ID]
                ];
                $this->_repoBonusCompress->create($data);
            }
            $this->_manTrans->commit($def);
        } finally {
            $this->_manTrans->end($def);
        }

        $result->markSucceed();
        $this->logger->info("'QualifyByUserData' operation is completed.");
        return $result;
    }
}