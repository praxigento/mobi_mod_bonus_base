<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;

class Add
    implements IAdd
{
    /** @var \Praxigento\Core\Api\Helper\Date */
    protected $hlpDate;
    /** @var \Praxigento\Core\Api\Helper\Period */
    protected $hlpPeriod;
    /** @var \Praxigento\BonusBase\Repo\Dao\Calculation */
    protected $daoCalc;
    /** @var \Praxigento\BonusBase\Repo\Dao\Period */
    protected $daoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Dao\Type\Calc */
    protected $daoTypeCalc;

    public function __construct(
        \Praxigento\Core\Api\Helper\Date $hlpDate,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusBase\Repo\Dao\Calculation $daoCalc,
        \Praxigento\BonusBase\Repo\Dao\Period $daoPeriod,
        \Praxigento\BonusBase\Repo\Dao\Type\Calc $daoTypeCalc
    )
    {
        $this->hlpDate = $hlpDate;
        $this->hlpPeriod = $hlpPeriod;
        $this->daoCalc = $daoCalc;
        $this->daoPeriod = $daoPeriod;
        $this->daoTypeCalc = $daoTypeCalc;
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
            $calcTypeId = $this->daoTypeCalc->getIdByCode($calcTypeCode);
            /* registry new period */
            $period = new EPeriod();
            $period->setCalcTypeId($calcTypeId);
            $period->setDstampBegin($dsBegin);
            $period->setDstampEnd($dsEnd);
            $periodId = $this->daoPeriod->create($period);
            /* registry new calculation for the period */
            $dateStarted = $this->hlpDate->getUtcNowForDb();
            $calc = new ECalc();
            $calc->setPeriodId($periodId);
            $calc->setDateStarted($dateStarted);
            $calc->setState(Cfg::CALC_STATE_STARTED);
            $calcId = $this->daoCalc->create($calc);

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