<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\BonusBase\Data\Entity\Compress as Entity;


include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Compress_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo\Entity
{
    /** @var  Compress */
    private $obj;
    /** @var array Constructor arguments for object mocking */
    private $objArgs = [];

    public function setUp()
    {
        parent::setUp();
        /** reset args. to create mock of the tested object */
        $this->objArgs = [
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        ];
        /** create object to test */
        $this->obj = new Compress(
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(\Praxigento\BonusBase\Repo\Entity\ICompress::class, $this->obj);
    }

    public function test_getTreeByCalcId()
    {
        /** === Test Data === */
        $CALC_ID = 2;
        $RESULT = [];
        /** === Mock object itself === */
        $this->obj = \Mockery::mock(Compress::class . '[get]', $this->objArgs);
        /** === Setup Mocks === */
        // $result = $this->get($where);
        $this->obj
            ->shouldReceive('get')->once()
            ->andReturn($RESULT);
        /** === Call and asserts  === */
        $res = $this->obj->getTreeByCalcId($CALC_ID);

    }
}