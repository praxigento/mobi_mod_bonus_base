<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\All;

/**
 * @method \Praxigento\BonusBase\Repo\Data\Calculation[] getCalcs()
 * @method \Praxigento\BonusBase\Repo\Data\Period getPeriod()
 * @method void setCalcs(\Praxigento\BonusBase\Repo\Data\Calculation $data)
 * @method void setPeriod(\Praxigento\BonusBase\Repo\Data\Period $data)
 */
class Response
    extends \Praxigento\Core\App\Service\Response
{
    /** \Praxigento\BonusBase\Repo\Data\Calculation[] */
    const CALC = 'calcs';
    /** \Praxigento\BonusBase\Repo\Data\Period */
    const PERIOD = 'period';
}