<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

use Praxigento\BonusBase\Repo\Entity\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Entity\Data\Type\Calc as ECalcType;
use Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder as QBGetLast;
use Praxigento\BonusHybrid\Config as Cfg;

class Dependent
    implements IDependent
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var \Praxigento\BonusBase\Service\Period\Calc\IAdd */
    protected $procCalcAdd;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
    protected $qbGetPeriod;
    /** @var \Praxigento\BonusBase\Repo\Entity\Calculation */
    protected $repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\Period */
    protected $repoPeriod;

    public function __construct(
        \Praxigento\Core\Fw\Logger\App $logger,
        \Praxigento\BonusBase\Repo\Entity\Calculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\Period $repoPeriod,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder $qbGetPeriod,
        \Praxigento\BonusBase\Service\Period\Calc\IAdd $procCalcAdd
    )
    {
        $this->logger = $logger;
        $this->repoCalc = $repoCalc;
        $this->repoPeriod = $repoPeriod;
        $this->qbGetPeriod = $qbGetPeriod;
        $this->procCalcAdd = $procCalcAdd;
    }

    /**
     * Registry new period and related calculation.
     *
     * @param string $dsBegin
     * @param string $dsEnd
     * @param $calcTypeCode
     * @return [$periodId, $calcId, $err]
     */
    private function addPeriodCalc($dsBegin, $dsEnd, $calcTypeCode)
    {
        /* result data */
        $periodId = $calcId = $err = null;
        /* register new period & calc */
        $ctxAdd = new \Praxigento\Core\Data();
        $ctxAdd->set($this->procCalcAdd::CTX_IN_CALC_TYPE_CODE, $calcTypeCode);
        $ctxAdd->set($this->procCalcAdd::CTX_IN_DSTAMP_BEGIN, $dsBegin);
        $ctxAdd->set($this->procCalcAdd::CTX_IN_DSTAMP_END, $dsEnd);
        $this->procCalcAdd->exec($ctxAdd);
        $success = $ctxAdd->get($this->procCalcAdd::CTX_OUT_SUCCESS);
        if ($success) {
            $periodId = $ctxAdd->get($this->procCalcAdd::CTX_OUT_PERIOD_ID);
            $calcId = $ctxAdd->get($this->procCalcAdd::CTX_OUT_CALC_ID);
            $this->logger->info("New period (#$periodId) and related calculation (#$calcId) are created.");
        } else {
            $err = $ctxAdd->get($this->procCalcAdd::ERR_PERIOD_END_IS_IN_FUTURE);
            if ($err) {
                $this->logger->warning("End of the adding period ($dsEnd) is in the future.");
            }
        }
        /* return results as array */
        return [$periodId, $calcId, $err];
    }

    public function exec(\Praxigento\Core\Data $ctx)
    {
        /* get working data from context */
        $calcTypeCodeBase = $ctx->get(self::CTX_IN_BASE_TYPE_CODE);
        $calcTypeCodeDep = $ctx->get(self::CTX_IN_DEP_TYPE_CODE);

        /**
         * perform processing
         */
        $ctx->set(self::CTX_OUT_SUCCESS, false);
        $this->logger->info("'Get dependent period calculation' processing is started "
            . "(base: $calcTypeCodeBase; dep: $calcTypeCodeDep).");
        /* get the last period data for given calculation type */
        $periodLastBase = $this->queryLastPeriod($calcTypeCodeBase);
        if ($periodLastBase) {
            $baseCalcState = $periodLastBase[QBGetLast::A_CALC_STATE];
            $baseDsBegin = $periodLastBase[QBGetLast::A_DS_BEGIN];
            $baseDsEnd = $periodLastBase[QBGetLast::A_DS_END];
            $basePeriodId = $periodLastBase[QBGetLast::A_PERIOD_ID];
            $baseCalcId = $periodLastBase[QBGetLast::A_CALC_ID];
            if ($baseCalcState == Cfg::CALC_STATE_COMPLETE) {
                /* base calculation is complete, get the last dependent calc */
                $periodLastDep = $this->queryLastPeriod($calcTypeCodeDep);
                if (!$periodLastDep) {
                    /* there is no dependent period, registry new one */
                    $this->logger->warning("There is no period data for calculation '$calcTypeCodeDep'. New period and related calculation will be created.");
                    list($depPeriodId, $depCalcId, $err) = $this->addPeriodCalc($baseDsBegin, $baseDsEnd, $calcTypeCodeDep);
                    $this->populateContext($ctx, $basePeriodId, $baseCalcId, $depPeriodId, $depCalcId, $err);
                } else {
                    /* there is dependent period */
                    $depDsBegin = $periodLastDep[QBGetLast::A_DS_BEGIN];
                    $depDsEnd = $periodLastDep[QBGetLast::A_DS_END];
                    if (
                        ($depDsBegin == $baseDsBegin) &&
                        ($depDsEnd == $baseDsEnd)
                    ) {
                        /* dependent period has the same begin/end as related base period */
                        $this->logger->info("There is base '$calcTypeCodeBase' period for dependent '$calcTypeCodeDep' period ($depDsBegin-$depDsEnd).");
                        $depCalcState = $periodLastDep[QBGetLast::A_CALC_STATE];
                        if ($depCalcState == Cfg::CALC_STATE_COMPLETE) {
                            /* complete dependent period for complete base period */
                            $this->logger->warning("There is '$calcTypeCodeDep' period with complete calculation. No more '$calcTypeCodeDep' could be calculated.");
                            $ctx->set(self::CTX_OUT_ERROR_CODE, self::ERR_DEP_CALC_COMPLETE);
                        } else {
                            /* incomplete dependent period for complete base period */
                            $this->logger->warning("There is '$calcTypeCodeDep' period without complete calculation. Continue calculation for this period.");
                            $depPeriodId = $periodLastDep[QBGetLast::A_PERIOD_ID];
                            $depCalcId = $periodLastDep[QBGetLast::A_CALC_ID];
                            $this->populateContext($ctx, $basePeriodId, $baseCalcId, $depPeriodId, $depCalcId);
                        }
                    } else {
                        /* dependent period has different begin/end then related base period */
                        $this->logger->warning("There is no period for '$calcTypeCodeDep' calculation based on '$calcTypeCodeBase' ($baseDsBegin-$baseDsEnd). New period and related calculation will be created.");
                        list($depPeriodId, $depCalcId, $err) = $this->addPeriodCalc($baseDsBegin, $baseDsEnd, $calcTypeCodeDep);
                        $this->populateContext($ctx, $basePeriodId, $baseCalcId, $depPeriodId, $depCalcId, $err);
                    }
                }
            } else {
                $this->logger->warning("Base calculation ($calcTypeCodeBase) is not complete yet. "
                    . "'$calcTypeCodeDep' could not be started.");
                $ctx->set(self::CTX_OUT_ERROR_CODE, self::ERR_BASE_CALC_NOT_COMPLETE);
            }
        } else {
            $this->logger->warning("There is no period for '$calcTypeCodeBase' calculation  yet. "
                . "'$calcTypeCodeDep' could not be calculated.");
            $ctx->set(self::CTX_OUT_ERROR_CODE, self::ERR_BASE_CALC_NOT_EXIST);
        }

        $this->logger->info("'Get basis period calculation' processing is completed "
            . "(base: $calcTypeCodeBase; dep: $calcTypeCodeDep).");
    }

    /**
     * Populate execution context with result data if no error.
     *
     * @param \Praxigento\Core\Data $ctx
     * @param int $basePeriodId
     * @param int $baseCalcId
     * @param int $depPeriodId
     * @param int $depCalcId
     * @param bool $err error code from self::addPeriodCalc()
     */
    private function populateContext($ctx, $basePeriodId, $baseCalcId, $depPeriodId, $depCalcId, $err = null)
    {
        if (!$err) {
            $ctx->set(self::CTX_OUT_SUCCESS, true);
            $basePeriodData = $this->repoPeriod->getById($basePeriodId);
            $baseCalcData = $this->repoCalc->getById($baseCalcId);
            $depPeriodData = $this->repoPeriod->getById($depPeriodId);
            $depCalcData = $this->repoCalc->getById($depCalcId);
            $ctx->set(self::CTX_OUT_BASE_PERIOD_DATA, $basePeriodData);
            $ctx->set(self::CTX_OUT_BASE_CALC_DATA, $baseCalcData);
            $ctx->set(self::CTX_OUT_DEP_PERIOD_DATA, $depPeriodData);
            $ctx->set(self::CTX_OUT_DEP_CALC_DATA, $depCalcData);
        } else {
            $ctx->set(self::CTX_OUT_ERROR_CODE, self::ERR_ADD_NEW_PERIOD);
        }
    }

    /**
     * Perform query to get the last calculation by type.
     *
     * @param string $calcCode
     * @return array|bool see \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder
     */
    private function queryLastPeriod($calcCode)
    {
        $query = $this->qbGetPeriod->build();
        /* modify query to get the last calculation by type code */
        $bindTypeCode = 'code';
        $whereType = $this->qbGetPeriod::AS_CALC_TYPE . '.' . ECalcType::ATTR_CODE . "=:$bindTypeCode";
        $query->where($whereType);
        /* sort desc by calcId and limit results if there are more than one calculations for the period */
        $query->order($this->qbGetPeriod::AS_CALC . '.' . ECalc::ATTR_ID . ' DESC');
        $query->limit(1);

        /* bind query parameters and get result set */
        $bind = [$bindTypeCode => $calcCode];
        $conn = $query->getConnection();
        $result = $conn->fetchRow($query, $bind);
        return $result;
    }
}