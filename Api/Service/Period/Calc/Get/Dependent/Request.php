<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent;

/**
 * @method string getBaseCalcTypeCode()
 * @method string getDepCalcTypeCode()
 * @method bool getDepIgnoreComplete()
 * @method string getPeriodEnd()
 * @method void setBaseCalcTypeCode(string $data)
 * @method void setDepCalcTypeCode(string $data)
 * @method void setDepIgnoreComplete(bool $data)
 * @method void setPeriodEnd(string $data)
 */
class Request
    extends \Praxigento\Core\App\Service\Request
{
    /** code of the calculation type to load/register */
    const BASE_CALC_TYPE_CODE = 'baseCalcTypeCode';
    /** code of the depended calculation type */
    const DEP_CALC_TYPE_CODE = 'depCalcTypeCode';
    /** 'true' ignore complete state for depended calculations */
    const DEP_IGNORE_COMPLETE = 'depIgnoreComplete';
    /** (optional) datestamp for the last date of the period end to search (including) */
    const PERIOD_END = 'periodEnd';
}