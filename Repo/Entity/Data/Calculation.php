<?php

namespace Praxigento\BonusBase\Repo\Entity\Data;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Calculation
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const ATTR_DATE_ENDED = 'date_ended';
    const ATTR_DATE_STARTED = 'date_started';
    const ATTR_ID = 'id';
    const ATTR_PERIOD_ID = 'period_id';
    const ATTR_STATE = 'state'; // see \Praxigento\BonusBase\Config::CALC_STATE_...
    const ENTITY_NAME = 'prxgt_bon_base_calc';

    /**
     * @return string
     */
    public function getDateEnded()
    {
        $result = parent::get(self::ATTR_DATE_ENDED);
        return $result;
    }

    /**
     * @return string
     */
    public function getDateStarted()
    {
        $result = parent::get(self::ATTR_DATE_STARTED);
        return $result;
    }

    /**
     * @return int
     */
    public function getId()
    {
        $result = parent::get(self::ATTR_ID);
        return $result;
    }

    /**
     * @return int
     */
    public function getPeriodId()
    {
        $result = parent::get(self::ATTR_PERIOD_ID);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
    }

    /**
     * @return string
     */
    public function getState()
    {
        $result = parent::get(self::ATTR_STATE);
        return $result;
    }

    /**
     * @param string $data
     */
    public function setDateEnded($data)
    {
        parent::set(self::ATTR_DATE_ENDED, $data);
    }

    /**
     * @param string $data
     */
    public function setDateStarted($data)
    {
        parent::set(self::ATTR_DATE_STARTED, $data);
    }

    /**
     * @param int $data
     */
    public function setId($data)
    {
        parent::set(self::ATTR_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setPeriodId($data)
    {
        parent::set(self::ATTR_PERIOD_ID, $data);
    }

    /**
     * @param string $data
     */
    public function setState($data)
    {
        parent::set(self::ATTR_STATE, $data);
    }
}