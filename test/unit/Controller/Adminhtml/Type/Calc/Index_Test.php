<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml\Type\Calc;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Index_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Controller
{

    /** @var Index */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        $this->obj = new Index(
            $this->mContext
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(Index::class, $this->obj);
    }

}