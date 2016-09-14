<?php
namespace Praxigento\BonusBase\Data\Entity\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Opers
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_CALC_ID = 'calc_id';
    const ATTR_OPER_ID = 'oper_id';
    const ENTITY_NAME = 'prxgt_bon_base_log_opers';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_CALC_ID, self::ATTR_OPER_ID];
    }
}