<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent;

/**
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Calculation getBaseCalcData()
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Period getBasePeriodData()
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Calculation getDepCalcData()
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Period getDepPeriodData()
 * @method void setBaseCalcData(\Praxigento\BonusBase\Repo\Entity\Data\Calculation $data)
 * @method void setBasePeriodData(\Praxigento\BonusBase\Repo\Entity\Data\Period $data)
 * @method void setDepCalcData(\Praxigento\BonusBase\Repo\Entity\Data\Calculation $data)
 * @method void setDepPeriodData(\Praxigento\BonusBase\Repo\Entity\Data\Period $data)
 */
class Response
    extends \Praxigento\Core\App\Service\Base\Response
{
    /** \Praxigento\BonusBase\Repo\Entity\Data\Calculation */
    const BASE_CALC_DATA = 'baseCalcData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Period */
    const BASE_PERIOD_DATA = 'basePeriodData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Calculation */
    const DEP_CALC_DATA = 'depCalcData';
    /** \Praxigento\BonusBase\Repo\Entity\Data\Period */
    const DEP_PERIOD_DATA = 'depPeriodData';
    /**
     * Error codes for $this->errorCode
     */
    const ERR_ADD_NEW_PERIOD = 'addNewPeriod';
    const ERR_BASE_CALC_NOT_COMPLETE = 'baseCalcNotComplete';
    const ERR_BASE_CALC_NOT_EXIST = 'baseCalcNotExist';
    const ERR_DEP_CALC_COMPLETE = 'depCalcComplete';
}