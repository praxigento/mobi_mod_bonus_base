<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service;

use Praxigento\BonusBase\Service\Period\Request;
use Praxigento\BonusBase\Service\Period\Response;

/**
 * @deprecated this service should be split to the set of standalone operations.
 */
interface IPeriod
{

    /**
     * @param Request\GetLatest $request
     *
     * @return Response\GetLatest
     *
     * @deprecated use \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\...
     */
    public function getLatest(Request\GetLatest $request);

}