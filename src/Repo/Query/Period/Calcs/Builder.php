<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Query\Period\Calcs;

use Praxigento\BonusBase\Data\Entity\Calculation as Calc;
use Praxigento\BonusBase\Data\Entity\Period as Period;
use Praxigento\BonusBase\Data\Entity\Type\Calc as CalcType;

/**
 * Build query to get periods with related calculations data.
 *
 * SELECT
 * `prd`.`id` AS `period_id`,
 * `prd`.`calc_type_id`,
 * `prd`.`dstamp_begin`,
 * `prd`.`dstamp_end`,
 * `calc`.`date_started`,
 * `calc`.`date_ended`,
 * `calc`.`state` AS `calc_state`,
 * `cType`.`code`,
 * `cType`.`note`
 * FROM `prxgt_bon_base_period` AS `prd`
 * LEFT JOIN `prxgt_bon_base_calc` AS `calc`
 * ON calc.period_id = prd.id
 * LEFT JOIN `prxgt_bon_base_type_calc` AS `cType`
 * ON cType.id = cType.calc_type_id
 */
class Builder
    extends \Praxigento\Core\Repo\Query\Def\Builder
{
    /** Tables aliases */
    const AS_CALC = 'calc';
    const AS_CALC_TYPE = 'cType';
    const AS_PERIOD = 'prd';

    /** Columns aliases */
    const A_CALC_ID = 'calc_id';
    const A_CALC_STATE = 'calc_state';
    const A_CALC_TYPE_CODE = CalcType::ATTR_CODE;
    const A_CALC_TYPE_ID = Period::ATTR_CALC_TYPE_ID;
    const A_CALC_TYPE_NOTE = CalcType::ATTR_NOTE;
    const A_DATE_ENDED = Calc::ATTR_DATE_ENDED;
    const A_DATE_STARTED = Calc::ATTR_DATE_STARTED;
    const A_DS_BEGIN = Period::ATTR_DSTAMP_BEGIN;
    const A_DS_END = Period::ATTR_DSTAMP_END;
    const A_PERIOD_ID = 'period_id';

    public function build(\Magento\Framework\DB\Select $source = null)
    {
        $result = $this->conn->select(); // this is root builder (not extender)
        /* define tables aliases */
        $asPeriod = self::AS_PERIOD;
        $asCalc = self::AS_CALC;
        $asType = self::AS_CALC_TYPE;

        /* select from prxgt_bon_base_period */
        $tbl = $this->resource->getTableName(Period::ENTITY_NAME);
        $cols = [
            self::A_PERIOD_ID => Period::ATTR_ID,
            self::A_CALC_TYPE_ID => Period::ATTR_CALC_TYPE_ID,
            self::A_DS_BEGIN => Period::ATTR_DSTAMP_BEGIN,
            self::A_DS_END => Period::ATTR_DSTAMP_END
        ];
        $result->from([$asPeriod => $tbl], $cols);

        /* LEFT JOIN prxgt_bon_base_calc */
        $tbl = $this->resource->getTableName(Calc::ENTITY_NAME);
        $on = $asCalc . '.' . Calc::ATTR_PERIOD_ID . '=' . $asPeriod . '.' . Period::ATTR_ID;
        $cols = [
            self::A_DATE_STARTED => Calc::ATTR_DATE_STARTED,
            self::A_DATE_ENDED => Calc::ATTR_DATE_ENDED,
            self::A_CALC_ID => Calc::ATTR_ID,
            self::A_CALC_STATE => Calc::ATTR_STATE
        ];
        $result->joinLeft([$asCalc => $tbl], $on, $cols);

        /* LEFT JOIN prxgt_bon_base_type_calc */
        $tbl = $this->resource->getTableName(CalcType::ENTITY_NAME);
        $on = $asType . '.' . CalcType::ATTR_ID . '=' . $asPeriod . '.' . Period::ATTR_CALC_TYPE_ID;
        $cols = [
            self::A_CALC_TYPE_CODE => CalcType::ATTR_CODE,
            self::A_CALC_TYPE_NOTE => CalcType::ATTR_NOTE
        ];
        $result->joinLeft([$asType => $tbl], $on, $cols);

        return $result;
    }

    public function getSelectQuery(\Praxigento\Core\Repo\Query\IBuilder $qbuild = null)
    {
        $result = $this->build();
        return $result;
    }

}