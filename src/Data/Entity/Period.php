<?php
namespace Praxigento\BonusBase\Data\Entity;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Period
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_DSTAMP_BEGIN = 'dstamp_begin';
    const ATTR_DSTAMP_END = 'dstamp_end';
    const ATTR_ID = 'id';
    const ENTITY_NAME = 'prxgt_bon_base_period';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
    }
}