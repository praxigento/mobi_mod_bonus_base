<?php

namespace Praxigento\BonusBase\Ui\DataProvider\Grid\Rank;

use Praxigento\BonusBase\Repo\Entity\Data\Rank as ERank;

class QueryBuilder
    extends \Praxigento\Core\App\Ui\DataProvider\Grid\Query\Builder
{


    /**#@+ Tables aliases for external usage ('camelCase' naming) */
    const AS_BON_BASE_RANK = 'bbr';
    /**#@- */
    const A_CODE = 'code';
    /**#@+
     * Aliases for data attributes.
     */
    const A_ID_RANK = 'id';
    const A_NOTE = 'note';
    /**#@- */

    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $map = [
                self::A_ID_RANK> self::AS_BON_BASE_RANK . '.' . ERank::ATTR_ID,
                self::A_CODE => self::AS_BON_BASE_RANK . '.' . ERank::ATTR_CODE,
                self::A_NOTE => self::AS_BON_BASE_RANK . '.' . ERank::ATTR_NOTE
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
        $asTypAsset = self::AS_BON_BASE_RANK;

        /* SELECT FROM prxgt_acc_type_asset */
        $tbl = $this->resource->getTableName(ERank::ENTITY_NAME);
        $as = $asTypAsset;
        $cols = [
            self::A_ID_RANK => ERank::ATTR_ID,
            self::A_CODE => ERank::ATTR_CODE,
            self::A_NOTE => ERank::ATTR_NOTE
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
        $value = 'COUNT(' . self::AS_BON_BASE_RANK . '.' . ERank::ATTR_ID . ')';

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