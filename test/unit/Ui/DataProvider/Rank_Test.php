<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Ui\DataProvider;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Rank_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Ui\DataProvider
{
    /** @var  \Mockery\MockInterface */
    private $mRepo;
    /** @var  Rank */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mRepo = $this->_mock(\Praxigento\BonusBase\Repo\Entity\IRank::class);
        /** create object to test */
        $this->obj = new Rank(
            $this->mUrl,
            $this->mCritAdapter,
            $this->mRepo,
            $this->mReporting,
            $this->mSearchCritBuilder,
            $this->mRequest,
            $this->mFilterBuilder,
            'name'
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(Rank::class, $this->obj);
    }

}