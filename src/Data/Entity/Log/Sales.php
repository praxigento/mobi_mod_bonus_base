<?php
namespace Praxigento\BonusBase\Data\Entity\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Sales
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_TRANS_ID = 'trans_id';
    const ATTR_SALES_ORDER_ID = 'sale_order_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_sales';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_TRANS_ID, self::ATTR_SALES_ORDER_ID];
    }
}