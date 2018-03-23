<?php

namespace Praxigento\BonusBase\Repo\Data;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Period
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_CALC_TYPE_ID = 'calc_type_id';
    const A_DSTAMP_BEGIN = 'dstamp_begin';
    const A_DSTAMP_END = 'dstamp_end';
    const A_ID = 'id';
    const ENTITY_NAME = 'prxgt_bon_base_period';

    /**
     * @return int
     */
    public function getCalcTypeId()
    {
        $result = parent::get(self::A_CALC_TYPE_ID);
        return $result;
    }

    /**
     * @return string
     */
    public function getDstampBegin()
    {
        $result = parent::get(self::A_DSTAMP_BEGIN);
        return $result;
    }

    /**
     * @return string
     */
    public function getDstampEnd()
    {
        $result = parent::get(self::A_DSTAMP_END);
        return $result;
    }

    /**
     * @return int
     */
    public function getId()
    {
        $result = parent::get(self::A_ID);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_ID];
    }

    /**
     * @param int $data
     */
    public function setCalcTypeId($data)
    {
        parent::set(self::A_CALC_TYPE_ID, $data);
    }

    /**
     * @param string $data
     */
    public function setDstampBegin($data)
    {
        parent::set(self::A_DSTAMP_BEGIN, $data);
    }

    /**
     * @param string $data
     */
    public function setDstampEnd($data)
    {
        parent::set(self::A_DSTAMP_END, $data);
    }

    /**
     * @param int $data
     */
    public function setId($data)
    {
        parent::set(self::A_ID, $data);
    }
}