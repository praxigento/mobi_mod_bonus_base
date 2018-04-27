<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent;

/**
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getBaseCalcData()
 * @method \Praxigento\BonusBase\Repo\Data\Period getBasePeriodData()
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getDepCalcData()
 * @method \Praxigento\BonusBase\Repo\Data\Period getDepPeriodData()
 * @method void setBaseCalcData(\Praxigento\BonusBase\Repo\Data\Calculation $data)
 * @method void setBasePeriodData(\Praxigento\BonusBase\Repo\Data\Period $data)
 * @method void setDepCalcData(\Praxigento\BonusBase\Repo\Data\Calculation $data)
 * @method void setDepPeriodData(\Praxigento\BonusBase\Repo\Data\Period $data)
 */
class Response
    extends \Praxigento\Core\App\Service\Response
{
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const BASE_CALC_DATA = 'baseCalcData';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const BASE_PERIOD_DATA = 'basePeriodData';
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const DEP_CALC_DATA = 'depCalcData';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const DEP_PERIOD_DATA = 'depPeriodData';
    /**
     * Error codes for $this->errorCode
     */
    const ERR_ADD_NEW_PERIOD = 'addNewPeriod';
    const ERR_BASE_CALC_NOT_COMPLETE = 'baseCalcNotComplete';
    const ERR_BASE_CALC_NOT_EXIST = 'baseCalcNotExist';
    const ERR_DEP_CALC_COMPLETE = 'depCalcComplete';
}