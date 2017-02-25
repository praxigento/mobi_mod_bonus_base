<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Helper;

class Rank
    implements IRank
{

    /** @var \Praxigento\BonusBase\Repo\Entity\IRank */
    protected $repoRank;
    /** @var array cached IDs accessed by code */
    protected $cachedIds = [];

    public function __construct(
        \Praxigento\BonusBase\Repo\Entity\IRank $repoRank
    ) {
        $this->repoRank = $repoRank;
    }

    public function getIdByCode($rankCode)
    {
        if (!isset($this->cachedIds[$rankCode])) {
            $this->cachedIds[$rankCode] = $this->repoRank->getIdByCode($rankCode);
        }
        return $this->cachedIds[$rankCode];
    }
}