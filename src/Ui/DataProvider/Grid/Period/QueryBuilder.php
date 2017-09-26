<?php

namespace Praxigento\BonusBase\Ui\DataProvider\Grid\Period;

use Praxigento\BonusBase\Repo\Entity\Data\Period as EPeriod;

class QueryBuilder
    extends \Praxigento\Core\Ui\DataProvider\Grid\Query\Builder
{
    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_BASE_PERIOD = 'bbp';
    /**#@- */

    /**#@+
     * Aliases for data attributes.
     */
    const A_ID_PERIOD = 'id';
    const A_CALC_TYPE_ID = 'calcTypeId';
    const A_DATE_STAMP_BEGIN = 'dstampBegin';
    const A_DATE_STAMP_END = 'dstampEnd';
    /**#@- */

    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $map = [
                self::A_ID_PERIOD> self::AS_BON_BASE_PERIOD . '.' . EPeriod::ATTR_ID,
                self::A_CALC_TYPE_ID => self::AS_BON_BASE_PERIOD . '.' . EPeriod::ATTR_CALC_TYPE_ID,
                self::A_DATE_STAMP_BEGIN => self::AS_BON_BASE_PERIOD . '.' . EPeriod::ATTR_DSTAMP_BEGIN,
                self::A_DATE_STAMP_END => self::AS_BON_BASE_PERIOD . '.' . EPeriod::ATTR_DSTAMP_END
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
        $asTypAsset = self::AS_BON_BASE_PERIOD;

        /* SELECT FROM prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(EPeriod::ENTITY_NAME);
        $as = $asTypAsset;
        $cols = [
            self::A_ID_PERIOD => EPeriod::ATTR_ID,
            self::A_CALC_TYPE_ID => EPeriod::ATTR_CALC_TYPE_ID,
            self::A_DATE_STAMP_BEGIN => EPeriod::ATTR_DSTAMP_BEGIN,
            self::A_DATE_STAMP_END => EPeriod::ATTR_DSTAMP_END
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
        $value = 'COUNT(' . self::AS_BON_BASE_PERIOD . '.' . EPeriod::ATTR_ID . ')';

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
