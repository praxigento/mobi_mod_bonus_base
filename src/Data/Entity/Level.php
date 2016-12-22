<?php
namespace Praxigento\BonusBase\Data\Entity;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Level
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_LEVEL = 'level';
    const ATTR_PERCENT = 'percent';
    const ENTITY_NAME = 'prxgt_bon_base_level';

    /**
     * @return int
     */
    public function getCalcTypeId()
    {
        $result = parent::get(self::ATTR_CALC_TYPE_ID);
        return $result;
    }

    /**
     * @return double
     */
    public function getLevel()
    {
        $result = parent::get(self::ATTR_LEVEL);
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

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_TYPE_ID, self::ATTR_LEVEL];
    }

    /**
     * @param int $data
     */
    public function setCalcTypeId($data)
    {
        parent::set(self::ATTR_CALC_TYPE_ID, $data);
    }

    /**
     * @param double $data
     */
    public function setLevel($data)
    {
        parent::set(self::ATTR_LEVEL, $data);
    }

    /**
     * @param double $data
     */
    public function setPercent($data)
    {
        parent::set(self::ATTR_PERCENT, $data);
    }

}