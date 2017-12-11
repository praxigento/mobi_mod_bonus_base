<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Data\Cfg;

/**
 * Configuration for ranks by generations in downline tree.
 */
class Generation
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_GENERATION = 'generation';
    const ATTR_PERCENT = 'percent';
    const ATTR_RANK_ID = 'rank_id';
    const ENTITY_NAME = 'prxgt_bon_base_cfg_gen';

    /**
     * @return int
     */
    public function getCalcTypeId()
    {
        $result = parent::get(self::ATTR_CALC_TYPE_ID);
        return $result;
    }

    /**
     * @return int
     */
    public function getGeneration()
    {
        $result = parent::get(self::ATTR_GENERATION);
        return $result;
    }

    /**
     * @return double
     */
    public function getPercent()
    {
        $result = parent::get(self::ATTR_PERCENT);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_TYPE_ID, self::ATTR_RANK_ID, self::ATTR_GENERATION];
    }

    /**
     * @return int
     */
    public function getRankId()
    {
        $result = parent::get(self::ATTR_RANK_ID);
        return $result;
    }

    /**
     * @param int $data
     */
    public function setCalcTypeId($data)
    {
        parent::set(self::ATTR_CALC_TYPE_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setGeneration($data)
    {
        parent::set(self::ATTR_GENERATION, $data);
    }

    /**
     * @param double $data
     */
    public function setPercent($data)
    {
        parent::set(self::ATTR_PERCENT, $data);
    }

    /**
     * @param int $data
     */
    public function setRankId($data)
    {
        parent::set(self::ATTR_RANK_ID, $data);
    }
}