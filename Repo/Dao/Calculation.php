<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalculation;

class Calculation
    extends \Praxigento\Core\App\Repo\Def\Entity
{
    /** @var \Praxigento\Core\Api\Helper\Date */
    protected $hlpDate;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $daoGeneric,
        \Praxigento\Core\Api\Helper\Date $hlpDate
    ) {
        parent::__construct(
            $resource,
            $daoGeneric,
            \Praxigento\BonusBase\Repo\Data\Calculation::class
        );
        $this->hlpDate = $hlpDate;
    }

    /**
     * Mark calculation as complete.
     *
     * @param int $calcId
     * @return int|void
     */
    public function markComplete($calcId)
    {
        $dateEnded = $this->hlpDate->getUtcNowForDb();
        $bind = [
            ECalculation::A_DATE_ENDED => $dateEnded,
            ECalculation::A_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $result = $this->updateById($calcId, $bind);
        return $result;

    }
}