<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Query\Period\Calcs;


include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

class Builder_ManualTest
    extends \Praxigento\Core\Test\BaseCase\Mockery
{


    public function test_getSelectQuery()
    {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $obj \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
        $obj = $obm->get(\Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder::class);
        $res = $obj->getSelectQuery();
        $this->assertTrue($res instanceof \Magento\Framework\DB\Select);
    }

}