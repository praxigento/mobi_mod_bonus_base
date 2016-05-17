<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Compress;

use Flancer32\Lib\DataObject;
use Praxigento\Downline\Data\Entity\Customer;
use Praxigento\Downline\Data\Entity\Snap;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Call_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase {
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mDba;
    /** @var  \Mockery\MockInterface */
    private $mRepoGeneric;
    /** @var  \Mockery\MockInterface */
    private $mCallDownlineMap;
    /** @var  \Mockery\MockInterface */
    private $mCallDownlineSnap;
    /** @var  \Mockery\MockInterface */
    private $mLogger;
    /** @var  \Mockery\MockInterface */
    private $mRepoMod;
    /** @var  \Mockery\MockInterface */
    private $mToolDownlineTree;
    /** @var  Call */
    private $call;

    protected function setUp() {
        parent::setUp();
        $this->mConn = $this->_mockDba();
        $this->mDba = $this->_mockResourceConnection($this->mConn);
        $this->mRepoGeneric = $this->_mockRepoGeneric($this->mDba);
        $this->mRepoMod = $this->_mock(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $this->mLogger = $this->_mockLogger();
        $this->mCallDownlineMap = $this->_mock(\Praxigento\Downline\Lib\Service\IMap::class);
        $this->mCallDownlineSnap = $this->_mock(\Praxigento\Downline\Lib\Service\ISnap::class);
        $this->mToolDownlineTree = $this->_mock(\Praxigento\Downline\Tool\ITree::class);
        $this->call = new Call(
            $this->mLogger,
            $this->mRepoMod,
            $this->mCallDownlineMap,
            $this->mCallDownlineSnap,
            $this->mToolDownlineTree
        );
    }


    public function test_qualifyByUserData() {
        /** === Test Data === */
        /**
         * Test data is not real customer tree, just for code coverage.
         */
        $CALC_ID = 2;
        $TREE = [ ];
        $SHOULD_SKIP_EXPAND = false;
        $DEPTH_4 = 4;
        $CUST_4 = 4;
        $CUST_3 = 3;
        $CUST_2 = 2;
        $CUST_1 = 1;

        /** === Setup Mocks === */
        $mQualifier = $this->_mock(\Praxigento\Bonus\Base\Lib\Tool\IQualifyUser::class);
        $this->mLogger->shouldReceive('info');
        // $treeExpanded = $this->_expandTree($treeFlat);
        // $resp = $this->_callDownlineSnap->expandMinimal($req);
        $mRespExp = new DataObject();
        $this->mCallDownlineSnap
            ->shouldReceive('expandMinimal')
            ->andReturn($mRespExp);
        // return $resp->getSnapData();
        $mSanpData = [
            $CUST_1 => [ Snap::ATTR_PATH => '/' ],
            $CUST_2 => [ Snap::ATTR_PATH => '/1/' ],
            $CUST_3 => [ Snap::ATTR_PATH => '/1/2/' ],
            $CUST_4 => [ Snap::ATTR_PATH => '/1/2/3/' ]
        ];
        $mRespExp->setSnapData($mSanpData);
        // $mapById = $this->_mapById($treeExpanded);
        // $resp = $this->_callDownlineMap->byId($req);
        $mRespById = new DataObject([ $CUST_1 => [ ] ]);
        $this->mCallDownlineMap
            ->shouldReceive('byId')
            ->andReturn($mRespById);
        // return $resp->getMapped();
        $mMapById = [
            $CUST_4 => [
                Customer::ATTR_HUMAN_REF => 'ref4'
            ],
            $CUST_3 => [
                Customer::ATTR_HUMAN_REF => 'ref3'
            ],
            $CUST_2 => [
                Customer::ATTR_HUMAN_REF => 'ref2'
            ],
            $CUST_1 => [
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
            $DEPTH_4 => [ $CUST_4, $CUST_3, $CUST_2 ]
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
            $CUST_3 => [ $CUST_4 ],
            $CUST_2 => [ $CUST_4 ]
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
        $mParents = [ $CUST_1 ];
        $this->mToolDownlineTree
            ->shouldReceive('getParentsFromPathReversed')
            ->andReturn($mParents);
        // $this->_repoMod->saveCompressedTree($calcId, $treeCompressed);
        $this->mRepoMod
            ->shouldReceive('saveCompressedTree')->once();

        /** === Call and asserts  === */
        $req = new Request\QualifyByUserData();
        $req->setCalcId($CALC_ID);
        $req->setFlatTree($TREE);
        $req->setQualifier($mQualifier);
        $req->setSkipTreeExpand($SHOULD_SKIP_EXPAND);
        $resp = $this->call->qualifyByUserData($req);
        $this->assertTrue($resp->isSucceed());
    }

    /**
     * To cover $skipExpand
     * @expectedException \Exception
     */
    public function test_qualifyByUserData_skipExpand() {
        /** === Test Data === */
        /**
         * Test data is not real customer tree, just for code coverage.
         */

        /** === Setup Mocks === */
        $this->mLogger->shouldReceive('info');
        // $mapById = $this->_mapById($treeExpanded);
        // $resp = $this->_callDownlineMap->byId($req);
        $this->mCallDownlineMap->shouldReceive()->andThrow('Finita la comedia!');

        /** === Call and asserts  === */
        $req = new Request\QualifyByUserData();
        $req->setSkipTreeExpand(true);
        $resp = $this->call->qualifyByUserData($req);
    }

}