<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Query\Period\Calcs\Get;

use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ECalcType;

/**
 * Build query to get periods with related calculations data by DATE_BEGIN, DATE_END & CALC_STATE.
 *
 * TODO: leave one query only - this or ../Builder
 */
class Builder
    extends \Praxigento\Core\App\Repo\Query\Builder
{
    /** Tables aliases for external usage ('camelCase' naming) */
    const AS_CALC = 'calc';
    const AS_CALC_TYPE = 'calcType';
    const AS_PERIOD = 'period';

    /** Columns/expressions aliases for external usage ('camelCase' naming) */
    const A_CALC_DATE_END = 'calcDateEnd';
    const A_CALC_DATE_START = 'calcDateStart';
    const A_CALC_ID = 'calcId';
    const A_CALC_STATE = 'calcState';
    const A_CALC_TYPE_CODE = 'calcTypeCode';
    const A_CALC_TYPE_ID = 'calcTypeId';
    const A_PERIOD_BEGIN = 'periodBegin';
    const A_PERIOD_END = 'periodEnd';
    const A_PERIOD_ID = 'periodId';

    /** Bound variables names ('camelCase' naming) */
    const BND_DATE_BEGIN = 'dateBegin';
    const BND_DATE_END = 'dateEnd';
    const BND_STATE = 'state';

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        /* this is root query builder (started from SELECT) */
        $result = $this->conn->select();

        /* define tables aliases for internal usage (in this method) */
        $asCalc = self::AS_CALC;
        $asType = self::AS_CALC_TYPE;
        $asPeriod = self::AS_PERIOD;

        /* FROM prxgt_bon_base_period */
        $tbl = $this->resource->getTableName(EPeriod::ENTITY_NAME);
        $as = $asPeriod;
        $cols = [
            self::A_PERIOD_ID => EPeriod::A_ID,
            self::A_CALC_TYPE_ID => EPeriod::A_CALC_TYPE_ID,
            self::A_PERIOD_BEGIN => EPeriod::A_DSTAMP_BEGIN,
            self::A_PERIOD_END => EPeriod::A_DSTAMP_END
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN prxgt_bon_base_calc to get calculation data */
        $tbl = $this->resource->getTableName(ECalc::ENTITY_NAME);
        $as = $asCalc;
        $cols = [
            self::A_CALC_ID => ECalc::A_ID,
            self::A_CALC_DATE_START => ECalc::A_DATE_STARTED,
            self::A_CALC_DATE_END => ECalc::A_DATE_ENDED,
            self::A_CALC_STATE => ECalc::A_STATE
        ];
        $cond = $as . '.' . ECalc::A_PERIOD_ID . '=' . $asPeriod . '.' . EPeriod::A_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN prxgt_bon_base_type_calc to get calculation type data */
        $tbl = $this->resource->getTableName(ECalcType::ENTITY_NAME);
        $as = $asType;
        $cols = [
            self::A_CALC_TYPE_ID => ECalcType::A_ID,
            self::A_CALC_TYPE_CODE => ECalcType::A_CODE
        ];
        $cond = $as . '.' . ECalcType::A_ID . '=' . $asPeriod . '.' . EPeriod::A_CALC_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* query tuning */
        $byBegin = "$asPeriod." . EPeriod::A_DSTAMP_BEGIN . "=:" . self::BND_DATE_BEGIN;
        $byEnd = "$asPeriod." . EPeriod::A_DSTAMP_END . "=:" . self::BND_DATE_END;
        $byState = "$asCalc." . ECalc::A_STATE . "=:" . self::BND_STATE;
        $result->where("($byBegin) AND ($byEnd) AND ($byState)");

        return $result;
    }

}