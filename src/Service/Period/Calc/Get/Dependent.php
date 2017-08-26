<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

use Praxigento\BonusBase\Data\Entity\Calculation as ECalc;
use Praxigento\BonusBase\Data\Entity\Type\Calc as ECalcType;
use Praxigento\BonusHybrid\Config as Cfg;

class Dependent
    implements IDependent
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
    protected $qbGetPeriod;

    public function __construct(
        \Praxigento\Core\Fw\Logger\App $logger,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder $qbGetPeriod
    )
    {
        $this->logger = $logger;
        $this->qbGetPeriod = $qbGetPeriod;
    }

    public function exec(\Praxigento\Core\Data $ctx)
    {
        /* get working data from context */
        $calcTypeCodeBase = $ctx->get(self::CTX_IN_BASE_TYPE_CODE);
        $calcTypeCodeDep = $ctx->get(self::CTX_IN_DEP_TYPE_CODE);
        $periodType = $ctx->get(self::CTX_IN_PERIOD_TYPE) ?? \Praxigento\Core\Tool\IPeriod::TYPE_MONTH;

        /**
         * perform processing
         */
        $ctx->set(self::CTX_OUT_SUCCESS, false);
        $this->logger->info("'Get dependent period calculation' processing is started "
            . "(base: $calcTypeCodeBase; dep: $calcTypeCodeDep).");
        /* get the last period data for given calculation type */
        $periodLastBase = $this->queryLastPeriod($calcTypeCodeBase);
        if ($periodLastBase) {
            $calcState = $periodLastBase[ECalc::ATTR_STATE];
            if ($calcState == Cfg::CALC_STATE_COMPLETE) {
                /* base calculation is complete, get the last dependent calc */
                $periodLastDep = $this->queryLastPeriod($calcTypeCodeDep);
                if (is_null($periodLastDep)) {
                    /* there is no dependent period */
                    $this->_logger->warning("There is no period data for calculation '$calcTypeCodeDep'. New period and related calculation will be created.");
                    $periodLastDep = $this->_subDb->addNewPeriodAndCalc($dependentCalcTypeId, $baseDsBegin,
                        $baseDsEnd);
                    $result->setDependentPeriodData($periodLastDep->get(Db::DATA_PERIOD));
                    $result->setDependentCalcData($periodLastDep->get(Db::DATA_CALC));
                    $result->markSucceed();
                } else {
                    /* there is dependent period */
                    $dependentDsBegin = $periodLastDep->getDstampBegin();
                    $dependentDsEnd = $periodLastDep->getDstampEnd();
                    if (
                        ($dependentDsBegin == $baseDsBegin) &&
                        ($dependentDsEnd == $baseDsEnd)
                    ) {
                        /* dependent period has the same begin/end as related base period */
                        $this->_logger->info("There is base '$calcTypeCodeBase' period for dependent '$calcTypeCodeDep' period ($dependentDsBegin-$dependentDsEnd).");
                        if (
                            $dependentCalcData &&
                            ($dependentCalcData->getState() == Cfg::CALC_STATE_COMPLETE)
                        ) {
                            /* complete dependent period for complete base period */
                            $this->_logger->warning("There is '$calcTypeCodeDep' period with complete calculation. No more '$calcTypeCodeDep' could be calculated.");
                        } else {
                            /* incomplete dependent period for complete base period */
                            $this->_logger->warning("There is '$calcTypeCodeDep' period without complete calculation. Continue calculation for this period.");
                            $result->setDependentPeriodData($periodLastDep);
                            $result->setDependentCalcData($dependentCalcData);
                            $result->markSucceed();
                        }
                    } else {
                        /* dependent period has different begin/end then related base period */
                        $this->_logger->warning("There is no period for '$calcTypeCodeDep' calculation based on '$calcTypeCodeBase' ($baseDsBegin-$baseDsEnd). New period and related calculation will be created.");
                        $periodLastDep = $this->_subDb->addNewPeriodAndCalc($dependentCalcTypeId, $baseDsBegin,
                            $baseDsEnd);
                        $result->setDependentPeriodData($periodLastDep->get(Db::DATA_PERIOD));
                        $result->setDependentCalcData($periodLastDep->get(Db::DATA_CALC));
                        $result->markSucceed();
                    }
                }
            } else {
                $this->logger->warning("Base calculation ($calcTypeCodeBase) is not complete yet. "
                    . "'$calcTypeCodeDep' could not be started.");
            }
        } else {
            $this->logger->warning("There is no period for '$calcTypeCodeBase' calculation  yet. "
                . "'$calcTypeCodeDep' could not be calculated.");
        }

        $this->logger->info("'Get basis period calculation' processing is completed "
            . "(base: $calcTypeCodeBase; dep: $calcTypeCodeDep).");
    }

    /**
     * Perform query to get the last calculation by type.
     *
     * @param string $calcCode
     * @return array see \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder
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