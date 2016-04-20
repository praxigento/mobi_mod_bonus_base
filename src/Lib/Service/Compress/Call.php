<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Compress;


use Praxigento\Bonus\Base\Lib\Service\ICompress;
use Praxigento\Core\Service\Base\Call as BaseCall;
use Praxigento\Downline\Data\Entity\Customer;
use Praxigento\Downline\Data\Entity\Snap;
use Praxigento\Downline\Lib\Service\Map\Request\ById as DownlineMapByIdRequest;
use Praxigento\Downline\Lib\Service\Map\Request\TreeByDepth as DownlineMapTreeByDepthRequest;
use Praxigento\Downline\Lib\Service\Map\Request\TreeByTeams as DownlineMapTreeByTeamsRequest;
use Praxigento\Downline\Lib\Service\Snap\Request\ExpandMinimal as DownlineSnapExtendMinimalRequest;

class Call extends BaseCall implements ICompress
{
    /** @var  \Praxigento\Downline\Lib\Service\IMap */
    protected $_callDownlineMap;
    /** @var   \Praxigento\Downline\Lib\Service\ISnap */
    protected $_callDownlineSnap;
    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;
    /** @var  \Praxigento\Bonus\Base\Lib\Repo\IModule */
    protected $_repoMod;
    /** @var  \Praxigento\Downline\Lib\Tool\ITree */
    protected $_toolDownlineTree;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Praxigento\Bonus\Base\Lib\Repo\IModule $repoMod,
        \Praxigento\Downline\Lib\Service\IMap $repoDownlineMap,
        \Praxigento\Downline\Lib\Service\ISnap $callDownlineSnap,
        \Praxigento\Downline\Lib\Tool\ITree $toolDownlineTree
    ) {
        $this->_logger = $logger;
        $this->_repoMod = $repoMod;
        $this->_callDownlineMap = $repoDownlineMap;
        $this->_callDownlineSnap = $callDownlineSnap;
        $this->_toolDownlineTree = $toolDownlineTree;
    }

    /**
     * Populate data with depth & path values.
     *
     * @param $data array of customer data with customer ID & parent ID
     * @param $labelCustomerId string label for customerId
     * @param $labelParentId string label for parentId
     *
     * @return array Downline Snap: [ $custId => [customer_id, depth, parent_id, path], ...]
     */
    private function _expandTree($data)
    {
        $req = new DownlineSnapExtendMinimalRequest();
        $req->setKeyParentId(Snap::ATTR_PARENT_ID);
        $req->setTree($data);
        $resp = $this->_callDownlineSnap->expandMinimal($req);
        return $resp->getSnapData();
    }

    private function _mapById($tree)
    {
        $req = new DownlineMapByIdRequest();
        $req->setDataToMap($tree);
        $req->setAsId(Snap::ATTR_CUSTOMER_ID);
        $resp = $this->_callDownlineMap->byId($req);
        return $resp->getMapped();
    }

    private function _mapByTeams($tree)
    {
        $req = new DownlineMapTreeByTeamsRequest();
        $req->setAsCustomerId(Snap::ATTR_CUSTOMER_ID);
        $req->setAsParentId(Snap::ATTR_PARENT_ID);
        $req->setDataToMap($tree);
        $resp = $this->_callDownlineMap->treeByTeams($req);
        return $resp->getMapped();
    }

    private function _mapByTreeDepthDesc($tree)
    {
        $req = new DownlineMapTreeByDepthRequest();
        $req->setDataToMap($tree);
        $req->setAsCustomerId(Snap::ATTR_CUSTOMER_ID);
        $req->setAsDepth(Snap::ATTR_DEPTH);
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
        $this->_logger->info("'QualifyByUserData' operation is started.");
        $treeCompressed = [];
        if ($skipExpand) {
            $treeExpanded = $treeFlat;
        } else {
            $treeExpanded = $this->_expandTree($treeFlat);
        }
        $mapById = $this->_mapById($treeExpanded);
        $mapDepth = $this->_mapByTreeDepthDesc($treeExpanded);
        $mapTeams = $this->_mapByTeams($treeExpanded);

        foreach ($mapDepth as $depth => $levelCustomers) {
            foreach ($levelCustomers as $custId) {
                $custData = $mapById[$custId];
                $ref = isset($custData[Customer::ATTR_HUMAN_REF]) ? $custData[Customer::ATTR_HUMAN_REF] : '';
                if ($qualifier->isQualified($custData)) {
                    $this->_logger->info("Customer #$custId ($ref) is qualified and added to compressed tree..");
                    $treeCompressed[$custId] = $custData;
                } else {
                    $this->_logger->info("Customer #$custId ($ref) is not qualified.");
                    if (isset($mapTeams[$custId])) {
                        $this->_logger->info("Customer #$custId ($ref) has own front team.");
                        /* Lookup for the closest qualified parent */
                        $path = $treeExpanded[$custId][Snap::ATTR_PATH];
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
                                $treeCompressed[$memberId][Snap::ATTR_PARENT_ID] = is_null($foundParentId)
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
        $this->_repoMod->saveCompressedTree($calcId, $treeCompressed);
        $result->markSucceed();
        $this->_logger->info("'QualifyByUserData' operation is completed.");
        return $result;
    }
}