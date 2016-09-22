<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service\Def;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

class Module
    implements \Praxigento\BonusBase\Repo\Service\IModule
{
    /** @var \Praxigento\BonusBase\Repo\Entity\ICalculation */
    protected $_repoCalc;
    protected $_repoPeriod;
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;

    public function __construct(
        \Praxigento\BonusBase\Repo\Entity\ICalculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\IPeriod $repoPeriod,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        $this->_repoCalc = $repoCalc;
        $this->_repoPeriod = $repoPeriod;
        $this->_toolDate = $toolDate;
    }

    public function getLastCalcForPeriod($periodId)
    {
        $result = null;
        $where = ECalculation::ATTR_PERIOD_ID . '=' . (int)$periodId;
        $limit = 1;
        $order = [ECalculation::ATTR_ID . ' ASC'];
        $rs = $this->_repoCalc->get($where, $order, $limit);
        if (is_array($rs) && count($rs)) {
            $data = reset($rs);
            $result = new ECalculation($data);
        }
        return $result;
    }

    public function getLastPeriodByCalcType($calcTypeId)
    {
        $result = null;
        /* set WHERE and ORDER BY clauses */
        $where = EPeriod::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $order = [EPeriod::ATTR_DSTAMP_BEGIN . ' DESC'];
        /* get one only period with the biggest begin date stamp */
        $rs = $this->_repoPeriod->get($where, $order, 1);
        if (is_array($rs) && count($rs)) {
            $data = reset($rs);
            $result = new EPeriod($data);
        }
        return $result;
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