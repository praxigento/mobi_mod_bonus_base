<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Lib\Service\Period\Response;

/**
 * @method array getCalcData()
 * @method void setCalcData(array $data)
 * @method array getPeriodData()
 * @method void setPeriodData(array $data)
 */
class GetForPvBasedCalc extends \Praxigento\Core\Service\Base\Response {
    const ERR_HAS_NO_PV_TRANSACTIONS_YET = 'HAS_NO_PV_TRANSACTIONS_YET';
    const ERR_PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY = 'PERIOD_CAN_BE_REGISTERED_IN_PAST_ONLY';
    const ERR_NO_CALC_FOR_EXISTING_PERIOD = 'NO_CALC_FOR_EXISTING_PERIOD';
}