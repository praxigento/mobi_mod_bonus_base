<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalc;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

class Add
    implements IAdd
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $hlpDate;
    /** @var \Praxigento\BonusBase\Repo\Entity\Calculation */
    protected $repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\Period */
    protected $repoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Entity\Type\Calc */
    protected $repoTypeCalc;

    public function __construct(
        \Praxigento\Core\Tool\IDate $hlpDate,
        \Praxigento\BonusBase\Repo\Entity\Calculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\Period $repoPeriod,
        \Praxigento\BonusBase\Repo\Entity\Type\Calc $repoTypeCalc
    )
    {
        $this->hlpDate = $hlpDate;
        $this->repoCalc = $repoCalc;
        $this->repoPeriod = $repoPeriod;
        $this->repoTypeCalc = $repoTypeCalc;
    }

    public function exec(\Praxigento\Core\Data $ctx)
    {
        /* get working data from context */
        $calcTypeCode = $ctx->get(self::CTX_IN_CALC_TYPE_CODE);
        $dsBegin = $ctx->get(self::CTX_IN_DSTAMP_BEGIN);
        $dsEnd = $ctx->get(self::CTX_IN_DSTAMP_END);

        /**
         * perform processing
         */
        $calcTypeId = $this->repoTypeCalc->getIdByCode($calcTypeCode);
        /* registry new period */
        $period = new EPeriod();
        $period->setCalcTypeId($calcTypeId);
        $period->setDstampBegin($dsBegin);
        $period->setDstampEnd($dsEnd);
        $periodId = $this->repoPeriod->create($period);
        /* registry new calculation for the period */
        $dateStarted = $this->hlpDate->getUtcNowForDb();
        $calc = new ECalc();
        $calc->setPeriodId($periodId);
        $calc->setDateStarted($dateStarted);
        $calc->setState(Cfg::CALC_STATE_STARTED);
        $calcId = $this->repoCalc->create($calc);

        /* put result data into context */
        $ctx->set(self::CTX_OUT_PERIOD_ID, $periodId);
        $ctx->set(self::CTX_OUT_CALC_ID, $calcId);
    }
}