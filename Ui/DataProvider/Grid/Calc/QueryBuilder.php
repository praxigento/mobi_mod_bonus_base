<?php

namespace Praxigento\BonusBase\Ui\DataProvider\Grid\Calc;

use Praxigento\BonusBase\Repo\Data\Calculation as ECalculation;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ETypeCalc;

class QueryBuilder
    extends \Praxigento\Core\App\Ui\DataProvider\Grid\Query\Builder
{
    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_BASE_CALC = 'bbc';
    const AS_BON_BASE_PERIOD = 'bbp';
    const AS_BON_BASE_TYPE_CALC = 'bbtc';
    /**#@- */

    /**#@+
     * Aliases for data attributes.
     */
    const A_CALC_ID = 'calcId';
    const A_CALC_TYPE_CODE = 'calcTypeCode';
    const A_CALC_TYPE_ID = 'calcTypeId';
    const A_DATE_ENDED = 'dateEnded';
    const A_DATE_STARTED = 'dateStarted';
    const A_PERIOD = 'period';
    const A_PERIOD_ID = 'periodId';
    const A_STATE = 'state';
    /**#@- */

    /**
     * Construct expression for Period("firstName lastName").
     */
    public function getExpForPeriod()
    {
        $value = 'SUBSTR(`' . self::AS_BON_BASE_PERIOD . '`.`' . EPeriod::A_DSTAMP_BEGIN . '`, 1, 6)';
        $result = new \Praxigento\Core\App\Repo\Query\Expression($value);
        return $result;
    }

    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $map = [
                self::A_CALC_ID => self::AS_BON_BASE_CALC . '.' . ECalculation::A_ID,
                self::A_CALC_TYPE_CODE => self::AS_BON_BASE_TYPE_CALC . '.' . ETypeCalc::A_CODE,
                self::A_CALC_TYPE_ID => self::AS_BON_BASE_PERIOD . '.' . EPeriod::A_CALC_TYPE_ID,
                self::A_DATE_ENDED => self::AS_BON_BASE_CALC . '.' . ECalculation::A_DATE_ENDED,
                self::A_DATE_STARTED => self::AS_BON_BASE_CALC . '.' . ECalculation::A_DATE_STARTED,
                self::A_PERIOD => $this->getExpForPeriod(),
                self::A_PERIOD_ID => self::AS_BON_BASE_CALC . '.' . ECalculation::A_PERIOD_ID,
                self::A_STATE => self::AS_BON_BASE_CALC . '.' . ECalculation::A_STATE
            ];
            $this->mapper = new \Praxigento\Core\App\Repo\Query\Criteria\Def\Mapper($map);
        }
        $result = $this->mapper;
        return $result;
    }

    protected function getQueryItems()
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asCalc = self::AS_BON_BASE_CALC;

        /* SELECT FROM prxgt_bon_base_calc */
        $tbl = $this->resource->getTableName(ECalculation::ENTITY_NAME);
        $as = $asCalc;
        $cols = [
            self::A_CALC_ID => ECalculation::A_ID,
            self::A_DATE_ENDED => ECalculation::A_DATE_ENDED,
            self::A_DATE_STARTED => ECalculation::A_DATE_STARTED,
            self::A_PERIOD_ID => ECalculation::A_PERIOD_ID,
            self::A_STATE => ECalculation::A_STATE
        ];
        $result->from([$as => $tbl], $cols);

        /* LEFT JOIN Period */
        $asPeriod = self::AS_BON_BASE_PERIOD;
        $tbl = $this->resource->getTableName(EPeriod::ENTITY_NAME);
        $as = $asPeriod;
        $exp = $this->getExpForPeriod();
        $cols = [
            self::A_PERIOD => $exp,
            self::A_CALC_TYPE_ID => EPeriod::A_CALC_TYPE_ID
        ];
        $cond = $as . '.' . EPeriod::A_ID . '=' . $asCalc . '.' . ECalculation::A_PERIOD_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        /* LEFT JOIN Type Calc*/
        $asTypeCalc = self::AS_BON_BASE_TYPE_CALC;
        $tbl = $this->resource->getTableName(ETypeCalc::ENTITY_NAME);
        $as = $asTypeCalc;
        $cols = [
            self::A_CALC_TYPE_CODE => ETypeCalc::A_CODE
        ];
        $cond = $as . '.' . ETypeCalc::A_ID . '=' . $asPeriod . '.' . EPeriod::A_CALC_TYPE_ID;
        $result->joinLeft([$as => $tbl], $cond, $cols);

        return $result;
    }

    protected function getQueryTotal()
    {
        /* get query to select items */
        /** @var \Magento\Framework\DB\Select $result */
        $result = $this->getQueryItems();
        /* ... then replace "columns" part with own expression */
        $value = 'COUNT(' . self::AS_BON_BASE_CALC . '.' . ECalculation::A_ID . ')';

        /**
         * See method \Magento\Framework\DB\Select\ColumnsRenderer::render:
         */
        /**
         * if ($column instanceof \Zend_Db_Expr) {...}
         */
        $exp = new \Praxigento\Core\App\Repo\Query\Expression($value);
        /**
         *  list($correlationName, $column, $alias) = $columnEntry;
         */
        $entry = [null, $exp, null];
        $cols = [$entry];
        $result->setPart('columns', $cols);
        return $result;
    }
}
