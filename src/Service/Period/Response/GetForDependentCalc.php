<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

/**
 * Response contains arrays with period and calculation data for base and dependent calculations.
 *
 * @method \Praxigento\BonusBase\Data\Entity\Calculation getBaseCalcData()
 * @method void setBaseCalcData(\Praxigento\BonusBase\Data\Entity\Calculation|array $data)
 * @method \Praxigento\BonusBase\Data\Entity\Period getBasePeriodData()
 * @method void setBasePeriodData(\Praxigento\BonusBase\Data\Entity\Period|array $data)
 * @method \Praxigento\BonusBase\Data\Entity\Calculation getDependentCalcData()
 * @method void setDependentCalcData(\Praxigento\BonusBase\Data\Entity\Calculation|array $data)
 * @method \Praxigento\BonusBase\Data\Entity\Period getDependentPeriodData()
 * @method void setDependentPeriodData(\Praxigento\BonusBase\Data\Entity\Period|array $data)
 */
class GetForDependentCalc extends \Praxigento\Core\Service\Base\Response
{

}