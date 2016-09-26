<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service\Def;

use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Module_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo
{
    /** @var  \Mockery\MockInterface */
    private $mRepoCalc;
    /** @var  \Mockery\MockInterface */
    private $mRepoPeriod;
    /** @var  \Mockery\MockInterface */
    private $mToolDate;
    /** @var  Module */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mRepoCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\ICalculation::class);
        $this->mRepoPeriod = $this->_mock(\Praxigento\BonusBase\Repo\Entity\IPeriod::class);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        /** create object to test */
        $this->obj = new Module(
            $this->mResource,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mToolDate
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(\Praxigento\BonusBase\Repo\Service\IModule::class, $this->obj);
    }

    public function test_getFirstDateForPvTransactions()
    {
        /** === Test Data === */
        $RESULT = 'result';
        /** === Setup Mocks === */
        // $tblAcc = $this->_resource->getTableName(EAccount::ENTITY_NAME);
        // $tblTrans = $this->_resource->getTableName(ETransaction::ENTITY_NAME);
        // $tblType = $this->_resource->getTableName(ETypeAsset::ENTITY_NAME);
        $this->mResource
            ->shouldReceive('getTableName')->times(3);
        // $query = $this->_conn->select();
        $mQuery = $this->_mockDbSelect(['from', 'joinLeft', 'where', 'order', 'limit']);
        $this->mConn
            ->shouldReceive('select')->once()
            ->andReturn($mQuery);
        // $where = $asType . '.' . ETypeAsset::ATTR_CODE . '=' . $this->_conn->quote(Cfg::CODE_TYPE_ASSET_PV);
        $this->mConn
            ->shouldReceive('quote')->once();
        // $result = $this->_conn->fetchOne($query);
        $this->mConn
            ->shouldReceive('fetchOne')->once()
            ->andReturn($RESULT);
        /** === Call and asserts  === */
        $res = $this->obj->getFirstDateForPvTransactions();
        $this->assertEquals($RESULT, $res);
    }

    public function test_getLastCalcForPeriodByDates()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 4;
        $DS_BEGIN = 'begin';
        $DS_END = 'end';
        /** === Setup Mocks === */
        // $tblPeriod = $this->_resource->getTableName(EPeriod::ENTITY_NAME);
        $this->mResource
            ->shouldReceive('getTableName')->twice();
        // $query = $this->_conn->select();
        $mQuery = $this->_mockDbSelect(['from', 'joinLeft', 'where', 'order', 'limit']);
        $this->mConn
            ->shouldReceive('select')->once()
            ->andReturn($mQuery);
        // $whereFrom = $asPeriod . '.' . EPeriod::ATTR_DSTAMP_BEGIN . '=' . $conn->quote($dsBegin);
        $this->mConn
            ->shouldReceive('quote')->twice();
        // $rs = $conn->fetchAll($query);
        $this->mConn
            ->shouldReceive('fetchAll')->once()
            ->andReturn([['data']]);
        /** === Call and asserts  === */
        $res = $this->obj->getLastCalcForPeriodByDates($CALC_TYPE_ID, $DS_BEGIN, $DS_END);
        $this->assertInstanceOf(ECalculation::class, $res);
    }

    public function test_getLastCalcForPeriodById()
    {
        /** === Test Data === */
        $PERIOD_ID = 4;
        $RS = [[]];
        /** === Setup Mocks === */
        // $rs = $this->_repoCalc->get($where, $order, $limit);
        $this->mRepoCalc
            ->shouldReceive('get')->once()
            ->andReturn($RS);
        /** === Call and asserts  === */
        $res = $this->obj->getLastCalcForPeriodById($PERIOD_ID);
        $this->assertInstanceOf(ECalculation::class, $res);
    }

    public function test_getLastPeriodByCalcType()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 8;
        $RS = [[]];
        /** === Setup Mocks === */
        // $rs = $this->_repoPeriod->get($where, $order, 1);
        $this->mRepoPeriod
            ->shouldReceive('get')->once()
            ->andReturn($RS);
        /** === Call and asserts  === */
        $res = $this->obj->getLastPeriodByCalcType($CALC_TYPE_ID);
        $this->assertInstanceOf(EPeriod::class, $res);
    }

    public function test_markCalcComplete()
    {
        /** === Test Data === */
        $CALC_ID = 4;
        /** === Setup Mocks === */
        // $tsEnded = $this->_toolDate->getUtcNowForDb();
        $mTsEnded = 'now';
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')->once()
            ->andReturn($mTsEnded);
        // $result = $this->_repoCalc->update($bind, $where);
        $mResult = 1;
        $this->mRepoCalc
            ->shouldReceive('update')->once()
            ->andReturn($mResult);
        /** === Call and asserts  === */
        $res = $this->obj->markCalcComplete($CALC_ID);
        $this->assertEquals($mResult, $res);
    }
}