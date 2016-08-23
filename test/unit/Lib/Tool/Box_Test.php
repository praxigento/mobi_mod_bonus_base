<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Tool;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Box_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery {
    /** @var  Box */
    private $obj;
    /** @var  \Mockery\MockInterface */
    private $mTree;

    protected function setUp() {
        parent::setUp();
        $this->markTestSkipped('Test is deprecated after M1 & M2 merge is done.');
        $mConvert = $this->_mock(\Praxigento\Core\Lib\Tool\Convert::class);
        $mDate = $this->_mock(\Praxigento\Core\Tool\IDate::class);
        $mFormat = $this->_mock(\Praxigento\Core\Tool\IFormat::class);
        $mPeriod = $this->_mock(\Praxigento\Core\Tool\IPeriod::class);
        $this->mTree = $this->_mock(\Praxigento\Downline\Tool\ITree::class);
        $this->obj = new Box(
            $mConvert,
            $mDate,
            $mFormat,
            $mPeriod,
            $this->mTree
        );
    }

    public function test_getDownlineTree() {
        /** === Test Data === */
        /** === Setup Mocks === */
        /** === Call and asserts  === */
        $resp = $this->obj->getDownlineTree();
        $this->assertTrue($resp instanceof \Praxigento\Downline\Tool\ITree);
    }
}