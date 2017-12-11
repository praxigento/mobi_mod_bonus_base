<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Sub;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Entity\Data\Calculation as ECalculation;
use Praxigento\BonusBase\Repo\Entity\Data\Period as EPeriod;
use Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc as ResponsePv;

/**
 * Period service's internal code for PV based calculations.
 */
class PvBased
{
    const DEF_PERIOD = \Praxigento\Core\Tool\IPeriod::TYPE_MONTH;
    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;
    /** @var \Praxigento\BonusBase\Repo\Entity\Calculation */
    protected $_repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\Period */
    protected $_repoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Service\IModule */
    protected $_repoService;
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var \Praxigento\Core\Tool\IPeriod */
    protected $_toolPeriod;

    public function __construct(
        \Praxigento\Core\App\Logger\App $logger,
        \Praxigento\BonusBase\Repo\Entity\Calculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\Period $repoPeriod,
        \Praxigento\BonusBase\Repo\Service\IModule $repoService,
        \Praxigento\Core\Tool\IDate $toolDate,
        \Praxigento\Core\Tool\IPeriod $toolPeriod

    ) {
        $this->_logger = $logger;
        $this->_repoCalc = $repoCalc;
        $this->_repoPeriod = $repoPeriod;
        $this->_repoService = $repoService;
        $this->_toolDate = $toolDate;
        $this->_toolPeriod = $toolPeriod;
    }

    /**
     * Check existing period and registry new one if current calculation is complete.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result
     * @param int $calcTypeId
     * @param string $periodType see \Praxigento\Core\Tool\IPeriod::TYPE_*
     * @param \Praxigento\BonusBase\Repo\Entity\Data\Period $periodData
     * @param \Praxigento\BonusBase\Repo\Entity\Data\Calculation $calcData
     * @return \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc
     */
    public function _checkStateForExistingPeriod(
        \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result,
        $calcTypeId,
        $periodType,
        \Praxigento\BonusBase\Repo\Entity\Data\Period $periodData,
        \Praxigento\BonusBase\Repo\Entity\Data\Calculation $calcData
    ) {
        if ($calcData->getState() == Cfg::CALC_STATE_COMPLETE) {
            $this->_logger->info("There is complete calculation for existing period. Create new period.");
            $result = $this->_registryNextPeriod($result, $calcTypeId, $periodType, $periodData);
        } else {
            $this->_logger->info("There is no complete calculation for existing period. Use existing period data.");
            $result->setCalcData($calcData);
        }
        return $result;
    }

    /**
     * Registry next period if current period has complete calculation.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result
     * @param int $calcTypeId
     * @param string $periodType see \Praxigento\Core\Tool\IPeriod::TYPE_*
     * @param \Praxigento\BonusBase\Repo\Entity\Data\Period $periodData
     * @return \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc
     */
    public function _registryNextPeriod(
        \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result,
        $calcTypeId,
        $periodType,
        \Praxigento\BonusBase\Repo\Entity\Data\Period $periodData
    ) {
        $periodEnd = $periodData->getDstampEnd();
        /* calculate new period bounds */
        $periodNext = $this->_toolPeriod->getPeriodNext($periodEnd, $periodType);
        $dsNextBegin = $this->_toolPeriod->getPeriodFirstDate($periodNext);
        $dsNextEnd = $this->_toolPeriod->getPeriodLastDate($periodNext);
        /* check "right" bound according to now */
        $periodNow = $this->_toolPeriod->getPeriodCurrentOld(time(), $periodType);
        $dsNowEnd = $this->_toolPeriod->getPeriodLastDate($periodNow);
        if ($dsNextEnd < $dsNowEnd) {
            /* registry new period */
            /* create new period for given calculation type */
            $newPeriod = new EPeriod();
            $newPeriod->setCalcTypeId($calcTypeId);
            $newPeriod->setDstampBegin($dsNextBegin);
            $newPeriod->setDstampEnd($dsNextEnd);
            $periodId = $this->_repoPeriod->create($newPeriod);
            $newPeriod->setId($periodId);
            /* create related calculation */
            $newCalc = new ECalculation();
            $newCalc->setPeriodId($periodId);
            $dateStarted = $this->_toolDate->getUtcNowForDb();
            $newCalc->setDateStarted($dateStarted);
            $newCalc->setState(Cfg::CALC_STATE_STARTED);
            $calcId = $this->_repoCalc->create($newCalc);
            $newCalc->setId($calcId);
            $result->setPeriodData($newPeriod);
            $result->setCalcData($newCalc);
        } else {
            $msg = "New period can be registered in the past only (to register: $dsNextBegin-$dsNextEnd, "
                . "current end: $dsNowEnd).";
            $this->_logger->warning($msg);
            $result->setErrorCode(ResponsePv::ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY);
        }
        return $result;
    }

    /**
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result
     * @param string $calcTypeCode
     * @param int $calcTypeId
     * @param string $periodType see \Praxigento\Core\Tool\IPeriod::TYPE_*
     * @param \Praxigento\BonusBase\Repo\Entity\Data\Period|null $periodData
     * @param \Praxigento\BonusBase\Repo\Entity\Data\Calculation|null $calcData
     * @return \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc
     */
    public function checkExistingPeriod(
        \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result,
        $calcTypeCode,
        $calcTypeId,
        $periodType,
        \Praxigento\BonusBase\Repo\Entity\Data\Period $periodData = null,
        \Praxigento\BonusBase\Repo\Entity\Data\Calculation $calcData = null
    ) {
        if (!$calcData) {
            $this->_logger->error("There is no calculation data for existing period ($calcTypeCode).");
            $result->setErrorCode(ResponsePv::ERR_NO_CALC_FOR_EXISTING_PERIOD);
        } else {
            $result = $this->_checkStateForExistingPeriod($result, $calcTypeId, $periodType, $periodData, $calcData);
        }
        return $result;
    }

    /**
     *
     * Get PV related period data if no period yet exist.
     *
     * @param \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result
     * @param string $periodType
     * @param int $calcTypeId
     * @return \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc
     */
    public function getNewPeriodDataForPv(
        \Praxigento\BonusBase\Service\Period\Response\GetForPvBasedCalc $result,
        $periodType,
        $calcTypeId
    ) {
        /* we should lookup for first PV transaction and calculate first period range */
        $firstDate = $this->_repoService->getFirstDateForPvTransactions();
        if ($firstDate === false) {
            $this->_logger->warning("There is no PV transactions yet. Nothing to do.");
            $result->setErrorCode($result::ERR_HAS_NO_PV_TRANSACTIONS_YET);
        } else {
            $this->_logger->info("First PV transaction was performed at '$firstDate'.");
            $periodMonth = $this->_toolPeriod->getPeriodCurrentOld($firstDate, $periodType);
            $dsBegin = $this->_toolPeriod->getPeriodFirstDate($periodMonth);
            $dsEnd = $this->_toolPeriod->getPeriodLastDate($periodMonth);
            /* create new period for given calculation type */
            $period = new EPeriod();
            $period->setCalcTypeId($calcTypeId);
            $period->setDstampBegin($dsBegin);
            $period->setDstampEnd($dsEnd);
            $periodId = $this->_repoPeriod->create($period);
            $period->setId($periodId);
            /* create related calculation */
            $calc = new ECalculation();
            $calc->setPeriodId($periodId);
            $dateStarted = $this->_toolDate->getUtcNowForDb();
            $calc->setDateStarted($dateStarted);
            $calc->setState(Cfg::CALC_STATE_STARTED);
            $calcId = $this->_repoCalc->create($calc);
            $calc->setId($calcId);
            /* place newly created objects into the response */
            $result->setPeriodData($period);
            $result->setCalcData($calc);
        }
        return $result;
    }
}