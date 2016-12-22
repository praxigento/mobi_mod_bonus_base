<?php
namespace Praxigento\BonusBase\Data\Entity;

/**
 *  Compressed Downline Tree for compression calculation.
 * User: Alex Gusev <alex@flancer64.com>
 */
class Compress
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_ID = 'calc_id';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_ID = 'id';
    const ATTR_PARENT_ID = 'parent_id';
    const ENTITY_NAME = 'prxgt_bon_base_compress';

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
    public function getCustomerId()
    {
        $result = parent::get(self::ATTR_CUSTOMER_ID);
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
    public function getParentId()
    {
        $result = parent::get(self::ATTR_PARENT_ID);
        return $result;
    }

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
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
    public function setCustomerId($data)
    {
        parent::set(self::ATTR_CUSTOMER_ID, $data);
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
    public function setParentId($data)
    {
        parent::set(self::ATTR_PARENT_ID, $data);
    }
}