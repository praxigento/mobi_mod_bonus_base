<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Data\Entity\Cfg;

/**
 * Configuration for ranks by generations in downline tree.
 */
class Generation
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_GENERATION = 'generation';
    const ATTR_PERCENT = 'percent';
    const ATTR_RANK_ID = 'rank_id';
    const ENTITY_NAME = 'prxgt_bon_base_cfg_gen';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_TYPE_ID, self::ATTR_RANK_ID, self::ATTR_GENERATION];
    }
}