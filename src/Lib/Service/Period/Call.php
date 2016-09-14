<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Lib\Service\Period;

use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;
use Praxigento\BonusBase\Lib\Repo\IModule;
use Praxigento\BonusBase\Lib\Repo\IModule as RepoModule;
use Praxigento\BonusBase\Lib\Service\IPeriod;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\Core\Service\Base\Call as BaseCall;
use Praxigento\Core\Tool\IPeriod as ToolPeriod;

class Call extends BaseCall implements IPeriod
{
    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;
    /** @var RepoModule */
    protected $_repoMod;
    /** @var  \Praxigento\Core\Tool\IPeriod */
    protected $_toolPeriod;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Praxigento\Core\Tool\IPeriod $toolPeriod,
        RepoModule $repoMod
    ) {
        $this->_logger = $logger;
        $this->_toolPeriod = $toolPeriod;
        $this->_repoMod = $repoMod;
    }

    /**
     * @param Request\GetForDependentCalc $request
     *
     * @return Response\GetForDependentCalc
     */
    public function getForDependentCalc(Request\GetForDependentCalc $request)
    {
        $result = new Response\GetForDependentCalc();
        $dependentCalcTypeCode = $request->getDependentCalcTypeCode();
        $baseCalcTypeCode = $request->getBaseCalcTypeCode();
        $this->_logger->info("'Get latest period for Dependent Calculation' operation is started (dependent=$dependentCalcTypeCode, base=$baseCalcTypeCode).");

        /* get IDs for calculations codes */
        $dependentCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($dependentCalcTypeCode);
        $baseCalcTypeId = $this->_repoMod->getTypeCalcIdByCode($baseCalcTypeCode);
        /* get the last base period data */
        $latestPeriodData = $this->_repoMod->getLatestPeriod($baseCalcTypeId);
        $basePeriodData = $latestPeriodData->getData(IModule::A_PERIOD);
        if (is_null($basePeriodData)) {
            $this->_logger->warning("There is no period for '$baseCalcTypeCode' calculation  yet. '$dependentCalcTypeCode' could not be calculated.");
        } else {
            $result->setBasePeriodData($basePeriodData);
            $baseCalcData = $latestPeriodData->getData(IModule::A_CALC);
            $result->setBaseCalcData($baseCalcData);
            $baseDsBegin = $basePeriodData[Period::ATTR_DSTAMP_BEGIN];
            $baseDsEnd = $basePeriodData[Period::ATTR_DSTAMP_END];
            if (
                is_array($baseCalcData) &&
                isset($baseCalcData[Calculation::ATTR_STATE]) &&
                ($baseCalcData[Calculation::ATTR_STATE] == Cfg::CALC_STATE_COMPLETE)
            ) {
                /* there is complete Base Calculation */
                $respDependentPeriod = $this->_repoMod->getLatestPeriod($dependentCalcTypeId);
                $dependPeriodData = $respDependentPeriod->getData(IModule::A_PERIOD);
                $dependentCalcData = $respDependentPeriod->getData(IModule::A_CALC);
                if (is_null($dependPeriodData)) {
                    /* there is no dependent period */
                    $this->_logger->warning("There is no period data for calculation '$dependentCalcTypeCode'. New period and related calculation will be created.");
                    $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
                    $result->setDependentPeriodData($dependPeriodData->getData(IModule::A_PERIOD));
                    $result->setDependentCalcData($dependPeriodData->getData(IModule::A_CALC));
                    $result->markSucceed();
                } else {
                    /* there is dependent period */
                    $dependentDsBegin = $dependPeriodData[Period::ATTR_DSTAMP_BEGIN];
                    $dependentDsEnd = $dependPeriodData[Period::ATTR_DSTAMP_END];
                    if (
                        ($dependentDsBegin == $baseDsBegin) &&
                        ($dependentDsEnd == $baseDsEnd)
                    ) {
                        /* dependent period has the same begin/end as related base period */
                        $this->_logger->info("There is base '$baseCalcTypeCode' period for dependent '$dependentCalcTypeCode' period ($dependentDsBegin-$dependentDsEnd).");
                        if (
                            is_array($dependentCalcData) &&
                            isset($dependentCalcData[Calculation::ATTR_STATE]) &&
                            ($dependentCalcData[Calculation::ATTR_STATE] == Cfg::CALC_STATE_COMPLETE)
                        ) {
                            /* complete dependent period for complete base period */
                            $this->_logger->warning("There is '$dependentCalcTypeCode' period with complete calculation. No more '$dependentCalcTypeCode' could be calculated.");
                        } else {
                            /* incomplete dependent period for complete base period */
                            $this->_logger->warning("There is '$dependentCalcTypeCode' period without complete calculation. Continue calculation for this period.");
                            $result->setDependentPeriodData($dependPeriodData);
                            $result->setDependentCalcData($dependentCalcData);
                            $result->markSucceed();
                        }
                    } else {
                        /* dependent period has different begin/end then related base period */
                        $this->_logger->warning("There is no period for '$dependentCalcTypeCode' calculation based on '$baseCalcTypeCode' ($baseDsBegin-$baseDsEnd). New period and related calculation will be created.");
                        $dependPeriodData = $this->_repoMod->addPeriod($dependentCalcTypeId, $baseDsBegin, $baseDsEnd);
                        $result->setDependentPeriodData($dependPeriodData->getData(IModule::A_PERIOD));
                        $result->setDependentCalcData($dependPeriodData->getData(IModule::A_CALC));
                        $result->markSucceed();
                    }
                }
            } else {
                /* there is no complete Base Calculation */
                $this->_logger->warning("There is no complete base '$baseCalcTypeCode' calculation for dependent '$dependentCalcTypeCode' calculation. New period could not be created.");
            }
        }
        $this->_logger->info("'Get latest period for Dependent Calculation' operation is completed.");
        return $result;
    }

    public function getForPvBasedCalc(Request\GetForPvBasedCalc $request)
    {
        $result = new Response\GetForPvBasedCalc();
        $calcTypeCode = $request->getCalcTypeCode();
        $this->_logger->info("'Get latest period for PV based calc' operation is started in bonus base module (type code '$calcTypeCode').");
        /* get calculation type ID by type code */
        $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
        $data = $this->_repoMod->getLatestPeriod($calcTypeId);
        $periodData = $data->getData(IModule::A_PERIOD);
        if (is_null($periodData)) {
            /* we should lookup for first PV transaction and calculate first period range */
            $ts = $this->_repoMod->getFirstDateForPvTransactions();
            if ($ts === false) {
                $this->_logger->warning("There is no PV transactions yet. Nothing to do.");
                $result->setErrorCode(Response\GetForPvBasedCalc::ERR_HAS_NO_PV_TRANSACTIONS_YET);
            } else {
                $this->_logger->info("First PV transaction was performed at '$ts'.");
                $periodMonth = $this->_toolPeriod->getPeriodCurrent($ts, ToolPeriod::TYPE_MONTH);
                $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
                $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
                $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
                $result->setPeriodData($data->getData(RepoModule::A_PERIOD));
                $result->setCalcData($data->getData(RepoModule::A_CALC));
                $result->markSucceed();
            }
        } else {
            $result->setPeriodData($periodData);
            $periodId = $periodData[Period::ATTR_ID];
            $this->_logger->info("There is registered period #$periodId for '$calcTypeCode' calculation.");
            $calcData = $data->getData(IModule::A_CALC);
            if (!is_array($calcData)) {
                $this->_logger->error("There is no calculation data for existing period ($calcTypeCode).");
                $result->setErrorCode(Response\GetForPvBasedCalc::ERR_NO_CALC_FOR_EXISTING_PERIOD);
            } else {
                if (
                    is_array($calcData) &&
                    isset($calcData[Calculation::ATTR_STATE]) &&
                    ($calcData[Calculation::ATTR_STATE] == Cfg::CALC_STATE_COMPLETE)
                ) {
                    $this->_logger->info("There is complete calculation for existing period. Create new period.");
                    $periodEnd = $periodData[Period::ATTR_DSTAMP_END];
                    /* calculate new period bounds */
                    $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, ToolPeriod::TYPE_MONTH);
                    $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
                    $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
                    /* check "right" bound according to now */
                    $periodNow = $this->_toolPeriod->getPeriodCurrent(time(), ToolPeriod::TYPE_MONTH);
                    $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
                    if ($dsNextEnd < $dsNowEnd) {
                        /* registry new period */
                        $newPeriodData = $this->_repoMod->addPeriod($calcTypeId, $dsNextBegin, $dsNextEnd);
                        $result->setPeriodData($newPeriodData->getData(RepoModule::A_PERIOD));
                        $result->setCalcData($newPeriodData->getData(RepoModule::A_CALC));
                        $result->markSucceed();
                    } else {
                        $this->_logger->warning("New period can be registered in the past only (to register: $dsNextBegin-$dsNextEnd, current end: $dsNowEnd).");
                        $result->setErrorCode(Response\GetForPvBasedCalc::ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY);
                    }
                } else {
                    $this->_logger->info("There is no complete calculation for existing period. Use existing period data.");
                    $result->setCalcData($calcData);
                    $result->markSucceed();
                }
            }
        }
        $this->_logger->info("'Get latest period for PV based calc' operation is completed in bonus base module.");
        return $result;
    }

    /**
     * @param Request\GetLatest $request
     */
    public function getLatest(Request\GetLatest $request)
    {
        $result = new Response\GetLatest();
        $calcTypeId = $request->getCalcTypeId();
        $calcTypeCode = $request->getCalcTypeCode();
        $shouldGetAllCalcs = $request->getShouldGetAllCalcs();
        $shouldGetLatestCalc = $request->getShouldGetLatestCalc();
        $msgParams = is_null($calcTypeId) ? "type code '$calcTypeCode'" : "type ID #$calcTypeId";
        $this->_logger->info("'Get latest calculation period' operation is started with $msgParams in bonus base module.");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
            $this->_logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $data = $this->_repoMod->getLatestPeriod($calcTypeId, $shouldGetLatestCalc, $shouldGetAllCalcs);
        $result->setPeriodData($data->getData(IModule::A_PERIOD));
        $result->setCalcData($data->getData(IModule::A_CALC));
        $result->markSucceed();
        $this->_logger->info("'Get latest calculation period' operation is completed in bonus base module.");
        return $result;
    }

    public function registerPeriod(Request\RegisterPeriod $request)
    {
        $result = new Response\RegisterPeriod();
        $calcTypeId = $request->getCalcTypeId();
        $calcTypeCode = $request->getCalcTypeCode();
        $dsBegin = $request->getDateStampBegin();
        $dsEnd = $request->getDateStampEnd();
        $msgParams = is_null($calcTypeId) ? "type code '$calcTypeCode'" : "type ID #$calcTypeId";
        $this->_logger->info("'Register Period' operation is started in bonus base module ($msgParams; $dsBegin-$dsEnd).");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->_repoMod->getTypeCalcIdByCode($calcTypeCode);
            $this->_logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $data = $this->_repoMod->addPeriod($calcTypeId, $dsBegin, $dsEnd);
        $result->setPeriodData($data->getData(IModule::A_PERIOD));
        $result->setCalcData($data->getData(IModule::A_CALC));
        $result->markSucceed();
        $this->_logger->info("'Register Period' operation is completed in bonus base module.");
        return $result;
    }
}