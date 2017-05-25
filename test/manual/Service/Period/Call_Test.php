<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Period;


use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_ManualTest
    extends \Praxigento\Core\Test\BaseCase\Manual
{
    public function test_addCalc()
    {
        $def = $this->manTrans->begin();
        /** @var  $call \Praxigento\BonusBase\Service\Period\Call */
        $call = $this->manObj->get(\Praxigento\BonusBase\Service\IPeriod::class);
        $req = new Request\AddCalc();
        $req->setCalcTypeId(2);
        $req->setDateStampBegin('20160922');
        $req->setDateStampEnd('20160925');
        $res = $call->addCalc($req);
        $this->assertTrue($res->isSucceed());
        $period = $res->getPeriod();
        $this->assertInstanceOf(Period::class, $period);
        $calc = $res->getCalculation();
        $this->assertInstanceOf(Calculation::class, $calc);
        // rollback
        $this->manTrans->rollback($def);
    }

    public function test_getForDependentCalc()
    {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $call \Praxigento\BonusBase\Service\Period\Call */
        $call = $obm->get('Praxigento\BonusBase\Service\Period\Call');
        $req = new Request\GetForDependentCalc();
        $req->setBaseCalcTypeCode('LOYALTY_BON_COMPRESS');
        $req->setDependentCalcTypeCode('HYBRID_VALUE_OV');
        $resp = $call->getForDependentCalc($req);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_getLatest()
    {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $call \Praxigento\BonusBase\Service\Period\Call */
        $call = $obm->get(\Praxigento\BonusBase\Service\IPeriod::class);
        $req = new Request\GetLatest();
        $req->setCalcTypeId(null);
        $req->setCalcTypeCode('LOYALTY_BON_COMPRESS');
        $resp = $call->getLatest($req);
        $this->assertTrue($resp->isSucceed());
    }


}