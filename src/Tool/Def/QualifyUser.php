<?php
/**
 * Wrapper for core config.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Tool\Def;


use Flancer32\Lib\DataObject;
use Praxigento\BonusBase\Tool\IQualifyUser;

class QualifyUser implements IQualifyUser {
    /**
     * Default implementation is always 'true'.
     *
     * @param DataObject $data
     *
     * @return bool
     */
    public function isQualified(array $data) {
        return true;
    }

}