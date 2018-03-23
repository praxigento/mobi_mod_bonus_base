<?php

namespace Praxigento\BonusBase\Repo\Data\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Rank
    extends \Praxigento\Core\App\Repo\Data\Entity\Base
{
    const A_RANK_REF = 'rank_ref';
    const A_TRANS_REF = 'trans_ref';
    const ENTITY_NAME = 'prxgt_bon_base_log_rank';

    public static function getPrimaryKeyAttrs()
    {
        return [self::A_TRANS_REF, self::A_RANK_REF];
    }

    /**
     * @return int
     */
    public function getRankRef()
    {
        $result = parent::get(self::A_RANK_REF);
        return $result;
    }

    /**
     * @return int
     */
    public function getTransRef()
    {
        $result = parent::get(self::A_TRANS_REF);
        return $result;
    }

    /**
     * @param int $data
     */
    public function setRankRef($data)
    {
        parent::set(self::A_RANK_REF, $data);
    }

    /**
     * @param int $data
     */
    public function setTransId($data)
    {
        parent::set(self::A_TRANS_REF, $data);
    }
}