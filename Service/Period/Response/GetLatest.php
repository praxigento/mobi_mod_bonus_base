<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

/**
 * @method \Praxigento\BonusBase\Repo\Data\Calculation|null getCalcData() Contains data for the latest period calculation.
 * @method void setCalcData(\Praxigento\BonusBase\Repo\Data\Calculation | array $data)
 * @method \Praxigento\BonusBase\Repo\Data\Period|null getPeriodData()
 * @method void setPeriodData(\Praxigento\BonusBase\Repo\Data\Calculation | array $data)
 */
class GetLatest extends \Praxigento\Core\App\Service\Response
{

}