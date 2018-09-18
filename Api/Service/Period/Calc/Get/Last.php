<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last\Request as ARequest;
use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last\Response as AResponse;

/**
 * Get the last complete calculation by type.
 */
interface Last
{
    /**
     * @param ARequest $request
     *
     * @return AResponse
     */
    public function exec($request);

}