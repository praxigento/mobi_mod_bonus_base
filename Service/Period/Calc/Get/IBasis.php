<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

/**
 * Get period for the first calculation in the chain.
 *
 * This service registers new calculation if it is possible.
 */
interface IBasis
    extends \Praxigento\Core\App\Service\IProcess
{
    const CTX_IN_ASSET_TYPE_CODE = 'in.assetTypeCode';
    const CTX_IN_CALC_CODE = 'in.calcCode';
    /** bool - if 'true' then period & calc data will be loaded from repo (periodData & calcData in out) */
    const CTX_IN_LOAD_DATA = 'in.loadData';
    const CTX_IN_PERIOD_TYPE = 'in.periodType';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Calculation */
    const CTX_OUT_CALC_DATA = 'out.calcData';
    const CTX_OUT_CALC_ID = 'out.calcId';
    const CTX_OUT_ERROR_CODE = 'out.errCode';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Period */
    const CTX_OUT_PERIOD_DATA = 'out.periodData';
    const CTX_OUT_PERIOD_ID = 'out.periodId';

    /**
     * Error codes for self::CTX_OUT_ERROR_CODE
     */
    const ERR_CALC_NOT_COMPLETE = 'err.calcNotComplete';
    const ERR_NO_TRANS_YET = 'err.noTransYet';

}