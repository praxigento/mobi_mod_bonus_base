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
     * Set complete state for given calculation and save 'date_ended' as current UTC date.
     *
     * @param int $calcId
     * @return null
     */
    public function markCalcComplete($calcId);
}