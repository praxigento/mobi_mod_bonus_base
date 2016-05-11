<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\Bonus\Base\Lib\Entity\Rank as Entity;
use Praxigento\BonusBase\Repo\Entity\IRank;


include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Rank_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase
{
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mRepoGeneric;
    /** @var  Rank */
    private $obj;

    public function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mConn = $this->_mockConn();
        $this->mRepoGeneric = $this->_mockRepoGeneric();
        /* create object */
        $mResource = $this->_mockResourceConnection($this->mConn);
        $this->obj = new Rank(
            $mResource,
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