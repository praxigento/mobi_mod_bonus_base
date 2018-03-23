<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Sub;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalculation;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;

/**
 * Period service's internal code for depended calculations.
 */
class Depended
{
    /** @var \Praxigento\Core\Api\Helper\Date */
    protected $hlpDate;
    /** @var \Praxigento\Core\Api\App\Logger\Main */
    protected $logger;
    /** @var \Praxigento\BonusBase\Repo\Dao\Calculation */
    protected $daoCalc;
    /** @var \Praxigento\BonusBase\Repo\Dao\Period */
    protected $daoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Service\IModule */
    protected $daoService;

    public function __construct(
        \Praxigento\Core\Api\App\Logger\Main $logger,
        \Praxigento\BonusBase\Repo\Dao\Calculation $daoCalc,
        \Praxigento\BonusBase\Repo\Dao\Period $daoPeriod,
        \Praxigento\BonusBase\Repo\Service\IModule $daoService,
        \Praxigento\Core\Api\Helper\Date $hlpDate

    ) {
        $this->logger = $logger;
        $this->daoCalc = $daoCalc;
        $this->daoPeriod = $daoPeriod;
        $this->daoService = $daoService;
        $this->hlpDate = $hlpDate;
    }

    /**
     * Analyze depended calculation data and to the results if state is incomplete.
     *
     * This function is created for CRAP reducing and is used from this class only.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result
     * @param string $dependentCalcTypeCode
     * @param int $dependentCalcTypeId
     * @param string $dependentDsBegin
     * @param string $dependentDsEnd
     */
    public function _analyzeDependedCalc(
        \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result,
        $dependentCalcTypeCode,
        $dependentCalcTypeId,
        $dependentDsBegin,
        $dependentDsEnd
    ) {
        $dependentCalcData = $this->daoService->getLastCalcForPeriodByDates(
            $dependentCalcTypeId,
            $dependentDsBegin,
            $dependentDsEnd
        );
        if (
            $dependentCalcData &&
            ($dependentCalcData->getState() == Cfg::CALC_STATE_COMPLETE)
        ) {
            /* complete dependent period for complete base period */
            $this->logger->warning("There is '$dependentCalcTypeCode' period with complete calculation. No more '$dependentCalcTypeCode' could be calculated.");
        } else {
            /* incomplete dependent period for complete base period */
            $this->logger->warning("There is '$dependentCalcTypeCode' period without complete calculation. Continue calculation for this period.");
            $result->setDependentCalcData($dependentCalcData);
        }
    }

    /**
     * Create new depended period and calculation or return existing data.
     *
     * This function is created for CRAP reducing and is used from this class only.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result
     * @param string $baseCalcTypeCode
     * @param string $baseDsBegin
     * @param string $baseDsEnd
     * @param string $dependentCalcTypeCode
     * @param int $dependentCalcTypeId
     * @param string $dependentDsBegin
     * @param string $dependentDsEnd
     */
    public function _getDependedCalcForExistingPeriod(
        \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result,
        $baseCalcTypeCode,
        $baseDsBegin,
        $baseDsEnd,
        $dependentCalcTypeCode,
        $dependentCalcTypeId,
        $dependentDsBegin,
        $dependentDsEnd
    ) {
        if (
            ($dependentDsBegin == $baseDsBegin) &&
            ($dependentDsEnd == $baseDsEnd)
        ) {
            /* dependent period has the same begin/end as related base period, get calc data */
            $this->logger->info("There is base '$baseCalcTypeCode' period for dependent '$dependentCalcTypeCode' period ($dependentDsBegin-$dependentDsEnd).");
            $this->_analyzeDependedCalc(
                $result,
                $dependentCalcTypeCode,
                $dependentCalcTypeId,
                $dependentDsBegin,
                $dependentDsEnd
            );
        } else {
            /* dependent period has different begin/end than related base period */
            $this->logger->warning("There is no period for '$dependentCalcTypeCode' calculation based on '$baseCalcTypeCode' ($baseDsBegin-$baseDsEnd). New period and related calculation will be created.");
            /* create new depended period & calc */
            $period = new EPeriod();
            $period->setCalcTypeId($dependentCalcTypeId);
            $period->setDstampBegin($baseDsBegin);
            $period->setDstampEnd($baseDsEnd);
            $periodId = $this->daoPeriod->create($period);
            $period->setId($periodId);
            /* create related calculation */
            $calc = new ECalculation();
            $calc->setPeriodId($periodId);
            $dateStarted = $this->hlpDate->getUtcNowForDb();
            $calc->setDateStarted($dateStarted);
            $calc->setState(Cfg::CALC_STATE_STARTED);
            $calcId = $this->daoCalc->create($calc);
            $calc->setId($calcId);
            /* place new objects into response */
            $result->setDependentPeriodData($period);
            $result->setDependentCalcData($calc);
        }
    }

    /**
     * This function is created for CRAP reducing and is used from this class only.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result
     * @param string $baseCalcTypeCode
     * @param string $baseDsBegin
     * @param string $baseDsEnd
     * @param string $dependentCalcTypeCode
     * @param int $dependentCalcTypeId
     */
    public function _getForCompleteBase(
        \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result,
        $baseCalcTypeCode,
        $baseDsBegin,
        $baseDsEnd,
        $dependentCalcTypeCode,
        $dependentCalcTypeId
    ) {
        $dependPeriodData = $this->daoService->getLastPeriodByCalcType($dependentCalcTypeId);
        if (is_null($dependPeriodData)) {
            /* there is no dependent period, create new period and calc */
            $msg = "There is no period data for calculation '$dependentCalcTypeCode'."
                . " New period and related calculation will be created.";
            $this->logger->warning($msg);
            /* create new period for given calculation type */
            $period = new EPeriod();
            $period->setCalcTypeId($dependentCalcTypeId);
            $period->setDstampBegin($baseDsBegin);
            $period->setDstampEnd($baseDsEnd);
            $periodId = $this->daoPeriod->create($period);
            $period->setId($periodId);
            /* create related calculation */
            $calc = new ECalculation();
            $calc->setPeriodId($periodId);
            $dateStarted = $this->hlpDate->getUtcNowForDb();
            $calc->setDateStarted($dateStarted);
            $calc->setState(Cfg::CALC_STATE_STARTED);
            $calcId = $this->daoCalc->create($calc);
            $calc->setId($calcId);
            /* place newly created objects into the response */
            $result->setDependentPeriodData($period);
            $result->setDependentCalcData($calc);
        } else {
            /* there is depended period, place period data into response */
            $result->setDependentPeriodData($dependPeriodData);
            /* then analyze base/depended periods begin/end  */
            $dependentDsBegin = $dependPeriodData->getDstampBegin();
            $dependentDsEnd = $dependPeriodData->getDstampEnd();
            $this->_getDependedCalcForExistingPeriod(
                $result,
                $baseCalcTypeCode,
                $baseDsBegin,
                $baseDsEnd,
                $dependentCalcTypeCode,
                $dependentCalcTypeId,
                $dependentDsBegin,
                $dependentDsEnd
            );
        }
    }

    /**
     * Sub-functionality to get period and calculation data for depended calculation.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result
     * @param int $basePeriodId
     * @param string $baseCalcTypeCode
     * @param string $baseDsBegin
     * @param string $baseDsEnd
     * @param string $dependentCalcTypeCode
     * @param int $dependentCalcTypeId
     * @return \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc
     */
    public function getDependedCalc(
        \Praxigento\BonusBase\Service\Period\Response\GetForDependentCalc $result,
        $basePeriodId,
        $baseCalcTypeCode,
        $baseDsBegin,
        $baseDsEnd,
        $dependentCalcTypeCode,
        $dependentCalcTypeId
    ) {
        $baseCalcData = $this->daoService->getLastCalcForPeriodById($basePeriodId);
        $result->setBaseCalcData($baseCalcData);
        /* get depended data for complete base calculation */
        if (
            $baseCalcData &&
            ($baseCalcData->getState() == Cfg::CALC_STATE_COMPLETE)
        ) {
            /* there is complete base calculation, get period for depended calc */
            $this->_getForCompleteBase(
                $result,
                $baseCalcTypeCode,
                $baseDsBegin,
                $baseDsEnd,
                $dependentCalcTypeCode,
                $dependentCalcTypeId
            );
        } else {
            /* there is no complete Base Calculation */
            $msg = "There is no complete base '$baseCalcTypeCode' calculation for dependent "
                . "'$dependentCalcTypeCode' calculation. New period could not be created.";
            $this->logger->warning($msg);
        }
        return $result;
    }

}