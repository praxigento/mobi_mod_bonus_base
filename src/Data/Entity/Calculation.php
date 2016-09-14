<?php
namespace Praxigento\BonusBase\Data\Entity;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Calculation
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_DATE_ENDED = 'date_ended';
    const ATTR_DATE_STARTED = 'date_started';
    const ATTR_ID = 'id';
    const ATTR_PERIOD_ID = 'period_id';
    const ATTR_STATE = 'state';
    const ENTITY_NAME = 'prxgt_bon_base_calc';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_ID];
    }
}