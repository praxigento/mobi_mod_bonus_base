<?php
/**
 * Wrapper for core config.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Tool\Def;

class QualifyUser
    implements \Praxigento\BonusBase\Tool\IQualifyUser
{
    /**
     * Default implementation: always 'true'.
     *
     * @param array $data
     *
     * @return bool
     */
    public function isQualified(array $data)
    {
        return true;
    }

}