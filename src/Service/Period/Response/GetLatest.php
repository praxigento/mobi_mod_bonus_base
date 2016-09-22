<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

/**
 * @method \Praxigento\BonusBase\Data\Entity\Calculation|null getCalcData() Contains data for the latest period calculation.
 * @method void setCalcData(\Praxigento\BonusBase\Data\Entity\Calculation|array $data)
 * @method \Praxigento\BonusBase\Data\Entity\Period|null getPeriodData()
 * @method void setPeriodData(\Praxigento\BonusBase\Data\Entity\Calculation|array $data)
 */
class GetLatest extends \Praxigento\Core\Service\Base\Response
{

}