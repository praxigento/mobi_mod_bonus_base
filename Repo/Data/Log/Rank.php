<?php

namespace Praxigento\BonusBase\Repo\Data\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Rank
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const ATTR_RANK_REF = 'rank_ref';
    const ATTR_TRANS_REF = 'trans_ref';
    const ENTITY_NAME = 'prxgt_bon_base_log_rank';

    public static function getPrimaryKeyAttrs()
    {
        return [self::ATTR_TRANS_REF, self::ATTR_RANK_REF];
    }

    /**
     * @return int
     */
    public function getRankRef()
    {
        $result = parent::get(self::ATTR_RANK_REF);
        return $result;
    }

    /**
     * @return int
     */
    public function getTransRef()
    {
        $result = parent::get(self::ATTR_TRANS_REF);
        return $result;
    }

    /**
     * @param int $data
     */
    public function setRankRef($data)
    {
        parent::set(self::ATTR_RANK_REF, $data);
    }

    /**
     * @param int $data
     */
    public function setTransId($data)
    {
        parent::set(self::ATTR_TRANS_REF, $data);
    }
}