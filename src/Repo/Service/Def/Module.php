<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service\Def;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;

class Module
    implements \Praxigento\BonusBase\Repo\Service\IModule
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var \Praxigento\BonusBase\Repo\Entity\ICalculation */
    protected $_repoCalc;

    public function __construct(
        \Praxigento\BonusBase\Repo\Entity\ICalculation $repoCalc,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        $this->_repoCalc = $repoCalc;
        $this->_toolDate = $toolDate;
    }

    public function markCalcComplete($calcId)
    {
        $tsEnded = $this->_toolDate->getUtcNowForDb();
        $bind = [
            ECalculation::ATTR_DATE_ENDED => $tsEnded,
            ECalculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $where = ECalculation::ATTR_ID . '=' . $calcId;
        $result = $this->_repoCalc->update($bind, $where);
        return $result;
    }

}