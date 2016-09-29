<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;


use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

/**
 * @method \Praxigento\BonusBase\Data\Entity\Period getPeriod()
 * @method \Praxigento\BonusBase\Data\Entity\Calculation getCalculation()
 */
class AddCalc
    extends \Praxigento\Core\Service\Base\Response
{

    public function setCalculation($data)
    {
        if ($data instanceof ECalculation) {
            parent::setCalculation($data);
        } else {
            $dataObj = new ECalculation($data);
            parent::setCalculation($dataObj);
        }
    }

    public function setPeriod($data)
    {
        if ($data instanceof EPeriod) {
            parent::setPeriod($data);
        } else {
            $dataObject = new EPeriod($data);
            parent::setPeriod($dataObject);
        }
    }
}