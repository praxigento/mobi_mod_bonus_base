<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service;


/**
 * Common repo service for BonusBase module.
 */
interface IModule
{
    /**
     * @return string 'Y-m-d H:i:s'
     */
    public function getFirstDateForPvTransactions();

    /**
     * Get calculations (or the last one) by Calculation Type ID and period's bounds (from & to).
     *
     * @param int $calcTypeId
     * @param string $dsBegin 'YYYYMMDD'
     * @param string $dsEnd 'YYYYMMDD'
     *
     * @return \Praxigento\BonusBase\Data\Entity\Calculation|null
     */
    public function getLastCalcForPeriodByDates($calcTypeId, $dsBegin, $dsEnd);

    /**
     * Get the last calculation for period.
     *
     * @param int $periodId
     * @return \Praxigento\BonusBase\Data\Entity\Calculation|null
     */
    public function getLastCalcForPeriodById($periodId);

    /**
     * Get the last period data by calculation type.
     *
     * @param int $calcTypeId
     * @return \Praxigento\BonusBase\Data\Entity\Period|null
     */
    public function getLastPeriodByCalcType($calcTypeId);

    /**
     * Set complete state for given calculation and save 'date_ended' as current UTC date.
     *
     * @param int $calcId
     * @return null
     */
    public function markCalcComplete($calcId);
}