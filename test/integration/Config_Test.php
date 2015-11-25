<?php
/**
 * Empty class to get stub for tests
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base;
include_once(__DIR__ . '/phpunit_bootstrap.php');

class Config_UnitTest extends \PHPUnit_Framework_TestCase {

    public function test_lib() {
        $ctx = \Praxigento\Bonus\Base\Lib\Context::instance();
        $this->assertTrue($ctx instanceof \Praxigento\Core\Lib\Context);
    }

}