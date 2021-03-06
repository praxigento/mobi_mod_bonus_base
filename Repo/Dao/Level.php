<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao;

use Praxigento\BonusBase\Repo\Data\Level as ELevel;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ETypeCalc;

class Level
    extends \Praxigento\Core\App\Repo\Dao
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Api\App\Repo\Generic $daoGeneric
    ) {
        parent::__construct(
            $resource,
            $daoGeneric,
            ELevel::class
        );
    }

    /**
     * Get array ([level=>percent]) of the levels for given calculation type.
     * This structure is used in bonus calculations.
     *
     * @param $calcTypeCode
     * @return array [level=>percent] ordered asc by level.
     */
    public function getByCalcTypeCode($calcTypeCode)
    {
        $result = [];
        /**
         * Join levels to calculation type:
         *
         * SELECT
         * pbbl.level,
         * pbbl.percent
         * FROM prxgt_bon_base_level pbbl
         * LEFT JOIN prxgt_bon_base_type_calc pbbtc
         * ON pbbl.calc_type_id = pbbtc.id
         * WHERE pbbtc.code = 'HYBRID_BON_TEAM_DEF'
         * ORDER BY pbbl.level ASC;
         *
         */
        /* tables aliases and names */
        $asLvl = 'lvl';
        $asType = 'tp';
        $tblLvl = $this->resource->getTableName(ELevel::ENTITY_NAME);
        $tblType = $this->resource->getTableName(ETypeCalc::ENTITY_NAME);
        /* compose query */
        $query = $this->conn->select();
        /* FROM prxgt_bon_base_level */
        $as = $asLvl;
        $tbl = $tblLvl;
        $cols = [
            ELevel::A_LEVEL,
            ELevel::A_PERCENT
        ];
        $query->from([$as => $tbl], $cols);
        /* JOIN prxgt_bon_base_type_calc */
        $as = $asType;
        $tbl = $tblType;
        $cols = [];
        $cond = $as . '.' . ETypeCalc::A_ID . '=' . $asLvl . '.' . ELevel::A_CALC_TYPE_ID;
        $query->joinLeft([$as => $tbl], $cond, $cols);
        /* query tuning */
        $bndCode = 'code';
        $query->where($asType . '.' . ETypeCalc::A_CODE . '=:' . $bndCode);
        $query->order($asLvl . '.' . ELevel::A_LEVEL . ' ASC');
        /* perform query, parse result set */
        $bind = [$bndCode => $calcTypeCode];
        $rs = $this->conn->fetchAll($query, $bind);
        foreach ($rs as $one) {
            $level = $one[ELevel::A_LEVEL];
            $percent = $one[ELevel::A_PERCENT];
            $result[$level] = $percent;
        }
        return $result;
    }

    /**
     * Get array ([level=>percent]) of the levels for given calculation type.
     * This structure is used in bonus calculations.
     *
     * @param int $calcTypeId
     * @return array [level=>percent] ordered asc by level.
     */
    public function getByCalcTypeId($calcTypeId)
    {
        $result = [];
        $where = ELevel::A_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $order = ELevel::A_LEVEL . ' ASC';
        $rs = $this->get($where, $order);
        /** @var \Praxigento\BonusBase\Repo\Data\Level $one */
        foreach ($rs as $one) {
            $level = $one->getLevel();
            $percent = $one->getPercent();
            $result[$level] = $percent;
        }
        return $result;
    }
}