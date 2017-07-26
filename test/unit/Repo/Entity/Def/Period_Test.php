<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity;

use Praxigento\BonusBase\Data\Entity\Period as Entity;


include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Period_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo\Entity
{
    /** @var  Period */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new Period(
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(\Praxigento\BonusBase\Repo\Entity\Period::class, $this->obj);
    }
}