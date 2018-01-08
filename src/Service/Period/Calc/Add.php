<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Entity\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Entity\Data\Period as EPeriod;

class Add
    implements IAdd
{
    /** @var \Praxigento\Core\Api\Helper\Date */
    protected $hlpDate;
    /** @var \Praxigento\Core\Api\Helper\Period */
    protected $hlpPeriod;
    /** @var \Praxigento\BonusBase\Repo\Entity\Calculation */
    protected $repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\Period */
    protected $repoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Entity\Type\Calc */
    protected $repoTypeCalc;

    public function __construct(
        \Praxigento\Core\Api\Helper\Date $hlpDate,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusBase\Repo\Entity\Calculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\Period $repoPeriod,
        \Praxigento\BonusBase\Repo\Entity\Type\Calc $repoTypeCalc
    )
    {
        $this->hlpDate = $hlpDate;
        $this->hlpPeriod = $hlpPeriod;
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
        /* reset 'success processing' flag */
        $ctx->set(self::CTX_OUT_SUCCESS, false);

        /**
         * perform processing
         */
        $dsToday = $this->hlpPeriod->getPeriodCurrent();
        if ($dsEnd <= $dsToday) {
            /* the end of the new period is not in the future */
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
            $ctx->set(self::CTX_OUT_SUCCESS, true);
        } else {
            /* the end of the new period is in the future - error */
            $ctx->set(self::ERR_PERIOD_END_IS_IN_FUTURE, true);
        }
    }
}