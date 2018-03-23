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
        \Praxigento\Core\App\Repo\IGeneric $repoGeneric,
        \Praxigento\Core\Api\Helper\Date $hlpDate
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Repo\Data\Calculation::class
        );
        $this->hlpDate = $hlpDate;
    }

    /**
     * Mark calculation as complete.
     *
     * @param $calcId
     * @param string $dateEnded timestamp to be added to DB.
     */
    public function markComplete($calcId, $dateEnded = null)
    {
        if (is_null($dateEnded)) {
            $dateEnded = $this->hlpDate->getUtcNowForDb();
        }
        $bind = [
            ECalculation::A_DATE_ENDED => $dateEnded,
            ECalculation::A_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $result = $this->updateById($calcId, $bind);
        return $result;

    }
}