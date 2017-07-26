<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Log;

use Praxigento\BonusBase\Data\Entity\Log\Opers as Entity;


include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class Opers_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo\Entity
{
    /** @var  Opers */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new Opers(
            $this->mResource,
            $this->mRepoGeneric,
            Entity::class
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(\Praxigento\BonusBase\Repo\Entity\Log\Opers::class, $this->obj);
    }
}