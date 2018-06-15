<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Api\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\All\Request as ARequest;
use Praxigento\BonusBase\Api\Service\Period\Calc\Get\All\Response as AResponse;

/**
 * Get all complete calculations for given period.
 */
interface All
{
    /**
     * @param ARequest $request
     *
     * @return AResponse
     */
    public function exec($request);

}