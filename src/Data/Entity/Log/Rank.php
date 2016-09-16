<?php
namespace Praxigento\BonusBase\Data\Entity\Log;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Rank
    extends \Praxigento\Core\Data\Entity\Base
{
    const ATTR_RANK_REF = 'rank_ref';
    const ATTR_TRANS_REF = 'trans_ref';
    const ENTITY_NAME = 'prxgt_bon_base_log_rank';

    public function getPrimaryKeyAttrs()
    {
        return [self::ATTR_TRANS_REF, self::ATTR_RANK_REF];
    }

    /**
     * @return int
     */
    public function getRankRef()
    {
        $result = parent::getData(self::ATTR_RANK_REF);
        return $result;
    }

    /**
     * @return int
     */
    public function getTransRef()
    {
        $result = parent::getData(self::ATTR_TRANS_REF);
        return $result;
    }

    /**
     * @param int $data
     */
    public function setRankRef($data)
    {
        parent::setData(self::ATTR_RANK_REF, $data);
    }

    /**
     * @param int $data
     */
    public function setTransId($data)
    {
        parent::setData(self::ATTR_TRANS_REF, $data);
    }
}