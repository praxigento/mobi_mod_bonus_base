<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Period;

use Flancer32\Lib\DataObject;
use Praxigento\Bonus\Base\Lib\Entity\Calculation;
use Praxigento\Bonus\Base\Lib\Entity\Period;
use Praxigento\Bonus\Base\Lib\Repo\IModule;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\Core\Tool\IPeriod as ToolPeriod;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Call_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase
{
    /** @var  Call */
    private $call;
    /** @var  \Mockery\MockInterface */
    private $mCallTypeCalc;
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mDba;
    /** @var  \Mockery\MockInterface */
    private $mLogger;
    /** @var  \Mockery\MockInterface */
    private $mRepoGeneric;
    /** @var  \Mockery\MockInterface */
    private $mRepoMod;
    /** @var  \Mockery\MockInterface */
    private $mToolPeriod;

    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped('Test is deprecated after M1 & M2 merge is done.');
        $this->mConn = $this->_mockDba();
        $this->mDba = $this->_mockResourceConnection($this->mConn);
        $this->mRepoGeneric = $this->_mockRepoGeneric($this->mDba);
        $this->mRepoMod = $this->_mock(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $this->mLogger = $this->_mockLogger();
        $this->mCallTypeCalc = $this->_mock(\Praxigento\Bonus\Base\Lib\Service\ITypeCalc::class);
        $this->mToolPeriod = $this->_mock(\Praxigento\Core\Tool\IPeriod::class);
        $this->call = new Call(
            $this->mLogger,
            $this->mCallTypeCalc,
            $this->mToolPeriod,
            $this->mRepoMod
        );
    }

    public function test_getForDependentCalc_isBasePeriod_complete_isDependent_samePeriod_incomplete()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;
        $PERIOD_BEGIN = 'beign';
        $PERIOD_END = 'end';
        $DEP_PERIOD_DATA = 'dep_period';
        $DEP_CALC_DATA = 'dep_calc';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_DEPEND)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
                ]
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getForDependentCalc_isBasePeriod_complete_noDependent()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;
        $PERIOD_BEGIN = 'beign';
        $PERIOD_END = 'end';
        $DEP_PERIOD_DATA = 'dep_period';
        $DEP_CALC_DATA = 'dep_calc';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_DEPEND)
            ->andReturn(new DataObject());
        // $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
        $this->mRepoMod
            ->shouldReceive('addPeriod')
            ->with($CALC_TYPE_ID_DEPEND, $PERIOD_BEGIN, $PERIOD_END)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => $DEP_PERIOD_DATA,
                IModule::A_CALC => $DEP_CALC_DATA
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($DEP_PERIOD_DATA, $resp->getDependentPeriodData());
        $this->assertEquals($DEP_CALC_DATA, $resp->getDependentCalcData());
    }

    public function test_getForDependentCalc_isBasePeriod_started()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;
        $PERIOD_BEGIN = 'beign';
        $PERIOD_END = 'end';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
                ]
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_getForDependentCalc_isBase_complete_isDependent_notSamePeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;
        $PERIOD_BEGIN = 'beign';
        $PERIOD_END = 'end';
        $DEP_PERIOD_DATA = 'dep_period';
        $DEP_CALC_DATA = 'dep_calc';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_DEPEND)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN . 'not equal',
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
                ]
            ]));
        // $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
        $this->mRepoMod
            ->shouldReceive('addPeriod')
            ->with($CALC_TYPE_ID_DEPEND, $PERIOD_BEGIN, $PERIOD_END)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => $DEP_PERIOD_DATA,
                IModule::A_CALC => $DEP_CALC_DATA
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($DEP_PERIOD_DATA, $resp->getDependentPeriodData());
        $this->assertEquals($DEP_CALC_DATA, $resp->getDependentCalcData());
    }

    public function test_getForDependentCalc_isBase_complete_isDependent_samePeriod_complete()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;
        $PERIOD_BEGIN = 'beign';
        $PERIOD_END = 'end';
        $DEP_PERIOD_DATA = 'dep_period';
        $DEP_CALC_DATA = 'dep_calc';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_DEPEND)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_DSTAMP_BEGIN => $PERIOD_BEGIN,
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_getForDependentCalc_noBasePeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE_BASE = 'base';
        $CALC_TYPE_CODE_DEPEND = 'depend';
        $CALC_TYPE_ID_BASE = 2;
        $CALC_TYPE_ID_DEPEND = 4;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_BASE)
            ->andReturn($CALC_TYPE_ID_BASE);
        // $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE_DEPEND)
            ->andReturn($CALC_TYPE_ID_DEPEND);
        // $dataBasePeriod = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID_BASE)
            ->andReturn(new DataObject([IModule::A_PERIOD => null]));

        /** === Call and asserts  === */
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode($CALC_TYPE_CODE_BASE);
        $req->setDependentCalcTypeCode($CALC_TYPE_CODE_DEPEND);
        $resp = $this->call->getForDependentCalc($req);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_getForPvBasedCalc_isPeriod_isCalc_complete_future()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;
        $PERIOD_END = 'end';
        $PERIOD_NEXT = 'next';
        $DS_NEXT_BEGIN = 'next begin';
        $DS_NEXT_END = 'next end';
        $PERIOD_NOW = 'period now';
        $DS_NOW_END = 'before now end';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        $this->mLogger->shouldReceive('error');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_ID => 'id',
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, ToolPeriod::TYPE_MONTH);
        $this->mToolPeriod
            ->shouldReceive('getPeriodNext')
            ->with($PERIOD_END, ToolPeriod::TYPE_MONTH)
            ->andReturn($PERIOD_NEXT);
        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')
            ->with($PERIOD_NEXT)
            ->andReturn($DS_NEXT_BEGIN);
        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')
            ->with($PERIOD_NEXT)
            ->andReturn($DS_NEXT_END);
        // $periodNow = $this->_toolPeriod->getPeriodCurrent(time(), ToolPeriod::TYPE_MONTH);
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrent')
            ->andReturn($PERIOD_NOW);
        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')
            ->andReturn($DS_NOW_END);

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertFalse($resp->isSucceed());
        $errCode = $resp->getErrorCode();
        $this->assertEquals(Response\GetForPvBasedCalc::ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY, $errCode);
    }

    public function test_getForPvBasedCalc_isPeriod_isCalc_complete_past()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;
        $PERIOD_END = 'end';
        $PERIOD_NEXT = 'next';
        $DS_NEXT_BEGIN = 'next begin';
        $DS_NEXT_END = 'next end';
        $PERIOD_NOW = 'period now';
        $DS_NOW_END = 'now end';
        $DATA_PERIOD = 'period data';
        $DATA_CALC = 'calc data';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        $this->mLogger->shouldReceive('error');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_ID => 'id',
                    Period::ATTR_DSTAMP_END => $PERIOD_END
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
                ]
            ]));
        // $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, ToolPeriod::TYPE_MONTH);
        $this->mToolPeriod
            ->shouldReceive('getPeriodNext')
            ->with($PERIOD_END, ToolPeriod::TYPE_MONTH)
            ->andReturn($PERIOD_NEXT);
        // $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')
            ->with($PERIOD_NEXT)
            ->andReturn($DS_NEXT_BEGIN);
        // $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')
            ->with($PERIOD_NEXT)
            ->andReturn($DS_NEXT_END);
        // $periodNow = $this->_toolPeriod->getPeriodCurrent(time(), ToolPeriod::TYPE_MONTH);
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrent')
            ->andReturn($PERIOD_NOW);
        // $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')
            ->andReturn($DS_NOW_END);
        // $newPeriodData = $this->_repoMod->addPeriod($calcTypeId, $dsNextBegin, $dsNextEnd);
        $this->mRepoMod
            ->shouldReceive('addPeriod')->once()
            ->with($CALC_TYPE_ID, $DS_NEXT_BEGIN, $DS_NEXT_END)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => $DATA_PERIOD,
                IModule::A_CALC => $DATA_CALC
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($DATA_PERIOD, $resp->getPeriodData());
        $this->assertEquals($DATA_CALC, $resp->getCalcData());
    }

    public function test_getForPvBasedCalc_isPeriod_isCalc_incomplete()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        $this->mLogger->shouldReceive('error');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => [
                    Period::ATTR_ID => 'id'
                ],
                IModule::A_CALC => [
                    Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
                ]
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getForPvBasedCalc_isPeriod_noCalc()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        $this->mLogger->shouldReceive('error');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([IModule::A_PERIOD => [Period::ATTR_ID => 'id']]));

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertFalse($resp->isSucceed());
        $errCode = $resp->getErrorCode();
        $this->assertEquals(Response\GetForPvBasedCalc::ERR_NO_CALC_FOR_EXISTING_PERIOD, $errCode);
    }

    public function test_getForPvBasedCalc_noPeriod_isTrans()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;
        $TS_FIRST_PV_TRANS = 'timestamp';
        $PERIOD_MONTH = 'month_here';
        $DS_BEGIN = 'begin';
        $DS_END = 'end';
        $PERIOD_DATA = 'period data';
        $CALC_DATA = 'calc data';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([IModule::A_PERIOD => null]));
        // $ts = $this->_repoMod->getFirstDateForPvTransactions();
        $this->mRepoMod
            ->shouldReceive('getFirstDateForPvTransactions')->once()
            ->andReturn($TS_FIRST_PV_TRANS);
        // $periodMonth = $this->_toolPeriod->getPeriodCurrent($ts, ToolPeriod::TYPE_MONTH);
        $this->mToolPeriod
            ->shouldReceive('getPeriodCurrent')->once()
            ->with($TS_FIRST_PV_TRANS, ToolPeriod::TYPE_MONTH)
            ->andReturn($PERIOD_MONTH);
        // $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
        $this->mToolPeriod
            ->shouldReceive('getPeriodFirstDate')->once()
            ->with($PERIOD_MONTH)
            ->andReturn($DS_BEGIN);
        // $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
        $this->mToolPeriod
            ->shouldReceive('getPeriodLastDate')->once()
            ->with($PERIOD_MONTH)
            ->andReturn($DS_END);
        // $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
        $this->mRepoMod
            ->shouldReceive('addPeriod')->once()
            ->with($CALC_TYPE_ID, $DS_BEGIN, $DS_END)
            ->andReturn(new DataObject([
                IModule::A_PERIOD => $PERIOD_DATA,
                IModule::A_CALC => $CALC_DATA
            ]));

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_DATA, $resp->getPeriodData());
        $this->assertEquals($CALC_DATA, $resp->getCalcData());

    }

    public function test_getForPvBasedCalc_noPeriod_noTrans()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'code';
        $CALC_TYPE_ID = 2;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        $this->mLogger->shouldReceive('warning');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')->once()
            ->with($CALC_TYPE_CODE)
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')->once()
            ->with($CALC_TYPE_ID)
            ->andReturn(new DataObject([IModule::A_PERIOD => null]));
        // $ts = $this->_repoMod->getFirstDateForPvTransactions();
        $this->mRepoMod
            ->shouldReceive('getFirstDateForPvTransactions')->once()
            ->andReturn(false);

        /** === Call and asserts  === */
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getForPvBasedCalc($req);
        $this->assertFalse($resp->isSucceed());
        $errCode = $resp->getErrorCode();
        $this->assertEquals(Response\GetForPvBasedCalc::ERR_HAS_NO_PV_TRANSACTIONS_YET, $errCode);
    }

    public function test_getLatest_byCalcCode_nothingToDo()
    {
        /** === Test Data === */
        $CALC_TYPE_CODE = 'calc_code';
        $CALC_TYPE_ID = 21;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->getLatestPeriod($calcTypeId, $shouldGetLatestCalc, $shouldGetAllCalcs);
        $mData = new DataObject();
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')
            ->andReturn($mData);

        /** === Call and asserts  === */
        $req = new Request\GetLatest();
        $req->setCalcTypeCode($CALC_TYPE_CODE);
        $resp = $this->call->getLatest($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getLatest_periodWithCalc()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 21;

        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        // $data = $this->_RepoMod->getLatestPeriod($calcTypeId, $shouldGetLatestCalc, $shouldGetAllCalcs);
        $mData = new DataObject([
            IModule::A_PERIOD => [],
            IModule::A_CALC => []
        ]);
        $this->mRepoMod
            ->shouldReceive('getLatestPeriod')
            ->andReturn($mData);

        /** === Call and asserts  === */
        $req = new Request\GetLatest();
        $req->setCalcTypeId($CALC_TYPE_ID);
        $req->setShouldGetLatestCalc(true);
        $resp = $this->call->getLatest($req);
        $this->assertTrue($resp->isSucceed());
        $calcData = $resp->getCalcData();
        $this->assertTrue(is_array($calcData));
        $periodData = $resp->getPeriodData();
        $this->assertTrue(is_array($periodData));
    }

    public function test_registerPeriod()
    {
        /** === Test Data === */
        $CALC_TYPE_ID = 21;
        $PERIOD_DATA = 'period';
        $CALC_DATA = 'calc';
        /** === Setup Mocks === */
        // logger
        $this->mLogger->shouldReceive('info');
        // $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $this->mRepoMod
            ->shouldReceive('getTypeCalcIdByCode')
            ->andReturn($CALC_TYPE_ID);
        // $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
        $mData = new DataObject([
            IModule::A_PERIOD => $PERIOD_DATA,
            IModule::A_CALC => $CALC_DATA
        ]);
        $this->mRepoMod
            ->shouldReceive('addPeriod')
            ->andReturn($mData);
        /** === Call and asserts  === */
        $req = new Request\RegisterPeriod();
        $resp = $this->call->registerPeriod($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_DATA, $resp->getPeriodData());
        $this->assertEquals($CALC_DATA, $resp->getCalcData());
    }

}