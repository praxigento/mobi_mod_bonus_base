<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get\All;

/**
 * @method string getPeriod()
 * @method void setPeriod(string $data)
 */
class Request
    extends \Praxigento\Core\App\Service\Request
{
    /** string YYYYMM */
    const PERIOD = 'period';
}