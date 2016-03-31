<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Rank;

use Praxigento\Bonus\Base\Lib\Entity\Rank;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Call_UnitTest extends \Praxigento\Core\Lib\Test\BaseTestCase {

    public function test_getByCode() {
        /** === Test Data === */
        $ID = 1;
        $CODE = 'CODE';
        $NOTE = 'NOTE';
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
            Rank::ATTR_ID   => $ID,
            Rank::ATTR_CODE => $CODE,
            Rank::ATTR_NOTE => $NOTE
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
        $req = new Request\GetByCode($CODE);
        $resp = $call->getByCode($req);
        $this->assertTrue($resp->isSucceed());
    }

}