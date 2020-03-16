<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get;

/**
 * Get period for the first calculation in the chain.
 *
 * This service registers new calculation if it is possible.
 */
interface Basis
{
    /**
     * @param \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis\Request $request
     * @return \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis\Response
     */
    public function execute($request);
}
