<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last;

/**
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getCalc()
 * @method \Praxigento\BonusBase\Repo\Data\Period getPeriod()
 * @method void setCalc(\Praxigento\BonusBase\Repo\Data\Calculation $data)
 * @method void setPeriod(\Praxigento\BonusBase\Repo\Data\Period $data)
 */
class Response
    extends \Praxigento\Core\App\Service\Response
{
    /** \Praxigento\BonusBase\Repo\Data\Calculation */
    const CALC = 'calc';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const PERIOD = 'period';
}