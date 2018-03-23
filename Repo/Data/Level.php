<?php

namespace Praxigento\BonusBase\Repo\Data;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Level
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_CALC_TYPE_ID = 'calc_type_id';
    const A_LEVEL = 'level';
    const A_PERCENT = 'percent';
    const ENTITY_NAME = 'prxgt_bon_base_level';

    /**
     * @return int
     */
    public function getCalcTypeId()
    {
        $result = parent::get(self::A_CALC_TYPE_ID);
        return $result;
    }

    /**
     * @return double
     */
    public function getLevel()
    {
        $result = parent::get(self::A_LEVEL);
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
        return [self::A_CALC_TYPE_ID, self::A_LEVEL];
    }

    /**
     * @param int $data
     */
    public function setCalcTypeId($data)
    {
        parent::set(self::A_CALC_TYPE_ID, $data);
    }

    /**
     * @param double $data
     */
    public function setLevel($data)
    {
        parent::set(self::A_LEVEL, $data);
    }

    /**
     * @param double $data
     */
    public function setPercent($data)
    {
        parent::set(self::A_PERCENT, $data);
    }

}