<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo;

/**
 * @deprecated use \Praxigento\BonusBase\Repo\Service\IModule or entities repo instead.
 */
interface IModule
{

    const A_CALC = 'calc';
    const A_PERIOD = 'period';

    /**
     * Get calculations (or the last one) by Calculation Type ID and period's bounds (from & to).
     *
     * @param int $calcTypeId
     * @param string $dsBegin 'YYYYMMDD'
     * @param string $dsEnd 'YYYYMMDD'
     * @param bool $shouldGetLatestCalc
     *
     * @return array [[Calculation/*], ...] or [Calculation/*]
     */
    public function getCalcsForPeriod($calcTypeId, $dsBegin, $dsEnd, $shouldGetLatestCalc = false);

}