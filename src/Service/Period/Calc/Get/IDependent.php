<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

/**
 * Get period for the next calculation in the chain dependent on the other calculation.
 *
 * This service registers new calculation if it is possible.
 */
interface IDependent
    extends \Praxigento\Core\Service\IProcess
{
    /** string code of the calculation type to load/register */
    const CTX_IN_BASE_TYPE_CODE = 'in.baseCalcTypeCode';
    /** string - code of the depended calculation type */
    const CTX_IN_DEP_TYPE_CODE = 'in.depCalcTypeCode';
    /** string - see \Praxigento\Core\Tool\IPeriod::TYPE_... */
    const CTX_IN_PERIOD_TYPE = 'in.periodType';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Calculation */
    const CTX_OUT_BASE_CALC_DATA = 'out.baseCalcData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Period */
    const CTX_OUT_BASE_PERIOD_DATA = 'out.basePeriodData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Calculation */
    const CTX_OUT_DEP_CALC_DATA = 'out.depCalcData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Period */
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