<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Bonus\Base\Lib\Repo\Def;

use Praxigento\Bonus\Base\Lib\Entity\Calculation;
use Praxigento\Bonus\Base\Lib\Entity\Cfg\Generation as CfgGeneration;
use Praxigento\Bonus\Base\Lib\Entity\Period;
use Praxigento\Core\Data\Entity\Type\Base as TypeBase;
use Praxigento\Downline\Data\Entity\Snap;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Module_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase
{
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mDba;
    /** @var  \Mockery\MockInterface */
    private $mRepoGeneric;
    /** @var  \Mockery\MockInterface */
    private $mToolDate;
    /** @var  Module */
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped('Test is deprecated after M1 & M2 merge is done.');
        $this->mConn = $this->_mockDba();
        $this->mDba = $this->_mockResourceConnection($this->mConn);
        $this->mRepoGeneric = $this->_mockRepoGeneric($this->mDba);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $this->repo = new Module(
            $this->mRepoGeneric,
            $this->mToolDate
        );
    }

    public function test_addLogSaleOrder()
    {
        /** === Test Data === */
        $TRAN_ID = 2;
        $SALE_ORDER_ID = 43;

        /** === Setup Mocks === */
        $this->mRepoGeneric->shouldReceive('addEntity');

        /** === Call and asserts  === */
        $this->repo->addLogSaleOrder($TRAN_ID, $SALE_ORDER_ID);
    }

    public function test_addPeriod_commit()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $DS_BEGIN = '20160304';
        $DS_END = '20160308';
        $CALC_ID = 23;
        $PERIOD_ID = 32;
        $DATE_STARTED = '2012-08-23 20:32:43';

        /** === Setup Mocks === */
        // $this->_getConn()->beginTransaction();
        $this->mConn->shouldReceive('beginTransaction');
        // $periodId = $this->_repoBasic->addEntity(Period::ENTITY_NAME, $periodData);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once()
            ->andReturn($PERIOD_ID);
        // $dateStarted = $this->_toolDate->getUtcNowForDb();
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')
            ->andReturn($DATE_STARTED);
        // $calcId = $this->_repoBasic->addEntity(Calculation::ENTITY_NAME, $calcData);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once()
            ->andReturn($CALC_ID);
        // $this->_getConn()->commit();
        $this->mConn->shouldReceive('commit');

        /** === Call and asserts  === */
        $this->repo->addPeriod($CALC_TYPE_ID, $DS_BEGIN, $DS_END);
    }

    /**
     * @expectedException \Exception
     */
    public function test_addPeriod_exception()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $DS_BEGIN = '20160304';
        $DS_END = '20160308';

        /** === Setup Mocks === */
        // $this->_getConn()->beginTransaction();
        $this->mConn->shouldReceive('beginTransaction');
        // $periodId = $this->_repoBasic->addEntity(Period::ENTITY_NAME, $periodData);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once()
            ->andThrow(new \Exception());
        // $this->_getConn()->rollBack();
        $this->mConn->shouldReceive('rollBack');

        /** === Call and asserts  === */
        $this->repo->addPeriod($CALC_TYPE_ID, $DS_BEGIN, $DS_END);
    }


    public function test_getCalcsForPeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $DS_BEGIN = '20160304';
        $DS_END = '20160308';
        $AS_ID = 'id';
        $CALC_ID = 23;
        $SHOULD_GET_LATEST = true;
        $CALCS_FOUND = [[$AS_ID => $CALC_ID]];

        /** === Setup Mocks === */
        // $tblPeriod = $this->_getTableName(Period::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $conn->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from([ $asPeriod => $tblPeriod ], [ ]);
        $mQuery->shouldReceive('from');
        // $query->join([ $asCalc => $tblCalc ], $on, $cols);
        $mQuery->shouldReceive('joinLeft');
        // $query->limit(1);
        $mQuery->shouldReceive('limit')->with(1);
        // $conn->quote(...);
        $this->mConn->shouldReceive('quote');
        // $query->where("$whereTypeId AND $whereFrom AND $whereTo");
        $mQuery->shouldReceive('where');
        // $result = $conn->fetchAll($query);
        $this->mConn
            ->shouldReceive('fetchAll')
            ->andReturn($CALCS_FOUND);

        /** === Call and asserts  === */
        $resp = $this->repo->getCalcsForPeriod($CALC_TYPE_ID, $DS_BEGIN, $DS_END, $SHOULD_GET_LATEST);
        $this->assertTrue(is_array($resp));
        $this->assertEquals($CALC_ID, $resp[$AS_ID]);
    }

    public function test_getCompressedTree()
    {
        /** === Test Data === */
        $CALC_ID = 2;

        /** === Setup Mocks === */
        $this->mRepoGeneric->shouldReceive('getEntities');

        /** === Call and asserts  === */
        $this->repo->getCompressedTree($CALC_ID);
    }

    public function test_getConfigGenerationsPercents()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $RANK_ID = 3;
        $GEN = 1;
        $PERCENT = 0.12;
        $FOUND = [
            [
                CfgGeneration::ATTR_RANK_ID => $RANK_ID,
                CfgGeneration::ATTR_GENERATION => $GEN,
                CfgGeneration::ATTR_PERCENT => $PERCENT
            ]
        ];

        /** === Setup Mocks === */
        $this->mRepoGeneric
            ->shouldReceive('getEntities')
            ->andReturn($FOUND);

        /** === Call and asserts  === */
        $resp = $this->repo->getConfigGenerationsPercents($CALC_TYPE_ID);
        $this->assertEquals($PERCENT, $resp[$RANK_ID][$GEN]);
    }

    public function test_getFirstDateForPvTransactions()
    {
        /** === Test Data === */
        $DATE = 'date';

        /** === Setup Mocks === */
        //$tblAcc = $this->_getTableName(Account::ENTITY_NAME);
        //$tblTrans = $this->_getTableName(Transaction::ENTITY_NAME);
        //$tblType = $this->_getTableName(TypeAsset::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $this->_getConn()->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from(...);
        $mQuery->shouldReceive('from');
        // $query->joinLeft(...);
        $mQuery->shouldReceive('joinLeft');
        // $where = $asType . '.' . TypeAsset::ATTR_CODE . '=' . $this->_getConn()->quote(Cfg::CODE_TYPE_ASSET_PV);
        $this->mConn->shouldReceive('quote');
        // $query->where(...);
        $mQuery->shouldReceive('where');
        // $query->order(...);
        $mQuery->shouldReceive('order');
        // $query->limit(...);
        $mQuery->shouldReceive('limit');
        // $result = $this->_getConn()->fetchOne($query);
        $this->mConn
            ->shouldReceive('fetchOne')
            ->andReturn($DATE);

        /** === Call and asserts  === */
        $resp = $this->repo->getFirstDateForPvTransactions();
        $this->assertEquals($DATE, $resp);
    }

    public function test_getLatestPeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $PERIOD_ID = 4;
        $CALC_ID = 8;
        $PERIOD_DATA = [
            [Period::ATTR_ID => $PERIOD_ID]
        ];
        $CALC_DATA = [
            [Calculation::ATTR_ID => $CALC_ID]
        ];

        /** === Setup Mocks === */
        //  $periodData = $this->_repoBasic->getEntities(Period::ENTITY_NAME, null, $wherePeriod, $orderPeriod, 1);
        $this->mRepoGeneric
            ->shouldReceive('getEntities')->once()
            ->andReturn($PERIOD_DATA);
        // $calcData = $this->_repoBasic->getEntities(Calculation::ENTITY_NAME, null, $where, $order, $limit);
        $this->mRepoGeneric
            ->shouldReceive('getEntities')->once()
            ->andReturn($CALC_DATA);
        /** === Call and asserts  === */
        $resp = $this->repo->getLatestPeriod($CALC_TYPE_ID);
        $periodData = $resp->getData(Module::A_PERIOD);
        $this->assertEquals($PERIOD_ID, $periodData[Period::ATTR_ID]);
        $calcData = $resp->getData(Module::A_CALC);
        $this->assertEquals($CALC_ID, $calcData[Calculation::ATTR_ID]);
    }

    public function test_getRankIdByCode()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $ID = 8;
        $DATA = [
            TypeBase::ATTR_ID => $ID
        ];

        /** === Setup Mocks === */
        // $tbl = $this->_getTableName(Rank::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $this->_getConn()->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from(...);
        $mQuery->shouldReceive('from');
        // $query->where(...);
        $mQuery->shouldReceive('where');
        // $data = $this->_getConn()->fetchRow($query, [ 'code' => $calcTypeCode ]);
        $this->mConn
            ->shouldReceive('fetchRow')
            ->andReturn($DATA);

        /** === Call and asserts  === */
        $resp = $this->repo->getRankIdByCode($CALC_TYPE_CODE);
        $this->assertEquals($ID, $resp);
    }

    public function test_getTypeAssetIdByCode()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $ID = 8;
        $DATA = [
            TypeBase::ATTR_ID => $ID
        ];

        /** === Setup Mocks === */
        // $tbl = $this->_getTableName(Rank::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $this->_getConn()->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from(...);
        $mQuery->shouldReceive('from');
        // $query->where(...);
        $mQuery->shouldReceive('where');
        // $data = $this->_getConn()->fetchRow($query, [ 'code' => $calcTypeCode ]);
        $this->mConn
            ->shouldReceive('fetchRow')
            ->andReturn($DATA);

        /** === Call and asserts  === */
        $resp = $this->repo->getTypeAssetIdByCode($CALC_TYPE_CODE);
        $this->assertEquals($ID, $resp);
    }

    public function test_getTypeCalcIdByCode()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $ID = 8;
        $DATA = [
            TypeBase::ATTR_ID => $ID
        ];

        /** === Setup Mocks === */
        // $tbl = $this->_getTableName(Rank::ENTITY_NAME);
        $this->mDba->shouldReceive('getTableName');
        // $query = $this->_getConn()->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')
            ->andReturn($mQuery);
        // $query->from(...);
        $mQuery->shouldReceive('from');
        // $query->where(...);
        $mQuery->shouldReceive('where');
        // $data = $this->_getConn()->fetchRow($query, [ 'code' => $calcTypeCode ]);
        $this->mConn
            ->shouldReceive('fetchRow')
            ->andReturn($DATA);

        /** === Call and asserts  === */
        $resp = $this->repo->getTypeCalcIdByCode($CALC_TYPE_CODE);
        $this->assertEquals($ID, $resp);
    }

    public function test_logRank()
    {
        /** === Test Data === */
        $TRAN_REF = 21;
        $RANK_REF = 12;

        /** === Setup Mocks === */
        // $this->_repoBasic->addEntity(LogRank::ENTITY_NAME, $bind);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once();

        /** === Call and asserts  === */
        $this->repo->logRank($TRAN_REF, $RANK_REF);
    }

    public function test_saveCompressedTree_commit()
    {
        /** === Test Data === */
        $CALC_ID = 21;
        $TREE = [
            [Snap::ATTR_CUSTOMER_ID => 1, Snap::ATTR_PARENT_ID => 2]
        ];

        /** === Setup Mocks === */
        // $this->_getConn()->beginTransaction();
        $this->mConn->shouldReceive('beginTransaction');
        // $this->_repoBasic->addEntity(Compress::ENTITY_NAME, $bind);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once();
        // $this->_getConn()->commit();
        $this->mConn->shouldReceive('commit');

        /** === Call and asserts  === */
        $this->repo->saveCompressedTree($CALC_ID, $TREE);
    }

    /**
     * @expectedException  \Exception
     */
    public function test_saveCompressedTree_exception()
    {
        /** === Test Data === */
        $CALC_ID = 21;
        $TREE = [
            [Snap::ATTR_CUSTOMER_ID => 1, Snap::ATTR_PARENT_ID => 2]
        ];

        /** === Setup Mocks === */
        // $this->_getConn()->beginTransaction();
        $this->mConn->shouldReceive('beginTransaction');
        // $this->_repoBasic->addEntity(Compress::ENTITY_NAME, $bind);
        $this->mRepoGeneric
            ->shouldReceive('addEntity')->once()
            ->andThrow(new \Exception());
        // $this->_getConn()->rollBack();
        $this->mConn->shouldReceive('rollBack');

        /** === Call and asserts  === */
        $this->repo->saveCompressedTree($CALC_ID, $TREE);
    }

    public function test_updateCalcSetComplete()
    {
        /** === Test Data === */
        $CALC_ID = 21;
        $RESULT = 1;

        /** === Setup Mocks === */
        // $tsEnded = $this->_toolDate->getUtcNowForDb();
        $this->mToolDate->shouldReceive('getUtcNowForDb');
        // $result = $this->_repoBasic->updateEntity(Calculation::ENTITY_NAME, $bind, $where);
        $this->mRepoGeneric
            ->shouldReceive('updateEntity')
            ->andReturn($RESULT);

        /** === Call and asserts  === */
        $resp = $this->repo->updateCalcSetComplete($CALC_ID);
        $this->assertEquals($RESULT, $resp);
    }


}