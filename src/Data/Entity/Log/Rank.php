<?php
namespace Praxigento\BonusBase\Data\Entity\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Rank
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_TRANS_REF = 'trans_ref';
    const ATTR_RANK_REF = 'rank_ref';
    const ENTITY_NAME = 'prxgt_bon_base_log_rank';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_TRANS_REF, self::ATTR_RANK_REF];
    }
}