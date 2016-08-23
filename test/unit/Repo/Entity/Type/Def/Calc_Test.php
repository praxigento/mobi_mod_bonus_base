<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Type\Def;

use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as Entity;
use Praxigento\BonusBase\Repo\Entity\Type\ICalc;


include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class Rank_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo\Entity
{
    /** @var  Calc */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new Calc(
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(ICalc::class, $this->obj);
    }
}