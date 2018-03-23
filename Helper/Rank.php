<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Helper;

class Rank
    implements IRank
{

    /** @var array cached IDs accessed by code */
    protected $cachedIds = [];
    /** @var \Praxigento\BonusBase\Repo\Dao\Rank */
    protected $daoRank;

    public function __construct(
        \Praxigento\BonusBase\Repo\Dao\Rank $daoRank
    ) {
        $this->daoRank = $daoRank;
    }

    public function getIdByCode($rankCode)
    {
        if (!isset($this->cachedIds[$rankCode])) {
            $this->cachedIds[$rankCode] = $this->daoRank->getIdByCode($rankCode);
        }
        return $this->cachedIds[$rankCode];
    }
}