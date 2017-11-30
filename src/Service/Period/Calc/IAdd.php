<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

/**
 * Register new period and related calculation.
 *
 * Set error if end of the requested period is in the future.
 */
interface IAdd
    extends \Praxigento\Core\Service\IProcess
{
    const CTX_IN_BASE_PERIOD_ID = 'in.periodId';
    const CTX_IN_CALC_TYPE_CODE = 'in.calcTypeCode';
    const CTX_IN_DSTAMP_BEGIN = 'in.dstampBegin';
    const CTX_IN_DSTAMP_END = 'in.dstampEnd';
    const CTX_OUT_CALC_ID = 'out.calcId';
    const CTX_OUT_PERIOD_ID = 'out.periodId';
    const ERR_PERIOD_END_IS_IN_FUTURE = 'err.endOfPeriodIsInTheFuture';
}