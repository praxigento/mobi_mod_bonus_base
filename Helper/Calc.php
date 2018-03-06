<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Helper;

/**
 * Functions to calculate base values.
 */
class Calc
{
    /** @var \Praxigento\Core\Api\Helper\Format */
    private $hlpFormat;

    public function __construct(
        \Praxigento\Core\Api\Helper\Format $hlpFormat
    )
    {
        $this->hlpFormat = $hlpFormat;
    }

    /**
     * Look up for the appropriate level for given $value and calculate percent.
     *
     * @param float $value PV amount for period to calculate bonus amount
     * @param array $levels Personal Bonus levels and percents ([level => percent]) where levels go from small to high (see 'prxgt_bon_base_level').
     *
     * @return string
     */
    public function calcForLevelPercent($value, $levels)
    {
        $mult = 0;
        foreach ($levels as $level => $percent) {
            if ($value < $level) {
                break;
            } elseif ($value == $level) {
                $mult = $percent;
                break;
            }
            $mult = $percent;
        }
        $bonus = $value * $mult;
        $result = $this->hlpFormat->roundBonus($bonus);
        return $result;
    }
}