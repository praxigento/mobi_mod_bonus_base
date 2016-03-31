<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Tool\Def;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class QualifyUser_UnitTest extends \Praxigento\Core\Lib\Test\BaseMockeryCase {
    /** @var  QualifyUser */
    private $obj;

    protected function setUp() {
        parent::setUp();
        $this->obj = new QualifyUser();
    }

    public function test_isQualified() {
        /** === Test Data === */
        /** === Setup Mocks === */
        /** === Call and asserts  === */
        $resp = $this->obj->isQualified([]);
        $this->assertTrue($resp);
    }
}