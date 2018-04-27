<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

/**
 * Get period for the next calculation in the chain dependent on the other calculation.
 *
 * This service registers new calculation if it is possible.
 *
 * @deprecated use \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent
 */
interface IDependent
    extends \Praxigento\Core\Api\App\Service\Process
{
    /** string code of the calculation type to load/register */
    const CTX_IN_BASE_TYPE_CODE = 'in.baseCalcTypeCode';
    /** bool - 'true' ignore complete state for depended calculations */
    const CTX_IN_DEP_IGNORE_COMPLETE = 'in.depIgnoreComplete';
    /** string - code of the depended calculation type */
    const CTX_IN_DEP_TYPE_CODE = 'in.depCalcTypeCode';
    /** string - (optional) datestamp for the last date of the period end to search (including) */
    const CTX_IN_PERIOD_END = 'in.periodEnd';
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const CTX_OUT_BASE_CALC_DATA = 'out.baseCalcData';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const CTX_OUT_BASE_PERIOD_DATA = 'out.basePeriodData';
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const CTX_OUT_DEP_CALC_DATA = 'out.depCalcData';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const CTX_OUT_DEP_PERIOD_DATA = 'out.depPeriodData';
    /** string - see self::ERR_... */
    const CTX_OUT_ERROR_CODE = 'out.errCode';
    /**
     * Error codes for self::CTX_OUT_ERROR_CODE
     */
    const ERR_ADD_NEW_PERIOD = 'err.addNewPeriod';
    const ERR_BASE_CALC_NOT_COMPLETE = 'err.baseCalcNotComplete';
    const ERR_BASE_CALC_NOT_EXIST = 'err.baseCalcNotExist';
    const ERR_DEP_CALC_COMPLETE = 'err.depCalcComplete';
}