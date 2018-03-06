<?php
/**
 * Qualification function to be used in downline tree compression.
 * See \Praxigento\BonusBase\Service\ICompress
 *
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Tool;


interface IQualifyUser {

    /**
     * @param array $data
     *
     * @return bool
     */
    public function isQualified(array $data);

}