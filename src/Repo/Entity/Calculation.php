<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;

class Calculation
    extends \Praxigento\Core\Repo\Def\Entity
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $toolDate;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Data\Entity\Calculation::class
        );
        $this->toolDate = $toolDate;
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
            $dateEnded = $this->toolDate->getUtcNowForDb();
        }
        $bind = [
            ECalculation::ATTR_DATE_ENDED => $dateEnded,
            ECalculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $result = $this->updateById($calcId, $bind);
        return $result;

    }
}