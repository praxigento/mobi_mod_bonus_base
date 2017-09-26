<?php

namespace Praxigento\BonusBase\Ui\DataProvider\Grid\Calc;

use Praxigento\BonusBase\Repo\Entity\Data\Calculation as ECalculation;

class QueryBuilder
    extends \Praxigento\Core\Ui\DataProvider\Grid\Query\Builder
{
    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_BASE_CALC = 'bbc';
    /**#@- */

    /**#@+
     * Aliases for data attributes.
     */
    const A_ID_CALC = 'id';
    const A_ID_PERIOD = 'periodId';
    const A_DATE_STARTED = 'dateStarted';
    const A_DATE_ENDED = 'dateEnded';
    const A_STATE = 'state';
    /**#@- */

    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $map = [
                self::A_ID_CALC> self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_ID,
                self::A_ID_PERIOD => self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_PERIOD_ID,
                self::A_DATE_STARTED => self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_DATE_STARTED,
                self::A_DATE_ENDED => self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_DATE_ENDED,
                self::A_STATE => self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_STATE
            ];
            $this->mapper = new \Praxigento\Core\Repo\Query\Criteria\Def\Mapper($map);
        }
        $result = $this->mapper;
        return $result;
    }

    protected function getQueryItems()
    {
        $result = $this->conn->select();
        /* define tables aliases for internal usage (in this method) */
        $asCalc = self::AS_BON_BASE_CALC;

        /* SELECT FROM prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ECalculation::ENTITY_NAME);
        $as = $asCalc;
        $cols = [
            self::A_ID_CALC => ECalculation::ATTR_ID,
            self::A_ID_PERIOD => ECalculation::ATTR_PERIOD_ID,
            self::A_DATE_STARTED => ECalculation::ATTR_DATE_STARTED,
            self::A_DATE_ENDED => ECalculation::ATTR_DATE_ENDED,
            self::A_STATE => ECalculation::ATTR_STATE
        ];
        $result->from([$as => $tbl], $cols);
        return $result;
    }

    protected function getQueryTotal()
    {
        /* get query to select items */
        /** @var \Magento\Framework\DB\Select $result */
        $result = $this->getQueryItems();
        /* ... then replace "columns" part with own expression */
        $value = 'COUNT(' . self::AS_BON_BASE_CALC . '.' . ECalculation::ATTR_ID . ')';

        /**
         * See method \Magento\Framework\DB\Select\ColumnsRenderer::render:
         */
        /**
         * if ($column instanceof \Zend_Db_Expr) {...}
         */
        $exp = new \Praxigento\Core\Repo\Query\Expression($value);
        /**
         *  list($correlationName, $column, $alias) = $columnEntry;
         */
        $entry = [null, $exp, null];
        $cols = [$entry];
        $result->setPart('columns', $cols);
        return $result;
    }
}
