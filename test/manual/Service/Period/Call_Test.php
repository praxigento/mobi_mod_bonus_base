<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Period;



include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_ManualTest extends \Praxigento\Core\Test\BaseMockeryCase {

    public function test_getForDependentCalc() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $call \Praxigento\Bonus\Base\Lib\Service\Period\Call */
        $call = $obm->get('Praxigento\Bonus\Base\Lib\Service\Period\Call');
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode('LOYALTY_BON_COMPRESS');
        $req->setDependentCalcTypeCode('HYBRID_VALUE_OV');
        $resp = $call->getForDependentCalc($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getLatest() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $call \Praxigento\Bonus\Base\Lib\Service\Period\Call */
        $call = $obm->get('Praxigento\Bonus\Base\Lib\Service\Period\Call');
        $req = new Request\GetLatest();
        $req->setCalcTypeId(null);
        $req->setCalcTypeCode('LOYALTY_BON_COMPRESS');
        $req->setShouldGetLatestCalc(false);
        $req->setShouldGetAllCalcs(true);
        $resp = $call->getLatest($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getLatestForPvBasedCalc() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $dba \Praxigento\Core\Lib\Context\IDbAdapter */
        $dba = $obm->get(\Praxigento\Core\Lib\Context\IDbAdapter::class);
        $dba->getDefaultConnection()->beginTransaction();
        /** @var  $call \Praxigento\Bonus\Base\Lib\Service\Period\Call */
        $call = $obm->get('Praxigento\Bonus\Base\Lib\Service\Period\Call');
        $req = new Request\GetForPvBasedCalc();
        $req->setCalcTypeCode('LOYALTY_BON_COMPRESS');
        $resp = $call->getForPvBasedCalc($req);
        $this->assertTrue($resp->isSucceed());
        $dba->getDefaultConnection()->commit();
    }

}