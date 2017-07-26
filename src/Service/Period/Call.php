<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;
use Praxigento\Core\Tool\IPeriod as ToolPeriod;

class Call
    extends \Praxigento\Core\Service\Base\Call
    implements \Praxigento\BonusBase\Service\IPeriod
{
    /** @var  \Praxigento\Core\Transaction\Database\IManager */
    protected $_manTrans;
    /** @var \Praxigento\BonusBase\Repo\Entity\Def\Calculation */
    protected $_repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\Def\Period */
    protected $_repoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Service\IModule */
    protected $_repoService;
    /** @var \Praxigento\BonusBase\Repo\Entity\Type\Def\Calc */
    protected $_repoTypeCalc;
    /** @var  \Praxigento\BonusBase\Service\Period\Sub\Depended */
    protected $_subDepended;
    /** @var \Praxigento\BonusBase\Service\Period\Sub\PvBased */
    protected $_subPvBased;
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var  \Praxigento\Core\Tool\IPeriod */
    protected $_toolPeriod;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(
        \Praxigento\Core\Fw\Logger\App $logger,
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Transaction\Database\IManager $manTrans,
        \Praxigento\BonusBase\Repo\Entity\Def\Calculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\Def\Period $repoPeriod,
        \Praxigento\BonusBase\Repo\Entity\Type\Def\Calc $repoTypeCalc,
        \Praxigento\BonusBase\Repo\Service\IModule $repoService,
        \Praxigento\Core\Tool\IPeriod $toolPeriod,
        \Praxigento\Core\Tool\IDate $toolDate,
        \Praxigento\BonusBase\Service\Period\Sub\Depended $subDepended,
        \Praxigento\BonusBase\Service\Period\Sub\PvBased $subPvBased
    ) {
        parent::__construct($logger, $manObj);
        $this->_manTrans = $manTrans;
        $this->_repoCalc = $repoCalc;
        $this->_repoPeriod = $repoPeriod;
        $this->_repoTypeCalc = $repoTypeCalc;
        $this->_repoService = $repoService;
        $this->_toolPeriod = $toolPeriod;
        $this->_toolDate = $toolDate;
        $this->_subDepended = $subDepended;
        $this->_subPvBased = $subPvBased;
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
        $baseCalcTypeCode = $request->getBaseCalcTypeCode();
        $dependentCalcTypeCode = $request->getDependentCalcTypeCode();
        $msg = "'Get latest period for Dependent Calculation' operation is started "
            . "(dependent=$dependentCalcTypeCode, base=$baseCalcTypeCode).";
        $this->logger->info($msg);
        $def = $this->_manTrans->begin();
        try {
            /* get IDs for calculations codes */
            $baseCalcTypeId = $this->_repoTypeCalc->getIdByCode($baseCalcTypeCode);
            $dependentCalcTypeId = $this->_repoTypeCalc->getIdByCode($dependentCalcTypeCode);
            /* get the last base period data from repo */
            $basePeriodData = $this->_repoService->getLastPeriodByCalcType($baseCalcTypeId);
            if (is_null($basePeriodData)) {
                $msg = "There is no period for '$baseCalcTypeCode' calculation yet (base). "
                    . "Depended '$dependentCalcTypeCode' could not be calculated.";
                $this->logger->warning($msg);
            } else {
                /* there is period for base calculation, place base data into response */
                $result->setBasePeriodData($basePeriodData);
                $baseDsBegin = $basePeriodData->getDstampBegin();
                $baseDsEnd = $basePeriodData->getDstampEnd();
                /* then get data for depended period & calc */
                $periodId = $basePeriodData->getId();
                $result = $this->_subDepended->getDependedCalc(
                    $result,
                    $periodId,
                    $baseCalcTypeCode,
                    $baseDsBegin,
                    $baseDsEnd,
                    $dependentCalcTypeCode,
                    $dependentCalcTypeId
                );
            }
            $this->_manTrans->commit($def);
            /* mark succeed if depended data exists */
            if (
                $result->getDependentPeriodData() &&
                $result->getDependentCalcData()
            ) {
                $result->markSucceed();
            }
        } finally {
            $this->_manTrans->end($def);
        }
        $this->logger->info("'Get latest period for Dependent Calculation' operation is completed.");
        return $result;
    }

    public function getForPvBasedCalc(Request\GetForPvBasedCalc $request)
    {
        $result = new Response\GetForPvBasedCalc();
        $calcTypeCode = $request->getCalcTypeCode();
        $periodType = $request->getPeriodType() ?? ToolPeriod::TYPE_MONTH;
        $msg = "'Get latest period for PV based calc' operation is started in bonus base module "
            . "(type code: $calcTypeCode; period: $periodType).";
        $this->logger->info($msg);
        /* get calculation type ID by type code */
        $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
        $def = $this->_manTrans->begin();
        try {
            $reqLatest = new Request\GetLatest();
            $reqLatest->setCalcTypeId($calcTypeId);
            $latestPeriod = $this->getLatest($reqLatest);
            $periodData = $latestPeriod->getPeriodData();
            if (is_null($periodData)) {
                $result = $this->_subPvBased->getNewPeriodDataForPv($result, $periodType, $calcTypeId);
            } else {
                $result->setPeriodData($periodData);
                $periodId = $periodData->getId();
                $this->logger->info("There is registered period #$periodId for '$calcTypeCode' calculation.");
                $calcData = $latestPeriod->getCalcData();
                $result = $this->_subPvBased->checkExistingPeriod(
                    $result, $calcTypeCode, $calcTypeId, $periodType, $periodData, $calcData
                );
            }
            /* mark succeed if period data exists */
            if ($result->getPeriodData() && $result->getCalcData()) {
                $this->_manTrans->commit($def);
                $result->markSucceed();
            }
        } finally {
            $this->_manTrans->end($def);
        }
        $this->logger->info("'Get latest period for PV based calc' operation is completed in bonus base module.");
        return $result;
    }

    public function getLatest(Request\GetLatest $request)
    {
        $result = new Response\GetLatest();
        $calcTypeId = $request->getCalcTypeId();
        $calcTypeCode = $request->getCalcTypeCode();
        $msgParams = is_null($calcTypeId) ? "type code '$calcTypeCode'" : "type ID #$calcTypeId";
        $this->logger->info("'Get latest calculation period' operation is started with $msgParams in bonus base module.");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
            $this->logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $periodLatest = $this->_repoService->getLastPeriodByCalcType($calcTypeId);
        if ($periodLatest) {
            $result->setPeriodData($periodLatest);
            /* add period calculations to result set */
            $periodId = $periodLatest->getId();
            $calcLatest = $this->_repoService->getLastCalcForPeriodById($periodId);
            $result->setCalcData($calcLatest);
        }
        $result->markSucceed();
        $this->logger->info("'Get latest calculation period' operation is completed in bonus base module.");
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
        $this->logger->info("'Register Period' operation is started in bonus base module ($msgParams; $dsBegin-$dsEnd).");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->_repoTypeCalc->getIdByCode($calcTypeCode);
            $this->logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $reqAddCalc = new Request\AddCalc();
        $reqAddCalc->setCalcTypeId($calcTypeId);
        $reqAddCalc->setDateStampBegin($dsBegin);
        $reqAddCalc->setDateStampEnd($dsEnd);
        $data = $this->addCalc($reqAddCalc);
        $result->setPeriodData($data->getPeriod());
        $result->setCalcData($data->getCalculation());
        $result->markSucceed();
        $this->logger->info("'Register Period' operation is completed in bonus base module.");
        return $result;
    }
}