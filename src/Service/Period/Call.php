<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;
use Praxigento\BonusBase\Repo\IModule as RepoModule;
use Praxigento\Core\Tool\IPeriod as ToolPeriod;

class Call
    extends \Praxigento\Core\Service\Base\Call
    implements \Praxigento\BonusBase\Service\IPeriod
{
    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;
    /** @var  \Praxigento\Core\Transaction\Database\IManager */
    protected $_manTrans;
    /** @var \Praxigento\BonusBase\Repo\Entity\ICalculation */
    protected $_repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\IPeriod */
    protected $_repoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Service\IModule */
    protected $_repoService;
    /** @var \Praxigento\BonusBase\Repo\Entity\Type\ICalc */
    protected $_repoTypeCalc;
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var  \Praxigento\Core\Tool\IPeriod */
    protected $_toolPeriod;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Praxigento\Core\Transaction\Database\IManager $manTrans,
        \Praxigento\BonusBase\Repo\Entity\ICalculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\IPeriod $repoPeriod,
        \Praxigento\BonusBase\Repo\Entity\Type\ICalc $repoTypeCalc,
        \Praxigento\BonusBase\Repo\Service\IModule $repoService,
        \Praxigento\Core\Tool\IPeriod $toolPeriod,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        $this->_logger = $logger;
        $this->_manTrans = $manTrans;
        $this->_repoCalc = $repoCalc;
        $this->_repoPeriod = $repoPeriod;
        $this->_repoTypeCalc = $repoTypeCalc;
        $this->_repoService = $repoService;
        $this->_toolPeriod = $toolPeriod;
        $this->_toolDate = $toolDate;
    }

    public function addCalc(Request\AddCalc $request)
    {
        $result = new Response\AddCalc();
        $calcTypeId = $request->getCalcTypeId();
        $dsBegin = $request->getDateStampBegin();
        $dsEnd = $request->getDateStampEnd();
        $def = $this->_manTrans->begin();
        try {
            /* create new period for given calculation type */
            $periodData = [
                Period::ATTR_CALC_TYPE_ID => $calcTypeId,
                Period::ATTR_DSTAMP_BEGIN => $dsBegin,
                Period::ATTR_DSTAMP_END => $dsEnd
            ];
            $periodId = $this->_repoPeriod->create($periodData);
            /* create new calculation for the period */
            $dateStarted = $this->_toolDate->getUtcNowForDb();
            $calcData = [
                Calculation::ATTR_PERIOD_ID => $periodId,
                Calculation::ATTR_DATE_STARTED => $dateStarted,
                Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
            ];
            $calcId = $this->_repoCalc->create($calcData);
            $this->_manTrans->commit($def);
            /* compose response */
            $periodData[Period::ATTR_ID] = $periodId;
            $calcData[Calculation::ATTR_ID] = $calcId;
            $result->setPeriod($periodData);
            $result->setCalculation($calcData);
            $result->markSucceed();
        } finally {
            $this->_manTrans->end($def);
        }
        return $result;
    }

    public function getForDependentCalc(Request\GetForDependentCalc $request)
    {
        $result = new Response\GetForDependentCalc();
        $dependentCalcTypeCode = $request->getDependentCalcTypeCode();
        $baseCalcTypeCode = $request->getBaseCalcTypeCode();
        $msg = "'Get latest period for Dependent Calculation' operation is started "
            . "(dependent=$dependentCalcTypeCode, base=$baseCalcTypeCode).";
        $this->_logger->info($msg);

        /* get IDs for calculations codes */
        $dependentCalcTypeId = $this->_repoTypeCalc->getIdByCode($dependentCalcTypeCode);
        $baseCalcTypeId = $this->_repoTypeCalc->getIdByCode($baseCalcTypeCode);
        /* get the last base period data */
        $reqLatest = new Request\GetLatest();
        $reqLatest->setCalcTypeId($baseCalcTypeId);
        $latestPeriodData = $this->getLatest($reqLatest);
        $basePeriodData = $latestPeriodData->getPeriodData();
        if (is_null($basePeriodData)) {
            $msg = "There is no period for '$baseCalcTypeCode' calculation  yet. "
                . "'$dependentCalcTypeCode' could not be calculated.";
            $this->_logger->warning($msg);
        } else {
            $result->setBasePeriodData($basePeriodData);
            $baseCalcData = $latestPeriodData->getCalcData();
            $result->setBaseCalcData($baseCalcData);
            $baseDsBegin = $basePeriodData->getDstampBegin();
            $baseDsEnd = $basePeriodData->getDstampEnd();
            if (
                $baseCalcData &&
                ($baseCalcData->getState() == Cfg::CALC_STATE_COMPLETE)
            ) {
                /* there is complete Base Calculation */
                $reqLatest = new Request\GetLatest();
                $reqLatest->setCalcTypeId($dependentCalcTypeId);
                $respDependentPeriod = $this->getLatest($reqLatest);
                $dependPeriodData = $respDependentPeriod->getPeriodData();
                $dependentCalcData = $respDependentPeriod->getCalcData();
                if (is_null($dependPeriodData)) {
                    /* there is no dependent period */
                    $msg = "There is no period data for calculation '$dependentCalcTypeCode'."
                        . " New period and related calculation will be created.";
                    $this->_logger->warning($msg);
                    $reqAddCalc = new Request\AddCalc();
                    $reqAddCalc->setCalcTypeId($dependentCalcTypeId);
                    $reqAddCalc->setDateStampBegin($baseDsBegin);
                    $reqAddCalc->setDateStampEnd($baseDsEnd);
                    $respAddCalc = $this->addCalc($reqAddCalc);
                    $result->setDependentPeriodData($respAddCalc->getPeriod());
                    $result->setDependentCalcData($respAddCalc->getCalculation());
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
                        $reqAddCalc = new Request\AddCalc();
                        $reqAddCalc->setCalcTypeId($dependentCalcTypeId);
                        $reqAddCalc->setDateStampBegin($baseDsBegin);
                        $reqAddCalc->setDateStampEnd($baseDsEnd);
                        $dependPeriodData = $this->addCalc($reqAddCalc);
                        $result->setDependentPeriodData($dependPeriodData->getPeriod());
                        $result->setDependentCalcData($dependPeriodData->getCalculation());
                        $result->markSucceed();
                    }
                }
            } else {
                /* there is no complete Base Calculation */
                $msg = "There is no complete base '$baseCalcTypeCode' calculation for dependent "
                    . "'$dependentCalcTypeCode' calculation. New period could not be created.";
                $this->_logger->warning($msg);
            }
        }
        $this->_logger->info("'Get latest period for Dependent Calculation' operation is completed.");
        return $result;
    }

    public function getForPvBasedCalc(Request\GetForPvBasedCalc $request)
    {
        $result = new Response\GetForPvBasedCalc();
        $calcTypeCode = $request->getCalcTypeCode();
        $periodType = $request->getPeriodType() ?? ToolPeriod::TYPE_MONTH;
        $this->_logger->info("'Get latest period for PV based calc' operation is started in bonus base module (type code '$calcTypeCode').");
        /* get calculation type ID by type code */
        $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
        $reqLatest = new Request\GetLatest();
        $reqLatest->setCalcTypeId($calcTypeId);
        $data = $this->getLatest($reqLatest);
        $periodData = $data->getPeriodData();
        if (is_null($periodData)) {
            /* we should lookup for first PV transaction and calculate first period range */
            $ts = $this->_repoService->getFirstDateForPvTransactions();
            if ($ts === false) {
                $this->_logger->warning("There is no PV transactions yet. Nothing to do.");
                $result->setErrorCode(Response\GetForPvBasedCalc::ERR_HAS_NO_PV_TRANSACTIONS_YET);
            } else {
                $this->_logger->info("First PV transaction was performed at '$ts'.");
                $periodMonth = $this->_toolPeriod->getPeriodCurrent($ts, $periodType);
                $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
                $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
                $reqAddCalc = new Request\AddCalc();
                $reqAddCalc->setCalcTypeId($calcTypeId);
                $reqAddCalc->setDateStampBegin($dsBegin);
                $reqAddCalc->setDateStampEnd($dsEnd);
                $data = $this->addCalc($reqAddCalc);
                $result->setPeriodData($data->getPeriod());
                $result->setCalcData($data->getCalculation());
                $result->markSucceed();
            }
        } else {
            $result->setPeriodData($periodData);
            $periodId = $periodData[Period::ATTR_ID];
            $this->_logger->info("There is registered period #$periodId for '$calcTypeCode' calculation.");
            $calcData = $data->getCalcData();
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
                        $reqAddCalc = new Request\AddCalc();
                        $reqAddCalc->setCalcTypeId($calcTypeId);
                        $reqAddCalc->setDateStampBegin($dsNextBegin);
                        $reqAddCalc->setDateStampEnd($dsNextEnd);
                        $newPeriodData = $this->addCalc($reqAddCalc);
                        $result->setPeriodData($newPeriodData->getPeriod());
                        $result->setCalcData($newPeriodData->getCalculation());
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

    public function getLatest(Request\GetLatest $request)
    {
        $result = new Response\GetLatest();
        $calcTypeId = $request->getCalcTypeId();
        $calcTypeCode = $request->getCalcTypeCode();
        $msgParams = is_null($calcTypeId) ? "type code '$calcTypeCode'" : "type ID #$calcTypeId";
        $this->_logger->info("'Get latest calculation period' operation is started with $msgParams in bonus base module.");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
            $this->_logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $periodLatest = $this->_repoService->getLastPeriodByCalcType($calcTypeId);
        if ($periodLatest) {
            $result->setPeriodData($periodLatest);
            /* add period calculations to result set */
            $periodId = $periodLatest->getId();
            $calcLatest = $this->_repoService->getLastCalcForPeriod($periodId);
            $result->setCalcData($calcLatest);
        }
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
            $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
            $this->_logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $reqAddCalc = new Request\AddCalc();
        $reqAddCalc->setCalcTypeId($calcTypeId);
        $reqAddCalc->setDateStampBegin($dsBegin);
        $reqAddCalc->setDateStampEnd($dsEnd);
        $data = $this->addCalc($reqAddCalc);
        $result->setPeriodData($data->getPeriod());
        $result->setCalcData($data->getCalculation());
        $result->markSucceed();
        $this->_logger->info("'Register Period' operation is completed in bonus base module.");
        return $result;
    }
}