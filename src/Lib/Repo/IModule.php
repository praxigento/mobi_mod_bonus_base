<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Lib\Repo;

use Flancer32\Lib\DataObject;

interface IModule
{

    const A_CALC = 'calc';
    const A_PERIOD = 'period';

    /**
     * Register new record in the log.
     *
     * @param int $transId
     * @param int $saleOrderId
     */
    public function addLogSaleOrder($transId, $saleOrderId);

    /**
     * Create new period record and related calculation record.
     *
     * @param int $calcTypeId
     * @param string $dsBegin 'YYYYMMDD'
     * @param string $dsEnd 'YYYYMMDD'
     *
     * @return DataObject
     */
    public function addPeriod($calcTypeId, $dsBegin, $dsEnd);

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
     * @param $calcId
     *
     * @return array [[Compress/*], ...]
     */
    public function getCompressedTree($calcId);

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
     * Get the latest period and related calculation(s).
     *
     * @param int $calcTypeId
     * @param bool $shouldGetLatestCalc
     * @param bool $shouldGetAllCalcs
     *
     * @return DataObject
     */
    public function getLatestPeriod($calcTypeId, $shouldGetLatestCalc = true, $shouldGetAllCalcs = false);

    /**
     * @param string $calcTypeCode
     *
     * @return int
     */
    public function getRankIdByCode($calcTypeCode);

    /**
     * @param string $calcTypeCode
     *
     * @return int
     */
    public function getTypeCalcIdByCode($calcTypeCode);

    /**
     * Register new record in the log.
     *
     * @param int $transRef
     * @param int $rankRef
     */
    public function logRank($transRef, $rankRef);

    /**
     * @param int $calcId
     * @param array $tree [[Snap::ATTR_CUSTOMER_ID, Snap::ATTR_PARENT_ID], ...]
     */
    public function saveCompressedTree($calcId, $tree);

    /**
     * @param int $calcId
     *
     * @return int number of updated rows (expected one only)
     */
    public function updateCalcSetComplete($calcId);
}