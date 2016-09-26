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

    protected function setUp()
    {
        parent::setUp();
        $this->mLogger = $this->_mockLogger();
        $this->mManTrans = $this->_mockTransactionManager();
        $this->mRepoCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\ICalculation::class);
        $this->mRepoPeriod = $this->_mock(\Praxigento\BonusBase\Repo\Entity\IPeriod::class);
        $this->mRepoTypeCalc = $this->_mock(\Praxigento\BonusBase\Repo\Entity\Type\ICalc::class);
        $this->mRepoService = $this->_mock(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $this->mToolPeriod = $this->_mock(\Praxigento\Core\Tool\IPeriod::class);
        $this->mToolDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $this->mSubDepended = $this->_mock(\Praxigento\BonusBase\Service\Period\Sub\Depended::class);
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
//
//    public function test_getForDependentCalc_isBasePeriod_complete_isDependent_samePeriod_incomplete()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//        $PERIOD_BEGIN = 'beign';
//        $PERIOD_END = 'end';
//        $DEP_PERIOD_DATA = 'dep_period';
//        $DEP_CALC_DATA = 'dep_calc';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_DEPEND)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
//                ]
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertTrue($resp->isSucceed());
//    }
//
//    public function test_getForDependentCalc_isBasePeriod_complete_noDependent()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//        $PERIOD_BEGIN = 'beign';
//        $PERIOD_END = 'end';
//        $DEP_PERIOD_DATA = 'dep_period';
//        $DEP_CALC_DATA = 'dep_calc';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_DEPEND)
//            ->andReturn(new DataObject());
//        // $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
//        $this->mRepoMod
//            ->shouldReceive('addPeriod')
//            ->with($CALC_TYPE_ID_DEPEND, $PERIOD_BEGIN, $PERIOD_END)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => $DEP_PERIOD_DATA,
//                IModule::A_CALC => $DEP_CALC_DATA
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertTrue($resp->isSucceed());
//        $this->assertEquals($DEP_PERIOD_DATA, $resp->getDependentPeriodData());
//        $this->assertEquals($DEP_CALC_DATA, $resp->getDependentCalcData());
//    }
//
//    public function test_getForDependentCalc_isBasePeriod_started()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//        $PERIOD_BEGIN = 'beign';
//        $PERIOD_END = 'end';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
//                ]
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertFalse($resp->isSucceed());
//    }
//
//    public function test_getForDependentCalc_isBase_complete_isDependent_notSamePeriod()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//        $PERIOD_BEGIN = 'beign';
//        $PERIOD_END = 'end';
//        $DEP_PERIOD_DATA = 'dep_period';
//        $DEP_CALC_DATA = 'dep_calc';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_DEPEND)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN . 'not equal',
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
//                ]
//            ]));
//        // $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
//        $this->mRepoMod
//            ->shouldReceive('addPeriod')
//            ->with($CALC_TYPE_ID_DEPEND, $PERIOD_BEGIN, $PERIOD_END)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => $DEP_PERIOD_DATA,
//                IModule::A_CALC => $DEP_CALC_DATA
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertTrue($resp->isSucceed());
//        $this->assertEquals($DEP_PERIOD_DATA, $resp->getDependentPeriodData());
//        $this->assertEquals($DEP_CALC_DATA, $resp->getDependentCalcData());
//    }
//
//    public function test_getForDependentCalc_isBase_complete_isDependent_samePeriod_complete()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//        $PERIOD_BEGIN = 'beign';
//        $PERIOD_END = 'end';
//        $DEP_PERIOD_DATA = 'dep_period';
//        $DEP_CALC_DATA = 'dep_calc';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_DEPEND)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertFalse($resp->isSucceed());
//    }
//
//    public function test_getForDependentCalc_noBasePeriod()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE_BASE = 'base';
//        $CALC_TYPE_CODE_DEPEND = 'depend';
//        $CALC_TYPE_ID_BASE = 2;
//        $CALC_TYPE_ID_DEPEND = 4;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_BASE)
//            ->andReturn($CALC_TYPE_ID_BASE);
//        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE_DEPEND)
//            ->andReturn($CALC_TYPE_ID_DEPEND);
//        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID_BASE)
//            ->andReturn(new DataObject([IModule::A_PERIOD => null]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForDependentCalc();
//        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
//        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
//        $resp = $this->obj->getForDependentCalc($req);
//        $this->assertFalse($resp->isSucceed());
//    }
//
//    public function test_getForPvBasedCalc_isPeriod_isCalc_complete_future()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//        $PERIOD_END = 'end';
//        $PERIOD_NEXT = 'next';
//        $DS_NEXT_BEGIN = 'next begin';
//        $DS_NEXT_END = 'next end';
//        $PERIOD_NOW = 'period now';
//        $DS_NOW_END = 'before now end';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        $this->mLogger->shouldReceive('error');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_ID => 'id',
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, ToolPeriod::TYPE_MONTH);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodNext')
//            ->with($PERIOD_END, ToolPeriod::TYPE_MONTH)
//            ->andReturn($PERIOD_NEXT);
//        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodFirstDate')
//            ->with($PERIOD_NEXT)
//            ->andReturn($DS_NEXT_BEGIN);
//        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodLastDate')
//            ->with($PERIOD_NEXT)
//            ->andReturn($DS_NEXT_END);
//        // $periodNow = $this->_toolPeriod->getPeriodCurrent(time(), ToolPeriod::TYPE_MONTH);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodCurrent')
//            ->andReturn($PERIOD_NOW);
//        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodLastDate')
//            ->andReturn($DS_NOW_END);
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertFalse($resp->isSucceed());
//        $errCode = $resp->getErrorCode();
//        $this->assertEquals(Response\GetForPvBasedCalc::ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY, $errCode);
//    }
//
//    public function test_getForPvBasedCalc_isPeriod_isCalc_complete_past()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//        $PERIOD_END = 'end';
//        $PERIOD_NEXT = 'next';
//        $DS_NEXT_BEGIN = 'next begin';
//        $DS_NEXT_END = 'next end';
//        $PERIOD_NOW = 'period now';
//        $DS_NOW_END = 'now end';
//        $DATA_PERIOD = 'period data';
//        $DATA_CALC = 'calc data';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        $this->mLogger->shouldReceive('error');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_ID => 'id',
//                    Period::ATTR_DSTAMP_END => $PERIOD_END
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
//                ]
//            ]));
//        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, ToolPeriod::TYPE_MONTH);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodNext')
//            ->with($PERIOD_END, ToolPeriod::TYPE_MONTH)
//            ->andReturn($PERIOD_NEXT);
//        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodFirstDate')
//            ->with($PERIOD_NEXT)
//            ->andReturn($DS_NEXT_BEGIN);
//        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodLastDate')
//            ->with($PERIOD_NEXT)
//            ->andReturn($DS_NEXT_END);
//        // $periodNow = $this->_toolPeriod->getPeriodCurrent(time(), ToolPeriod::TYPE_MONTH);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodCurrent')
//            ->andReturn($PERIOD_NOW);
//        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodLastDate')
//            ->andReturn($DS_NOW_END);
//        // $newPeriodData = $this->_repoMod->addPeriod($calcTypeId, $dsNextBegin, $dsNextEnd);
//        $this->mRepoMod
//            ->shouldReceive('addPeriod')->once()
//            ->with($CALC_TYPE_ID, $DS_NEXT_BEGIN, $DS_NEXT_END)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => $DATA_PERIOD,
//                IModule::A_CALC => $DATA_CALC
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertTrue($resp->isSucceed());
//        $this->assertEquals($DATA_PERIOD, $resp->getPeriodData());
//        $this->assertEquals($DATA_CALC, $resp->getCalcData());
//    }
//
//    public function test_getForPvBasedCalc_isPeriod_isCalc_incomplete()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        $this->mLogger->shouldReceive('error');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => [
//                    Period::ATTR_ID => 'id'
//                ],
//                IModule::A_CALC => [
//                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
//                ]
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertTrue($resp->isSucceed());
//    }
//
//    public function test_getForPvBasedCalc_isPeriod_noCalc()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        $this->mLogger->shouldReceive('error');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([IModule::A_PERIOD => [Period::ATTR_ID => 'id']]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertFalse($resp->isSucceed());
//        $errCode = $resp->getErrorCode();
//        $this->assertEquals(Response\GetForPvBasedCalc::ERR_NO_CALC_FOR_EXISTING_PERIOD, $errCode);
//    }
//
//    public function test_getForPvBasedCalc_noPeriod_isTrans()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//        $TS_FIRST_PV_TRANS = 'timestamp';
//        $PERIOD_MONTH = 'month_here';
//        $DS_BEGIN = 'begin';
//        $DS_END = 'end';
//        $PERIOD_DATA = 'period data';
//        $CALC_DATA = 'calc data';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([IModule::A_PERIOD => null]));
//        // $ts = $this->_repoMod->getFirstDateForPvTransactions();
//        $this->mRepoMod
//            ->shouldReceive('getFirstDateForPvTransactions')->once()
//            ->andReturn($TS_FIRST_PV_TRANS);
//        // $periodMonth = $this->_toolPeriod->getPeriodCurrent($ts, ToolPeriod::TYPE_MONTH);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodCurrent')->once()
//            ->with($TS_FIRST_PV_TRANS, ToolPeriod::TYPE_MONTH)
//            ->andReturn($PERIOD_MONTH);
//        // $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodFirstDate')->once()
//            ->with($PERIOD_MONTH)
//            ->andReturn($DS_BEGIN);
//        // $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
//        $this->mToolPeriod
//            ->shouldReceive('getPeriodLastDate')->once()
//            ->with($PERIOD_MONTH)
//            ->andReturn($DS_END);
//        // $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
//        $this->mRepoMod
//            ->shouldReceive('addPeriod')->once()
//            ->with($CALC_TYPE_ID, $DS_BEGIN, $DS_END)
//            ->andReturn(new DataObject([
//                IModule::A_PERIOD => $PERIOD_DATA,
//                IModule::A_CALC => $CALC_DATA
//            ]));
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertTrue($resp->isSucceed());
//        $this->assertEquals($PERIOD_DATA, $resp->getPeriodData());
//        $this->assertEquals($CALC_DATA, $resp->getCalcData());
//
//    }
//
//    public function test_getForPvBasedCalc_noPeriod_noTrans()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'code';
//        $CALC_TYPE_ID = 2;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        $this->mLogger->shouldReceive('warning');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')->once()
//            ->with($CALC_TYPE_CODE)
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')->once()
//            ->with($CALC_TYPE_ID)
//            ->andReturn(new DataObject([IModule::A_PERIOD => null]));
//        // $ts = $this->_repoMod->getFirstDateForPvTransactions();
//        $this->mRepoMod
//            ->shouldReceive('getFirstDateForPvTransactions')->once()
//            ->andReturn(false);
//
//        /** === Call and asserts  === */
//        $req = new Request\GetForPvBasedCalc();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getForPvBasedCalc($req);
//        $this->assertFalse($resp->isSucceed());
//        $errCode = $resp->getErrorCode();
//        $this->assertEquals(Response\GetForPvBasedCalc::ERR_HAS_NO_PV_TRANSACTIONS_YET, $errCode);
//    }
//
//    public function test_getLatest_byCalcCode_nothingToDo()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_CODE = 'calc_code';
//        $CALC_TYPE_ID = 21;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->getLatestPeriod($calcTypeId, $shouldGetLatestCalc, $shouldGetAllCalcs);
//        $mData = new DataObject();
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')
//            ->andReturn($mData);
//
//        /** === Call and asserts  === */
//        $req = new Request\GetLatest();
//        $req->setCalcTypeCode($CALC_TYPE_CODE);
//        $resp = $this->obj->getLatest($req);
//        $this->assertTrue($resp->isSucceed());
//    }
//
//    public function test_getLatest_periodWithCalc()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_ID = 21;
//
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        // $data = $this->_RepoMod->getLatestPeriod($calcTypeId, $shouldGetLatestCalc, $shouldGetAllCalcs);
//        $mData = new DataObject([
//            IModule::A_PERIOD => [],
//            IModule::A_CALC => []
//        ]);
//        $this->mRepoMod
//            ->shouldReceive('getLatestPeriod')
//            ->andReturn($mData);
//
//        /** === Call and asserts  === */
//        $req = new Request\GetLatest();
//        $req->setCalcTypeId($CALC_TYPE_ID);
//        $resp = $this->obj->getLatest($req);
//        $this->assertTrue($resp->isSucceed());
//        $calcData = $resp->getCalcData();
//        $this->assertTrue(is_array($calcData));
//        $periodData = $resp->getPeriodData();
//        $this->assertTrue(is_array($periodData));
//    }
//
//    public function test_registerPeriod()
//    {
//        /** === Test Data === */
//        $CALC_TYPE_ID = 21;
//        $PERIOD_DATA = 'period';
//        $CALC_DATA = 'calc';
//        /** === Setup Mocks === */
//        // logger
//        $this->mLogger->shouldReceive('info');
//        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
//        $this->mRepoMod
//            ->shouldReceive('getTypeCalcIdByCode')
//            ->andReturn($CALC_TYPE_ID);
//        // $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
//        $mData = new DataObject([
//            IModule::A_PERIOD => $PERIOD_DATA,
//            IModule::A_CALC => $CALC_DATA
//        ]);
//        $this->mRepoMod
//            ->shouldReceive('addPeriod')
//            ->andReturn($mData);
//        /** === Call and asserts  === */
//        $req = new Request\RegisterPeriod();
//        $resp = $this->obj->registerPeriod($req);
//        $this->assertTrue($resp->isSucceed());
//        $this->assertEquals($PERIOD_DATA, $resp->getPeriodData());
//        $this->assertEquals($CALC_DATA, $resp->getCalcData());
//    }

}