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

    /**
     * @param $calcTypeId
     *
     * @return array [$rankId=>[$gen=>$percent, ...], ...]
     */
    public function getConfigGenerationsPercents($calcTypeId);

    /**
     * @return string 'Y-m-d H:i:s'
     */
    public function getFirstDateForPvTransactions();

    /**
     * @param int $calcId
     * @param array $tree [[Snap::ATTR_CUSTOMER_ID, Snap::ATTR_PARENT_ID], ...]
     */
    public function saveCompressedTree($calcId, $tree);

}