<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc;

/* WARNING: reverse dependency, just for tests; hyb module depends on base, not vice versa. */
use Praxigento\BonusBase\Service\Period\Calc\Get\IBasis as IBasis;
use Praxigento\BonusHybrid\Config as CfgHyb;


include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class Basis_Test
    extends \Praxigento\Core\Test\BaseCase\Manual
{
    public function test_exec()
    {
        $def = $this->manTrans->begin();
        /** @var  $proc \Praxigento\BonusBase\Service\Period\Calc\Get\IBasis */
        $proc = $this->manObj->get(\Praxigento\BonusBase\Service\Period\Calc\Get\IBasis::class);
        /* prepare execution context */
        $ctx = new \Praxigento\Core\Data();
        $ctx->set(IBasis::CTX_IN_CALC_CODE, CfgHyb::CODE_TYPE_CALC_BONUS_SIGNUP_DEBIT);
        $ctx->set(IBasis::CTX_IN_ASSET_TYPE_CODE, CfgHyb::CODE_TYPE_ASSET_PV);
        /* processing */
        $proc->exec($ctx);
        // rollback
        $this->manTrans->rollback($def);
//        $this->manTrans->commit($def);
    }

}