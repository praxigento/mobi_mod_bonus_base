<?php

namespace Praxigento\BonusBase\Repo\Data\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Sales
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_SALE_ORDER_ID = 'sale_order_id';
    const A_TRANS_ID = 'trans_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_sales';

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_TRANS_ID, self::A_SALE_ORDER_ID];
    }

    /**
     * @return int
     */
    public function getSaleOrderId()
    {
        $result = parent::get(self::A_SALE_ORDER_ID);
        return $result;
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
    public function setSaleOrderId($data)
    {
        parent::set(self::A_SALE_ORDER_ID, $data);
    }

    /**
     * @param int $data
     */
    public function setTransId($data)
    {
        parent::set(self::A_TRANS_ID, $data);
    }
}