<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Helper;

interface IRank
{
    /**
     * Cached variant of the repo-operation (\Praxigento\BonusBase\Repo\Entity\IRank::getIdByCode).
     *
     * @param string $rankCode
     * @return int|null
     */
    public function getIdByCode($rankCode);
}