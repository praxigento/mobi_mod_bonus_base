<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service\Def;

use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Module_ManualTest
    extends \Praxigento\Core\Test\BaseCase\Manual
{

    public function test_getLastCalcForPeriod()
    {
        /** @var \Praxigento\BonusBase\Repo\Service\Def\Module $obj */
        $obj = $this->_manObj->get(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $res = $obj->getLastCalcForPeriodById(1);
        $this->assertInstanceOf(ECalculation::class, $res);
    }

    public function test_getLastPeriodByCalcType()
    {
        /** @var \Praxigento\BonusBase\Repo\Service\Def\Module $obj */
        $obj = $this->_manObj->get(\Praxigento\BonusBase\Repo\Service\IModule::class);
        $res = $obj->getLastPeriodByCalcType(2);
        $this->assertInstanceOf(EPeriod::class, $res);
    }


}