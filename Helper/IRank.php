<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Helper;

interface IRank
{
    /**
     * Cached variant of the repo-operation (\Praxigento\BonusBase\Repo\Dao\Rank::getIdByCode).
     *
     * @param string $rankCode
     * @return int|null
     *
     * @deprecated use \Praxigento\BonusBase\Repo\Dao\Rank (w/o cache but nonetheless)
     */
    public function getIdByCode($rankCode);
}