<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2020
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis;

/**
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getCalcData()
 * @method int getCalcId()
 * @method \Praxigento\BonusBase\Repo\Data\Period getPeriodData()
 * @method int getPeriodId()
 * @method void setCalcData(\Praxigento\BonusBase\Repo\Data\Calculation $data)
 * @method void setCalcId(int $data)
 * @method void setPeriodData(\Praxigento\BonusBase\Repo\Data\Period $data)
 * @method void setPeriodId(int $data)
 */
class Response
    extends \Praxigento\Core\App\Service\Response
{
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const CALC_DATA = 'calcData';
    const CALC_ID = 'calcId';
    /**
     * Error codes for $this->errorCode
     */
    const ERR_CALC_NOT_COMPLETE = 'calcNotComplete';
    const ERR_NO_TRANS_YET = 'noTransYet';

    /** \Praxigento\BonusBase\Repo\Data\Period */
    const PERIOD_DATA = 'periodData';
    const PERIOD_ID = 'periodId';
}
