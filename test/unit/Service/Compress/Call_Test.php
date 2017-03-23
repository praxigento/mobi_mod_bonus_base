<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Compress;

use Flancer32\Lib\Data as DataObject;
use Praxigento\Downline\Data\Entity\Customer;
use Praxigento\Downline\Data\Entity\Snap;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Call_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Service\Call
{
    /** @var  Call */
    private $call;
    /** @var  \Mockery\MockInterface */
    private $mCallDownlineMap;
    /** @var  \Mockery\MockInterface */
    private $mCallDownlineSnap;
    /** @var  \Mockery\MockInterface */
    private $mManTrans;
    /** @var  \Mockery\MockInterface */
    private $mRepoBonusCompress;
    /** @var  \Mockery\MockInterface */
    private $mToolDownlineTree;

    protected function setUp()
    {
        parent::setUp();
        $this->mManTrans = $this->_mockTransactionManager();
        $this->mRepoBonusCompress = $this->_mock(\Praxigento\BonusBase\Repo\Entity\ICompress::class);
        $this->mCallDownlineMap = $this->_mock(\Praxigento\Downline\Service\IMap::class);
        $this->mCallDownlineSnap = $this->_mock(\Praxigento\Downline\Service\ISnap::class);
        $this->mToolDownlineTree = $this->_mock(\Praxigento\Downline\Tool\ITree::class);
        $this->call = new Call(
            $this->mLogger,
            $this->mManObj,
            $this->mManTrans,
            $this->mRepoBonusCompress,
            $this->mCallDownlineMap,
            $this->mCallDownlineSnap,
            $this->mToolDownlineTree
        );
    }


    public function test_qualifyByUserData()
    {
        /** === Test Data === */
        /**
         * Test data is not real customer tree, just for code coverage.
         */
        $calcId = 2;
        $tree = [];
        $skipExpand = false;
        $depth4 = 4;
        $cust4 = 4;
        $cust3 = 3;
        $cust2 = 2;
        $cust1 = 1;
        $treeExp = [
            $cust1 => [Snap::ATTR_PATH => 'path1'],
            $cust2 => [Snap::ATTR_PATH => 'path2'],
            $cust3 => [Snap::ATTR_PATH => 'path3'],
            $cust4 => [Snap::ATTR_PATH => 'path4']
        ];
        /** === Setup Mocks === */
        $mQualifier = $this->_mock(\Praxigento\BonusBase\Tool\IQualifyUser::class);
        $this->mLogger->shouldReceive('info');
        // $treeExpanded = $this->_toolDownlineTree->expandMinimal($treeFlat, Snap::ATTR_PARENT_ID);
        $this->mToolDownlineTree
            ->shouldReceive('expandMinimal')->once()
            ->andReturn($treeExp);
        // $resp = $this->_callDownlineSnap->expandMinimal($req);
        $mRespExp = new DataObject();
        $this->mCallDownlineSnap
            ->shouldReceive('expandMinimal')
            ->andReturn($mRespExp);
        // return $resp->getSnapData();
        $mSanpData = [
            $cust1 => [Snap::ATTR_PATH => '/'],
            $cust2 => [Snap::ATTR_PATH => '/1/'],
            $cust3 => [Snap::ATTR_PATH => '/1/2/'],
            $cust4 => [Snap::ATTR_PATH => '/1/2/3/']
        ];
        $mRespExp->setSnapData($mSanpData);
        // $mapById = $this->_mapById($treeExpanded);
        // $resp = $this->_callDownlineMap->byId($req);
        $mRespById = new DataObject([$cust1 => []]);
        $this->mCallDownlineMap
            ->shouldReceive('byId')
            ->andReturn($mRespById);
        // return $resp->getMapped();
        $mMapById = [
            $cust4 => [
                Customer::ATTR_HUMAN_REF => 'ref4'
            ],
            $cust3 => [
                Customer::ATTR_HUMAN_REF => 'ref3'
            ],
            $cust2 => [
                Customer::ATTR_HUMAN_REF => 'ref2'
            ],
            $cust1 => [
                Customer::ATTR_HUMAN_REF => 'ref1'
            ]
        ];
        $mRespById->setMapped($mMapById);
        // $mapDepth = $this->_mapByTreeDepthDesc($treeExpanded);
        // $resp = $this->_callDownlineMap->treeByDepth($req);
        $mRespByDepth = new DataObject();
        $this->mCallDownlineMap
            ->shouldReceive('treeByDepth')
            ->andReturn($mRespByDepth);
        // return $resp->getMapped();
        $mMapByDepth = [
            $depth4 => [$cust4, $cust3, $cust2]
        ];
        $mRespByDepth->setMapped($mMapByDepth);
        // $mapTeams = $this->_mapByTeams($treeExpanded);
        // $resp = $this->_callDownlineMap->treeByTeams($req);
        $mRespByTeams = new DataObject();
        $this->mCallDownlineMap
            ->shouldReceive('treeByTeams')
            ->andReturn($mRespByTeams);
        // return $resp->getMapped();
        $mMapByTeams = [
            $cust3 => [$cust4],
            $cust2 => [$cust4]
        ];
        $mRespByTeams->setMapped($mMapByTeams);
        // if($qualifier->isQualified($custData)) {
        $mQualifier
            ->shouldReceive('isQualified')->once()// 4
            ->andReturn(true);
        $mQualifier
            ->shouldReceive('isQualified')->once()// 3
            ->andReturn(false);
        $mQualifier
            ->shouldReceive('isQualified')->once()// 3 parent (1)
            ->andReturn(true);
        $mQualifier
            ->shouldReceive('isQualified')->once()// 2
            ->andReturn(false);
        $mQualifier
            ->shouldReceive('isQualified')->once()// 2 parent (1)
            ->andReturn(false);
        // $parents = $this->_toolDownlineTree->getParentsFromPathReversed($path);
        $mParents = [$cust1];
        $this->mToolDownlineTree
            ->shouldReceive('getParentsFromPathReversed')
            ->andReturn($mParents);
        // $def = $this->_manTrans->begin();
        $mDef = $this->_mockTransactionDefinition();
        $this->mManTrans
            ->shouldReceive('begin')->once()
            ->andReturn($mDef);
        // $this->_repoBonusCompress->create($data);
        $this->mRepoBonusCompress
            ->shouldReceive('create')->once();
        // $this->_manTrans->commit($def);
        $this->mManTrans
            ->shouldReceive('commit')->once()
            ->with($mDef);
        // $this->_manTrans->end($def);
        $this->mManTrans
            ->shouldReceive('end')->once()
            ->with($mDef);
        /** === Call and asserts  === */
        $req = new Request\QualifyByUserData();
        $req->setCalcId($calcId);
        $req->setFlatTree($tree);
        $req->setQualifier($mQualifier);
        $req->setSkipTreeExpand($skipExpand);
        $resp = $this->call->qualifyByUserData($req);
        $this->assertTrue($resp->isSucceed());
    }

    /**
     * To cover $skipExpand
     * @expectedException \Exception
     */
    public function test_qualifyByUserData_skipExpand()
    {
        /** === Test Data === */
        /**
         * Test data is not real customer tree, just for code coverage.
         */

        /** === Setup Mocks === */
        $this->mLogger->shouldReceive('info');
        // $mapById = $this->_mapById($treeExpanded);
        // $resp = $this->_callDownlineMap->byId($req);
        $this->mCallDownlineMap->shouldReceive('byId')->andThrow(new \Exception());

        /** === Call and asserts  === */
        $req = new Request\QualifyByUserData();
        $req->setSkipTreeExpand(true);
        $this->call->qualifyByUserData($req);
    }

}