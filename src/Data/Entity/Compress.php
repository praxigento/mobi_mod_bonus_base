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

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
    }
}