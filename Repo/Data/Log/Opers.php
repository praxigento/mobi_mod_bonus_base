<?php

namespace Praxigento\BonusBase\Repo\Data\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Opers
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const ATTR_CALC_ID = 'calc_id';
    const ATTR_OPER_ID = 'oper_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_opers';

    /**
     * @return int
     */
    public function getCalcId()
    {
        $result = parent::get(self::ATTR_CALC_ID);
        return $result;
    }

    /**
     * @return int
     */
    public function getOperId()
    {
        $result = parent::get(self::ATTR_OPER_ID);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_ID, self::ATTR_OPER_ID];
    }

    /**
     * @param int $data
     */
    public function setCalcId($data)
    {
        parent::set(self::ATTR_CALC_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setOperId($data)
    {
        parent::set(self::ATTR_OPER_ID, $data);
    }
}