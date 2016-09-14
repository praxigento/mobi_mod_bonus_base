<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Service\Compress;

use Praxigento\BonusBase\Tool\IQualifyUser;

use Praxigento\Downline\Data\Entity\Customer;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_ManualTest extends \Praxigento\Core\Test\BaseCase\Mockery {

    public function test_qualifyByUserData() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * Prepare request data.
         */
        /** @var  $callDownlineSnap \Praxigento\Downline\Service\ISnap */
        $callDownlineSnap = $obm->get('\Praxigento\Downline\Service\ISnap');
        $reqSnap = new \Praxigento\Downline\Service\Snap\Request\GetStateOnDate();
        $reqSnap->setDatestamp('20201231');
        $respSnap = $callDownlineSnap->getStateOnDate($reqSnap);
        $flatTree = $respSnap->getData();
        /** @var  $call \Praxigento\BonusBase\Service\Compress\Call */
        $call = $obm->get('Praxigento\BonusBase\Service\Compress\Call');
        $request = new Request\QualifyByUserData();
        $request->setCalcId(6);
        $request->setFlatTree($flatTree);
        $request->setQualifier(new CustomQualifyUser());
        $response = $call->qualifyByUserData($request);
        $this->assertTrue($response->isSucceed());
    }

}

class CustomQualifyUser implements IQualifyUser {
    public function isQualified(array $data) {
        $result = $data[Customer::ATTR_CUSTOMER_ID] < 5;
        return $result;
    }

}