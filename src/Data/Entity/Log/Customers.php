<?php
namespace Praxigento\BonusBase\Data\Entity\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Customers
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_TRANS_ID = 'trans_id';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_cust';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_TRANS_ID];
    }
}