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
    const CTX_IN_CALC_TYPE_CODE = 'inCalcTypeCode';
    const CTX_IN_DSTAMP_BEGIN = 'inDstampBegin';
    const CTX_IN_DSTAMP_END = 'inDstampEnd';
    const CTX_OUT_CALC_ID = 'outCalcId';
    const CTX_OUT_PERIOD_ID = 'outPeriodId';
    const ERR_PERIOD_END_IS_IN_FUTURE = 'errEndOfPeriodIsInTheFuture';
}