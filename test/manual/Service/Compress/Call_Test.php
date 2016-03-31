<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Compress;

use Praxigento\Bonus\Base\Lib\Tool\IQualifyUser;
use Praxigento\Core\Lib\Context;
use Praxigento\Downline\Lib\Entity\Customer;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Call_ManualTest extends \Praxigento\Core\Lib\Test\BaseTestCase {

    public function test_qualifyByUserData() {
        $obm = Context::instance()->getObjectManager();
        /**
         * Prepare request data.
         */
        /** @var  $callDownlineSnap \Praxigento\Downline\Lib\Service\ISnap */
        $callDownlineSnap = $obm->get('\Praxigento\Downline\Lib\Service\ISnap');
        $reqSnap = new \Praxigento\Downline\Lib\Service\Snap\Request\GetStateOnDate();
        $reqSnap->setDatestamp('20201231');
        $respSnap = $callDownlineSnap->getStateOnDate($reqSnap);
        $flatTree = $respSnap->getData();
        /** @var  $call \Praxigento\Bonus\Base\Lib\Service\Compress\Call */
        $call = $obm->get('Praxigento\Bonus\Base\Lib\Service\Compress\Call');
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