<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period;

use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
{
    /** @var  \Mockery\MockInterface */
    private $mLogger;
    /** @var  \Mockery\MockInterface */
    private $mManTrans;
    /** @var  \Mockery\MockInterface */
    private $mRepoCalc;
    /** @var  \Mockery\MockInterface */
    private $mRepoPeriod;
    /** @var  \Mockery\MockInterface */
    private $mRepoService;
    /** @var  \Mockery\MockInterface */
    private $mRepoTypeCalc;
    /** @var  \Mockery\MockInterface */
    private $mToolDate;
    /** @var  \Mockery\MockInterface */
    private $mToolPeriod;
    /** @var  \Mockery\MockInterface */
    private $mSubDepended;
    /** @var  Call */
    private $obj;
    /** @var array Constructor arguments for object mocking */
    private $objArgs = [];

    protected function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mLogger = $this->_mockLogger();
        $this->mManTrans = $this->_mockTransactionManager();
        $this->mRepoCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\ICalculation::class);
        $this->mRepoPeriod = $this->_mock(\Praxigento\BonusBase\Repo\Entity\IPeriod::class);
        $this->mRepoTypeCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Type\ICalc::class);
        $this->mRepoService = $this->_mock(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $this->mToolPeriod = $this->_mock(\Praxigento\Core\Tool\IPeriod::class);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $this->mSubDepended = $this->_mock(\Praxigento\BonusBase\Service\Period\Sub\Depended::class);
        /** reset args. to create mock of the tested object */
        $this->objArgs = [
            $this->mLogger,
            $this->mManTrans,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoTypeCalc,
            $this->mRepoService,
            $this->mToolPeriod,
            $this->mToolDate,
            $this->mSubDepended
        ];
        /** create object to test */
        $this->obj = new Call(
            $this->mLogger,
            $this->mManTrans,
            $this->mRepoCalc,
            $this->mRepoPeriod,
            $this->mRepoTypeCalc,
            $this->mRepoService,
            $this->mToolPeriod,
            $this->mToolDate,
            $this->mSubDepended
        );
    }

    public function test_addCalc()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 2;
        $DS_BEGIN = 'begin';
        $DS_END = 'end';
        /** === Setup Mocks === */
        // $def = $this->_manTrans->begin();
        $mDef = $this->_mockTransactionDefinition();
        $this->mManTrans
            ->shouldReceive('begin')->once()
            ->andReturn($mDef);
        // $periodId = $this->_repoPeriod->create($periodData);
        $mPeriodId = 32;
        $this->mRepoPeriod
            ->shouldReceive('create')->once()
            ->andReturn($mPeriodId);
        // $dateStarted = $this->_toolDate->getUtcNowForDb();
        $mDateStarted = 'started';
        $this->mToolDate
            ->shouldReceive('getUtcNowForDb')->once()
            ->andReturn($mDateStarted);
        // $calcId = $this->_repoCalc->create($calcData);
        $mCalcId = 64;
        $this->mRepoCalc
            ->shouldReceive('create')->once()
            ->andReturn($mCalcId);
        // $this->_manTrans->commit($def);
        $this->mManTrans
            ->shouldReceive('commit')->once()
            ->with($mDef);
        // $this->_manTrans->end($def);
        $this->mManTrans
            ->shouldReceive('end')->once()
            ->with($mDef);
        /** === Call and asserts  === */
        $req = new Request\AddCalc();
        $req->setCalcTypeId($CALC_TYPE_ID);
        $req->setDateStampBegin($DS_BEGIN);
        $req->setDateStampEnd($DS_END);
        $res = $this->obj->addCalc($req);
        $this->assertTrue($res->isSucceed());
        $this->assertInstanceOf(Response\AddCalc::class, $res);
        $this->assertInstanceOf(EPeriod::class, $res->getPeriod());
        $this->assertInstanceOf(ECalculation::class, $res->getCalculation());
    }

    public function test_getForDependentCalc_null()
    {
        /** === Test Data === */
        $BASE_TYPE_CODE = 'base';
        $DEP_TYPE_CODE = 'depended';
        $REQ = new \Praxigento\BonusBase\Service\Period\Request\GetForDependentCalc();
        $REQ->setBaseCalcTypeCode($BASE_TYPE_CODE);
        $REQ->setDependentCalcTypeCode($DEP_TYPE_CODE);
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Call::class . '[method1, method2]', $this->objArgs);
        /** === Setup Mocks === */
        // $def = $this->_manTrans->begin();
        $mDef = $this->_mockTransactionDefinition();
        $this->mManTrans
            ->shouldReceive('begin')->once()
            ->andReturn($mDef);
        // $baseCalcTypeId = $this->_repoTypeCalc->getIdByCode($baseCalcTypeCode);
        $mBaseCalcTypeId = 2;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->with($BASE_TYPE_CODE)
            ->andReturn($mBaseCalcTypeId);
        // $dependentCalcTypeId = $this->_repoTypeCalc->getIdByCode($dependentCalcTypeCode);
        $mDependentCalcTypeId = 4;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->with($DEP_TYPE_CODE)
            ->andReturn($mDependentCalcTypeId);
        // $basePeriodData = $this->_repoService->getLastPeriodByCalcType($baseCalcTypeId);
        $this->mRepoService
            ->shouldReceive('getLastPeriodByCalcType')->once()
            ->andReturn(null);
        // $this->_manTrans->commit($def);
        $this->mManTrans
            ->shouldReceive('commit')->once()
            ->with($mDef);
        // $this->_manTrans->end($def);
        $this->mManTrans
            ->shouldReceive('end')->once()
            ->with($mDef);
        /** === Call and asserts  === */
        $res = $this->obj->getForDependentCalc($REQ);
        $this->assertFalse($res->isSucceed());
    }

    public function test_getForDependentCalc_notNull()
    {
        /** === Test Data === */
        $BASE_TYPE_CODE = 'base';
        $DEP_TYPE_CODE = 'depended';
        $REQ = new \Praxigento\BonusBase\Service\Period\Request\GetForDependentCalc();
        $REQ->setBaseCalcTypeCode($BASE_TYPE_CODE);
        $REQ->setDependentCalcTypeCode($DEP_TYPE_CODE);
        /** === Setup Mocks === */
        // $def = $this->_manTrans->begin();
        $mDef = $this->_mockTransactionDefinition();
        $this->mManTrans
            ->shouldReceive('begin')->once()
            ->andReturn($mDef);
        // $baseCalcTypeId = $this->_repoTypeCalc->getIdByCode($baseCalcTypeCode);
        $mBaseCalcTypeId = 2;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->with($BASE_TYPE_CODE)
            ->andReturn($mBaseCalcTypeId);
        // $dependentCalcTypeId = $this->_repoTypeCalc->getIdByCode($dependentCalcTypeCode);
        $mDependentCalcTypeId = 4;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->with($DEP_TYPE_CODE)
            ->andReturn($mDependentCalcTypeId);
        // $basePeriodData = $this->_repoService->getLastPeriodByCalcType($baseCalcTypeId);
        $mBasePeriodData = new EPeriod();
        $this->mRepoService
            ->shouldReceive('getLastPeriodByCalcType')->once()
            ->andReturn($mBasePeriodData);
        // $baseDsBegin = $basePeriodData->getDstampBegin();
        $mBaseDsBegin = 'base begin';
        $mBasePeriodData->setDstampBegin($mBaseDsBegin);
        // $baseDsEnd = $basePeriodData->getDstampEnd();
        $mBaseDsEnd = 'base end';
        $mBasePeriodData->setDstampEnd($mBaseDsEnd);
        // $periodId = $basePeriodData->getId();
        $mPeriodId = 16;
        $mBasePeriodData->setId($mPeriodId);
        // $result = $this->_subDepended->getDependedCalc(...)
        $mResult = new \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc();
        $this->mSubDepended
            ->shouldReceive('getDependedCalc')->once()
            ->with(
                \Mockery::any(), $mPeriodId,
                $BASE_TYPE_CODE, $mBaseDsBegin, $mBaseDsEnd,
                $DEP_TYPE_CODE, $mDependentCalcTypeId
            )
            ->andReturn($mResult);
        //        if (
        //            $result->getDependentPeriodData() &&
        //            $result->getDependentCalcData()
        //        ) {
        $mResult->setDependentPeriodData(['data']);
        $mResult->setDependentCalcData(['data']);
        // $this->_manTrans->commit($def);
        $this->mManTrans
            ->shouldReceive('commit')->once()
            ->with($mDef);
        // $this->_manTrans->end($def);
        $this->mManTrans
            ->shouldReceive('end')->once()
            ->with($mDef);
        /** === Call and asserts  === */
        $res = $this->obj->getForDependentCalc($REQ);
        $this->assertTrue($res->isSucceed());
    }

    public function test_getLatest()
    {
        /** === Test Data === */
        $TYPE_CODE = 'code';
        $REQ = new \Praxigento\BonusBase\Service\Period\Request\GetLatest();
        $REQ->setCalcTypeCode($TYPE_CODE);
        /** === Setup Mocks === */
        // $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
        $mCalcTypeId = 4;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->andReturn($mCalcTypeId);
        // $periodLatest = $this->_repoService->getLastPeriodByCalcType($calcTypeId);
        $mPeriodLatest = new EPeriod();
        $this->mRepoService
            ->shouldReceive('getLastPeriodByCalcType')->once()
            ->with($mCalcTypeId)
            ->andReturn($mPeriodLatest);
        // $periodId = $periodLatest->getId();
        $mPeriodId = 16;
        $mPeriodLatest->setId($mPeriodId);
        // $calcLatest = $this->_repoService->getLastCalcForPeriodById($periodId);
        $mCalcLatest = new ECalculation();
        $this->mRepoService
            ->shouldReceive('getLastCalcForPeriodById')->once()
            ->with($mPeriodId)
            ->andReturn($mCalcLatest);
        /** === Call and asserts  === */
        $res = $this->obj->getLatest($REQ);
        $this->assertTrue($res->isSucceed());
    }

    public function test_registerPeriod()
    {
        /** === Test Data === */
        $REQ = new \Praxigento\BonusBase\Service\Period\Request\RegisterPeriod();
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Call::class . '[addCalc]', $this->objArgs);
        /** === Setup Mocks === */
        // $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
        $mCalcTypeId = 16;
        $this->mRepoTypeCalc
            ->shouldReceive('getIdByCode')->once()
            ->andReturn($mCalcTypeId);
        // $data = $this->addCalc($reqAddCalc);
        $mData = new \Flancer32\Lib\DataObject(['period' => [], 'calculation' => []]);
        $this->obj->shouldReceive('addCalc')->once()
            ->andReturn($mData);
        /** === Call and asserts  === */
        $res = $this->obj->registerPeriod($REQ);
        $this->assertTrue($res->isSucceed());
    }
}