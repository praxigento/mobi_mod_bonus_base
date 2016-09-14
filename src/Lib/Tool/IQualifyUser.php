<?php
/**
 * Qualification function to be used in downline tree compression.
 * See \Praxigento\BonusBase\Lib\Service\ICompress
 *
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Lib\Tool;


interface IQualifyUser {

    /**
     * @param array $data
     *
     * @return bool
     */
    public function isQualified(array $data);

}