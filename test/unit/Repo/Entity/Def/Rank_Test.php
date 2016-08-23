<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\Bonus\Base\Lib\Entity\Rank as Entity;
use Praxigento\BonusBase\Repo\Entity\IRank;


include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Rank_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo
{
    /** @var  Rank */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new Rank(
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(IRank::class, $this->obj);
    }
}