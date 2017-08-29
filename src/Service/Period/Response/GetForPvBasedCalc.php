<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

/**
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Calculation getCalcData()
 * @method void setCalcData(\Praxigento\BonusBase\Repo\Entity\Data\Calculation | array $data)
 * @method \Praxigento\BonusBase\Repo\Entity\Data\Period getPeriodData()
 * @method void setPeriodData(\Praxigento\BonusBase\Repo\Entity\Data\Period | array $data)
 */
class GetForPvBasedCalc extends \Praxigento\Core\Service\Base\Response
{
    const ERR_HAS_NO_PV_TRANSACTIONS_YET = 'HAS_NO_PV_TRANSACTIONS_YET';
    const ERR_NO_CALC_FOR_EXISTING_PERIOD = 'NO_CALC_FOR_EXISTING_PERIOD';
    const ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY = 'PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY';
}