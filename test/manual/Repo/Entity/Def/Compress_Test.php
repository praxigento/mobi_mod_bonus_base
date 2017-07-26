<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity;

use Magento\Framework\App\ObjectManager;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Compress_ManualTest extends \Praxigento\Core\Test\BaseCase\Mockery
{

    /** @var  Compress */
    private $_obj;

    public function setUp()
    {
        parent::setUp();
        $this->_obj = ObjectManager::getInstance()->create(\Praxigento\BonusBase\Repo\Entity\Compress::class);
    }

    public function test_getTreeByCalcId()
    {
        $CALC_ID = 2;
        $res = $this->_obj->getTreeByCalcId($CALC_ID);
        $this->assertTrue(is_array($res));
    }


}