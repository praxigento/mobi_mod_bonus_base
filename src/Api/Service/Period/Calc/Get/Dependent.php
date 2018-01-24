<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent\Request as ARequest;
use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Dependent\Response as AResponse;

/**
 * Get period for the next calculation in the chain dependent on the other calculation.
 *
 * This service registers new calculation if it is possible.
 */
interface Dependent
{
    /**
     * @param ARequest $request
     *
     * @return AResponse
     */
    public function exec($request);

}