<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Data\Cfg;

/**
 * Configuration for ranks by generations in downline tree.
 */
class Generation
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_CALC_TYPE_ID = 'calc_type_id';
    const A_GENERATION = 'generation';
    const A_PERCENT = 'percent';
    const A_RANK_ID = 'rank_id';
    const ENTITY_NAME = 'prxgt_bon_base_cfg_gen';

    /**
     * @return int
     */
    public function getCalcTypeId()
    {
        $result = parent::get(self::A_CALC_TYPE_ID);
        return $result;
    }

    /**
     * @return int
     */
    public function getGeneration()
    {
        $result = parent::get(self::A_GENERATION);
        return $result;
    }

    /**
     * @return double
     */
    public function getPercent()
    {
        $result = parent::get(self::A_PERCENT);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_CALC_TYPE_ID, self::A_RANK_ID, self::A_GENERATION];
    }

    /**
     * @return int
     */
    public function getRankId()
    {
        $result = parent::get(self::A_RANK_ID);
        return $result;
    }

    /**
     * @param int $data
     */
    public function setCalcTypeId($data)
    {
        parent::set(self::A_CALC_TYPE_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setGeneration($data)
    {
        parent::set(self::A_GENERATION, $data);
    }

    /**
     * @param double $data
     */
    public function setPercent($data)
    {
        parent::set(self::A_PERCENT, $data);
    }

    /**
     * @param int $data
     */
    public function setRankId($data)
    {
        parent::set(self::A_RANK_ID, $data);
    }
}