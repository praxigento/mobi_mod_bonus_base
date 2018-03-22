<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

/**
 * Response contains arrays with period and calculation data for base and dependent calculations.
 *
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getBaseCalcData()
 * @method void setBaseCalcData(\Praxigento\BonusBase\Repo\Data\Calculation|array $data)
 * @method \Praxigento\BonusBase\Repo\Data\Period getBasePeriodData()
 * @method void setBasePeriodData(\Praxigento\BonusBase\Repo\Data\Period|array $data)
 * @method \Praxigento\BonusBase\Repo\Data\Calculation getDependentCalcData()
 * @method void setDependentCalcData(\Praxigento\BonusBase\Repo\Data\Calculation|array $data)
 * @method \Praxigento\BonusBase\Repo\Data\Period getDependentPeriodData()
 * @method void setDependentPeriodData(\Praxigento\BonusBase\Repo\Data\Period|array $data)
 */
class GetForDependentCalc extends \Praxigento\Core\App\Service\Base\Response
{

}