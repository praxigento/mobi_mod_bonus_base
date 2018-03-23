<?php

namespace Praxigento\BonusBase\Repo\Data\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Customers
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_CUSTOMER_ID = 'customer_id';
    const A_TRANS_ID = 'trans_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_cust';

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $result = parent::get(self::A_CUSTOMER_ID);
        return $result;
    }

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_TRANS_ID];
    }

    /**
     * @return int
     */
    public function getTransId()
    {
        $result = parent::get(self::A_TRANS_ID);
        return $result;
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
    public function setTransId($data)
    {
        parent::set(self::A_TRANS_ID, $data);
    }
}