<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;


use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;

/**
 * @method \Praxigento\BonusBase\Data\Entity\Period getPeriod()
 * @method \Praxigento\BonusBase\Data\Entity\Calculation getCalculation()
 */
class AddCalc
    extends \Praxigento\Core\Service\Base\Response
{

    public function setCalculation($data)
    {
        if (is_array($data)) {
            $do = new Calculation($data);
            parent::setCalculation($do);
        } else {
            parent::setCalculation($data);
        }
    }

    public function setPeriod($data)
    {
        if (is_array($data)) {
            $do = new Period($data);
            parent::setPeriod($do);
        } else {
            parent::setPeriod($data);
        }
    }
}