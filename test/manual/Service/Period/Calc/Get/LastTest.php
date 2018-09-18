<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

/* WARNING: reverse dependency, just for tests; hyb module depends on base, not vice versa. */

use Praxigento\BonusHybrid\Config as Cfg;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class LastTest
    extends \Praxigento\Core\Test\BaseCase\Manual
{
    public function test_exec()
    {
        /** @var  $obj \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last */
        $obj = $this->manObj->get(\Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last::class);
        $request = new \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last\Request();
        $request->setCalcTypeCode(Cfg::CODE_TYPE_CALC_PV_WRITE_OFF);
        $resp = $obj->exec($request);
        $calc = $resp->getCalc();
        $this->assertNotNull($calc);
    }

}