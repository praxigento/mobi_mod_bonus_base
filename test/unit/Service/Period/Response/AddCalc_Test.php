<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period\Response;

use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;
include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class AddCalc_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Mockery
{
    public function test_setCalculation()
    {
        $obj = new AddCalc();
        $mData = new ECalculation();
        $obj->setCalculation($mData);
        $this->assertEquals($mData, $obj->getCalculation());
    }

    public function test_setPeriod()
    {
        $obj = new AddCalc();
        $mData = new EPeriod();
        $obj->setPeriod($mData);
        $this->assertEquals($mData, $obj->getPeriod());
    }
}