<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Type\Calc;

use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as TypeCalc;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class Call_UnitTest extends \Praxigento\Core\Lib\Test\BaseTestCase {

    public function test_getByCode() {
        /** === Test Data === */
        $ASSET_TYPE_ID = '21';
        $ASSET_CODE = 'CODE';
        $ASSET_NOTE = 'NOTE';
        /** === Mocks === */
        $mLogger = $this->_mockLogger();
        $mConn = $this->_mockConnection();
        $mDba = $this->_mockDbAdapter(null, $mConn);
        $mToolbox = $this->_mockToolbox();
        $mCallRepo = $this->_mockCallRepo();

        // $query = $this->_conn->select();
        $mQuery = $this->_mockDbSelect();
        $mConn
            ->expects($this->once())
            ->method('select')
            ->will($this->returnValue($mQuery));
        // $data = $this->_conn->fetchRow($query, [ 'code' => $assetCode ]);
        $mData = [
            TypeCalc::ATTR_ID   => $ASSET_TYPE_ID,
            TypeCalc::ATTR_CODE => $ASSET_CODE,
            TypeCalc::ATTR_NOTE => $ASSET_NOTE
        ];
        $mConn
            ->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue($mData));
        /**
         * Prepare request and perform call.
         */
        /** @var  $call Call */
        $call = new Call($mLogger, $mDba, $mToolbox, $mCallRepo);
        $req = new Request\GetByCode($ASSET_CODE);
        $resp = $call->getByCode($req);
        $this->assertTrue($resp->isSucceed());
    }

}