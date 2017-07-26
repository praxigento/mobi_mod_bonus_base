<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Sub;

use Praxigento\BonusBase\Config as Cfg;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class PvBased_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
{
    /** @var  \Mockery\MockInterface */
    private $mLogger;
    /** @var  \Mockery\MockInterface */
    private $mRepoCalc;
    /** @var  \Mockery\MockInterface */
    private $mRepoPeriod;
    /** @var  \Mockery\MockInterface */
    private $mRepoService;
    /** @var  \Mockery\MockInterface */
    private $mToolDate;
    /** @var  \Mockery\MockInterface */
    private $mToolPeriod;
    /** @var  PvBased */
    private $obj;
    /** @var array Constructor arguments for object mocking */
    private $objArgs = [];

    protected function setUp()
    {
        /** create mocks */
        parent::setUp();
        $this->mLogger = $this->_mockLogger();
        $this->mRepoCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Def\Calculation::class);
        $this->mRepoPeriod = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Def\Period::class);
        $this->mRepoService = $this->_mock(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $this->mToolPeriod = $this->_mock(\Praxigento\Core\Tool\IPeriod::class);
        /** reset args. to create mock of the tested object */
        $this->objArgs = [
            $this->mLogger,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoService,
            $this->mToolDate,
            $this->mToolPeriod
        ];
        /** create object to test */
        $this->obj = new PvBased(
            $this->mLogger,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoService,
            $this->mToolDate,
            $this->mToolPeriod
        );
    }

    public function test__checkStateForExistingPeriod_complete()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        $CALC = new \Praxigento\BonusBase\Data\Entity\Calculation();
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(PvBased::class . '[_registryNextPeriod]', $this->objArgs);
        /** === Setup Mocks === */
        // if ($calcData->getState() == Cfg::CALC_STATE_COMPLETE) {...}
        $CALC->setState(Cfg::CALC_STATE_COMPLETE);
        // $result = $this->_registryNextPeriod($result, $calcTypeId, $periodData);
        $this->obj
            ->shouldReceive('_registryNextPeriod')->once()
            ->with($RESULT, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD)
            ->andReturn($RESULT);
        /** === Call and asserts  === */
        $res = $this->obj->_checkStateForExistingPeriod($RESULT, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD, $CALC);
        $this->assertEquals($RESULT, $res);
    }

    public function test__checkStateForExistingPeriod_started()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        $CALC = new \Praxigento\BonusBase\Data\Entity\Calculation();
        /** === Setup Mocks === */
        // if ($calcData->getState() == Cfg::CALC_STATE_COMPLETE) {...}
        $CALC->setState(Cfg::CALC_STATE_STARTED);
        /** === Call and asserts  === */
        $res = $this->obj->_checkStateForExistingPeriod($RESULT, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD, $CALC);
        $this->assertEquals($CALC, $res->getCalcData());
    }

    public function test__registryNextPeriod_error()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        /** === Setup Mocks === */
        // $periodEnd = $periodData->getDstampEnd();
        $mPeriodEnd = 'end';
        $PERIOD->setDstampEnd($mPeriodEnd);
        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, $periodType);
        $mPeriodNext = 'next';
        $this->mToolPeriod
            ->shouldReceive('getPeriodNext')->once()
            ->with($mPeriodEnd, $PERIOD_TYPE)
            ->andReturn($mPeriodNext);
        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
        $mDsNextBegin = 'next begin';
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')->once()
            ->with($mPeriodNext)
            ->andReturn($mDsNextBegin);
        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
        $mDsNextEnd = '21000101';
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($mPeriodNext)
            ->andReturn($mDsNextEnd);
        // $periodNow = $this->_toolPeriod->getPeriodCurrentOld(time(), $periodType);
        $mPeriodNow = 'now';
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrentOld')->once()
            ->with(\Mockery::any(), $PERIOD_TYPE)
            ->andReturn($mPeriodNow);
        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
        $mDsNowEnd = '20161231';
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($mPeriodNow)
            ->andReturn($mDsNowEnd);
        /** === Call and asserts  === */
        $res = $this->obj->_registryNextPeriod($RESULT, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD);
        $this->assertEquals(
            \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc::ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY,
            $res->getErrorCode()
        );
    }

    public function test__registryNextPeriod_new()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        /** === Setup Mocks === */
        // $periodEnd = $periodData->getDstampEnd();
        $mPeriodEnd = 'end';
        $PERIOD->setDstampEnd($mPeriodEnd);
        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, $periodType);
        $mPeriodNext = 'next';
        $this->mToolPeriod
            ->shouldReceive('getPeriodNext')->once()
            ->with($mPeriodEnd, $PERIOD_TYPE)
            ->andReturn($mPeriodNext);
        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
        $mDsNextBegin = 'next begin';
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')->once()
            ->with($mPeriodNext)
            ->andReturn($mDsNextBegin);
        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
        $mDsNextEnd = '20160101';
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($mPeriodNext)
            ->andReturn($mDsNextEnd);
        // $periodNow = $this->_toolPeriod->getPeriodCurrentOld(time(), $periodType);
        $mPeriodNow = 'now';
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrentOld')->once()
            ->with(\Mockery::any(), $PERIOD_TYPE)
            ->andReturn($mPeriodNow);
        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
        $mDsNowEnd = '20161231';
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($mPeriodNow)
            ->andReturn($mDsNowEnd);
        // $periodId = $this->_repoPeriod->create($newPeriod);
        $mPeriodId = 32;
        $this->mRepoPeriod
            ->shouldReceive('create')->once()
            ->andReturn($mPeriodId);
        // $dateStarted = $this->_toolDate->getUtcNowForDb();
        $mDateStarted = 'started';
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')->once()
            ->andReturn($mDateStarted);
        // $calcId = $this->_repoCalc->create($newCalc);
        $mCalcId = 64;
        $this->mRepoCalc
            ->shouldReceive('create')->once()
            ->andReturn($mCalcId);
        /** === Call and asserts  === */
        $res = $this->obj->_registryNextPeriod($RESULT, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD);
        $this->assertEquals($mPeriodId, $res->getPeriodData()->getId());
        $this->assertEquals($mCalcId, $res->getCalcData()->getId());
    }

    public function test_checkExistingPeriod_calc()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        $CALC = new \Praxigento\BonusBase\Data\Entity\Calculation();
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(PvBased::class . '[_checkStateForExistingPeriod]', $this->objArgs);
        /** === Setup Mocks === */
        // $result = $this->_checkStateForExistingPeriod($result, $calcTypeId, $periodType, $periodData, $calcData);
        $this->obj
            ->shouldReceive('_checkStateForExistingPeriod')->once()
            ->andReturn($RESULT);
        /** === Call and asserts  === */
        $res = $this->obj->checkExistingPeriod(
            $RESULT, $CALC_TYPE_CODE, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD, $CALC
        );
        $this->assertEquals($RESULT, $res);
    }

    public function test_checkExistingPeriod_noCalc()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 4;
        $PERIOD_TYPE = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        $PERIOD = new \Praxigento\BonusBase\Data\Entity\Period();
        $CALC = null;
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(PvBased::class . '[_checkStateForExistingPeriod]', $this->objArgs);
        /** === Setup Mocks === */
        /** === Call and asserts  === */
        $res = $this->obj->checkExistingPeriod(
            $RESULT, $CALC_TYPE_CODE, $CALC_TYPE_ID, $PERIOD_TYPE, $PERIOD, $CALC
        );
        $this->assertEquals(
            \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc::ERR_NO_CALC_FOR_EXISTING_PERIOD,
            $res->getErrorCode()
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(PvBased::class, $this->obj);
    }

    public function test_getNewPeriodDataForPv_date()
    {
        /** === Test Data === */
        $mResult = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $mCalcTypeId = 4;
        $mPeriodType = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        /** === Setup Mocks === */
        // $firstDate = $this->_repoService->getFirstDateForPvTransactions();
        $mFirstDate = 'timestamp';
        $this->mRepoService
            ->shouldReceive('getFirstDateForPvTransactions')->once()
            ->andReturn($mFirstDate);
        // $periodMonth = $this->_toolPeriod->getPeriodCurrentOld($firstDate, $periodType);
        $mPeriodMonth = 'month';
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrentOld')->once()
            ->with($mFirstDate, $mPeriodType)
            ->andReturn($mPeriodMonth);
        // $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
        $mDsBegin = 'datestamp begin';
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')->once()
            ->with($mPeriodMonth)
            ->andReturn($mDsBegin);
        // $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
        $mDsEnd = 'datestamp end';
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($mPeriodMonth)
            ->andReturn($mDsEnd);
        // $periodId = $this->_repoPeriod->create($period);
        $mPeriodId = 32;
        $this->mRepoPeriod
            ->shouldReceive('create')->once()
            ->andReturn($mPeriodId);
        // $dateStarted = $this->_toolDate->getUtcNowForDb();
        $mDateStarted = 'started';
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')->once()
            ->andReturn($mDateStarted);
        // $calcId = $this->_repoCalc->create($calc);
        $mCalcId = 64;
        $this->mRepoCalc
            ->shouldReceive('create')->once()
            ->andReturn($mCalcId);
        /** === Call and asserts  === */
        $res = $this->obj->getNewPeriodDataForPv($mResult, $mPeriodType, $mCalcTypeId);
        $this->assertEquals($mPeriodId, $res->getPeriodData()->getId());
        $this->assertEquals($mCalcId, $res->getCalcData()->getId());
    }

    public function test_getNewPeriodDataForPv_noDate()
    {
        /** === Test Data === */
        $mResult = new \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc();
        $mCalcTypeId = 4;
        $mPeriodType = \Praxigento\Core\Tool\IPeriod::TYPE_DAY;
        /** === Setup Mocks === */
        // $firstDate = $this->_repoService->getFirstDateForPvTransactions();
        $mFirstDate = false;
        $this->mRepoService
            ->shouldReceive('getFirstDateForPvTransactions')->once()
            ->andReturn($mFirstDate);
        /** === Call and asserts  === */
        $res = $this->obj->getNewPeriodDataForPv($mResult, $mPeriodType, $mCalcTypeId);
        $this->assertEquals(
            \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc::ERR_HAS_NO_PV_TRANSACTIONS_YET,
            $res->getErrorCode()
        );
    }
}