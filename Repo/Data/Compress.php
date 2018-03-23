<?php

namespace Praxigento\BonusBase\Repo\Data;

/**
 *  Compressed Downline Tree for compression calculation.
 * User: Alex Gusev <alex@flancer64.com>
 */
class Compress
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_CALC_ID = 'calc_id';
    const A_CUSTOMER_ID = 'customer_id';
    const A_ID = 'id';
    const A_PARENT_ID = 'parent_id';
    const ENTITY_NAME = 'prxgt_bon_base_compress';

    /**
     * @return int
     */
    public function getCalcId()
    {
        $result = parent::get(self::A_CALC_ID);
        return $result;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $result = parent::get(self::A_CUSTOMER_ID);
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

    /**
     * @return int
     */
    public function getParentId()
    {
        $result = parent::get(self::A_PARENT_ID);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_ID];
    }

    /**
     * @param int $data
     */
    public function setCalcId($data)
    {
        parent::set(self::A_CALC_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setCustomerId($data)
    {
        parent::set(self::A_CUSTOMER_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setId($data)
    {
        parent::set(self::A_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setParentId($data)
    {
        parent::set(self::A_PARENT_ID, $data);
    }
}