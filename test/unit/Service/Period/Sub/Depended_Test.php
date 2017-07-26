<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Sub;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Depended_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
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
    /** @var  Depended */
    private $obj;
    /** @var array Constructor arguments for object mocking */
    private $objArgs = [];

    protected function setUp()
    {
        /** create mocks */
        parent::setUp();
        $this->mLogger = $this->_mockLogger();
        $this->mRepoCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Calculation::class);
        $this->mRepoPeriod = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Period::class);
        $this->mRepoService = $this->_mock(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        /** reset args. to create mock of the tested object */
        $this->objArgs = [
            $this->mLogger,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoService,
            $this->mToolDate
        ];
        /** create object to test */
        $this->obj = new Depended(
            $this->mLogger,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoService,
            $this->mToolDate
        );
    }

    public function test__analyzeDependedCalc_complete()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $DEP_TYPE_CODE = 'code';
        $DEP_TYPE_ID = 4;
        $DEP_DS_BEGIN = 'begin';
        $DEP_DS_END = 'end';
        /** === Setup Mocks === */
        // $dependentCalcData = $this->_repoService->getLastCalcForPeriodByDates(...)
        $mDependentCalcData = new ECalculation();
        $mDependentCalcData->setState(Cfg::CALC_STATE_COMPLETE);
        $this->mRepoService
            ->shouldReceive('getLastCalcForPeriodByDates')->once()
            ->with($DEP_TYPE_ID, $DEP_DS_BEGIN, $DEP_DS_END)
            ->andReturn($mDependentCalcData);
        // $this->_logger->warning(...)
        /** === Call and asserts  === */
        $this->obj->_analyzeDependedCalc(
            $RESULT,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID,
            $DEP_DS_BEGIN,
            $DEP_DS_END
        );
        $this->assertNull($RESULT->getDependentCalcData());
    }

    public function test__analyzeDependedCalc_incomplete()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $DEP_TYPE_CODE = 'code';
        $DEP_TYPE_ID = 4;
        $DEP_DS_BEGIN = 'begin';
        $DEP_DS_END = 'end';
        /** === Setup Mocks === */
        // $dependentCalcData = $this->_repoService->getLastCalcForPeriodByDates(...)
        $mDependentCalcData = new ECalculation();
        $mDependentCalcData->setState(Cfg::CALC_STATE_STARTED);
        $this->mRepoService
            ->shouldReceive('getLastCalcForPeriodByDates')->once()
            ->with($DEP_TYPE_ID, $DEP_DS_BEGIN, $DEP_DS_END)
            ->andReturn($mDependentCalcData);
        // $this->_logger->warning(...)
        /** === Call and asserts  === */
        $this->obj->_analyzeDependedCalc(
            $RESULT,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID,
            $DEP_DS_BEGIN,
            $DEP_DS_END
        );
        $this->assertEquals($mDependentCalcData, $RESULT->getDependentCalcData());
    }

    public function test__getDependedCalcForExistingPeriod_equal()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        $DEP_DS_BEGIN = 'begin';
        $DEP_DS_END = 'end';
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Depended::class . '[_analyzeDependedCalc]', $this->objArgs);
        /** === Setup Mocks === */
        // $this->_analyzeDependedCalc(...)
        $this->obj
            ->shouldReceive('_analyzeDependedCalc')->once()
            ->with($RESULT, $DEP_TYPE_CODE, $DEP_TYPE_ID, $DEP_DS_BEGIN, $DEP_DS_END);
        /** === Call and asserts  === */
        $this->obj->_getDependedCalcForExistingPeriod(
            $RESULT,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID,
            $DEP_DS_BEGIN,
            $DEP_DS_END
        );
    }

    public function test__getDependedCalcForExistingPeriod_notEqual()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        $DEP_DS_BEGIN = 'past begin';
        $DEP_DS_END = 'past end';
        /** === Setup Mocks === */
        // $periodId = $this->_repoPeriod->create($period);
        $mPeriodId = 16;
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
        $this->obj->_getDependedCalcForExistingPeriod(
            $RESULT,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID,
            $DEP_DS_BEGIN,
            $DEP_DS_END
        );
        $this->assertEquals($mPeriodId, $RESULT->getDependentPeriodData()->getId());
        $this->assertEquals($mCalcId, $RESULT->getDependentCalcData()->getId());
    }


    public function test__getForCompleteBase_notNull()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Depended::class . '[_getDependedCalcForExistingPeriod]', $this->objArgs);
        /** === Setup Mocks === */
        // $dependPeriodData = $this->_repoService->getLastPeriodByCalcType($dependentCalcTypeId);
        $mDependPeriodData = new EPeriod();
        $this->mRepoService
            ->shouldReceive('getLastPeriodByCalcType')->once()
            ->with($DEP_TYPE_ID)
            ->andReturn($mDependPeriodData);
        // $dependentDsBegin = $dependPeriodData->getDstampBegin();
        $mDependentDsBegin = 'dep begin';
        $mDependPeriodData->setDstampBegin($mDependentDsBegin);
        // $dependentDsEnd = $dependPeriodData->getDstampEnd();
        $mDependentDsEnd = 'dep end';
        $mDependPeriodData->setDstampEnd($mDependentDsEnd);
        // $this->_getDependedCalcForExistingPeriod(...)
        $this->obj
            ->shouldReceive('_getDependedCalcForExistingPeriod')->once()
            ->with(
                $RESULT, $BASE_TYPE_CODE, $BASE_DS_BEGIN, $BASE_DS_END, $DEP_TYPE_CODE, $DEP_TYPE_ID,
                $mDependentDsBegin, $mDependentDsEnd
            );
        /** === Call and asserts  === */
        $this->obj->_getForCompleteBase(
            $RESULT,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID
        );
        $this->assertEquals($mDependPeriodData, $RESULT->getDependentPeriodData());
    }

    public function test__getForCompleteBase_null()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        /** === Setup Mocks === */
        // $dependPeriodData = $this->_repoService->getLastPeriodByCalcType($dependentCalcTypeId);
        $this->mRepoService
            ->shouldReceive('getLastPeriodByCalcType')->once()
            ->with($DEP_TYPE_ID)
            ->andReturn(null);
        // $periodId = $this->_repoPeriod->create($period);
        $mPeriodId = 4;
        $this->mRepoPeriod
            ->shouldReceive('create')->once()
            ->andReturn($mPeriodId);
        // $dateStarted = $this->_toolDate->getUtcNowForDb();
        $mDateStarted = 'started';
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')->once()
            ->andReturn($mDateStarted);
        // $calcId = $this->_repoCalc->create($calc);
        $mCalcId = 32;
        $this->mRepoCalc
            ->shouldReceive('create')->once()
            ->andReturn($mCalcId);
        /** === Call and asserts  === */
        $this->obj->_getForCompleteBase(
            $RESULT,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID
        );
        $this->assertEquals($mPeriodId, $RESULT->getDependentPeriodData()->getId());
        $this->assertEquals($mCalcId, $RESULT->getDependentCalcData()->getId());
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(Depended::class, $this->obj);
    }

    public function test_getDependedCalc_complete()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $BASE_PERIOD_ID = 2;
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Depended::class . '[_getForCompleteBase]', $this->objArgs);
        /** === Setup Mocks === */
        // $baseCalcData = $this->_repoService->getLastCalcForPeriodById($basePeriodId);
        $mBaseCalcData = new ECalculation();
        $mBaseCalcData->setState(Cfg::CALC_STATE_COMPLETE);
        $this->mRepoService
            ->shouldReceive('getLastCalcForPeriodById')->once()
            ->with($BASE_PERIOD_ID)
            ->andReturn($mBaseCalcData);
        // $this->_getForCompleteBase(...)
        $this->obj
            ->shouldReceive('_getForCompleteBase')->once()
            ->andReturn($RESULT, $BASE_TYPE_CODE, $BASE_DS_BEGIN, $BASE_DS_END, $DEP_TYPE_CODE, $DEP_TYPE_ID);
        /** === Call and asserts  === */
        $this->obj->getDependedCalc(
            $RESULT,
            $BASE_PERIOD_ID,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID
        );
    }

    public function test_getDependedCalc_incomplete()
    {
        /** === Test Data === */
        $RESULT = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $BASE_PERIOD_ID = 2;
        $BASE_TYPE_CODE = 'base';
        $BASE_DS_BEGIN = 'begin';
        $BASE_DS_END = 'end';
        $DEP_TYPE_CODE = 'depend';
        $DEP_TYPE_ID = 4;
        /** === Setup Mocks === */
        // $baseCalcData = $this->_repoService->getLastCalcForPeriodById($basePeriodId);
        $this->mRepoService
            ->shouldReceive('getLastCalcForPeriodById')->once()
            ->with($BASE_PERIOD_ID)
            ->andReturn(null);
        /** === Call and asserts  === */
        $this->obj->getDependedCalc(
            $RESULT,
            $BASE_PERIOD_ID,
            $BASE_TYPE_CODE,
            $BASE_DS_BEGIN,
            $BASE_DS_END,
            $DEP_TYPE_CODE,
            $DEP_TYPE_ID
        );
    }
}