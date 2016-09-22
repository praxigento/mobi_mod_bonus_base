<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service;

use Praxigento\BonusBase\Service\Period\Request;
use Praxigento\BonusBase\Service\Period\Response;

interface IPeriod
{
    /**
     * Create new calculation for given period.
     *
     * @param Request\AddCalc $request
     * @return Response\AddCalc
     */
    public function addCalc(Request\AddCalc $request);

    /**
     * Get period data for calculation dependent on the other calculation.
     *
     * @param Request\GetForDependentCalc $request
     *
     * @return Response\GetForDependentCalc
     */
    public function getForDependentCalc(Request\GetForDependentCalc $request);

    /**
     * @param Request\GetForPvBasedCalc $request
     *
     * @return Response\GetForPvBasedCalc
     */
    public function getForPvBasedCalc(Request\GetForPvBasedCalc $request);

    /**
     * @param Request\GetLatest $request
     *
     * @return Response\GetLatest
     */
    public function getLatest(Request\GetLatest $request);

    /**
     * @param Request\RegisterPeriod $request
     *
     * @return Response\RegisterPeriod
     */
    public function registerPeriod(Request\RegisterPeriod $request);

}