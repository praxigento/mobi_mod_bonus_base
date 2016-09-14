<?php
namespace Praxigento\BonusBase\Data\Entity;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Level
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_LEVEL = 'level';
    const ATTR_PERCENT = 'percent';
    const ENTITY_NAME = 'prxgt_bon_base_level';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_TYPE_ID, self::ATTR_LEVEL];
    }
}