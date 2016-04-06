<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Repo\Def;

use Praxigento\Bonus\Base\Lib\Repo\IModule;


include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Main_ManualTest extends \Praxigento\Core\Lib\Test\BaseTestCase {

    public function test_construct() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $repo \Praxigento\Bonus\Base\Lib\Repo\IModule */
        $repo = $obm->get(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $this->assertTrue($repo instanceof \Praxigento\Bonus\Base\Lib\Repo\Def\Module);
    }

    public function test_addLogSaleOrder() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $dba \Praxigento\Core\Lib\Context\IDbAdapter */
        $dba = $obm->get(\Praxigento\Core\Lib\Context\IDbAdapter::class);
        $dba->getDefaultConnection()->beginTransaction();

        /** @var  $repo \Praxigento\Bonus\Base\Lib\Repo\IModule */
        $repo = $obm->get(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $data = $repo->addLogSaleOrder(1, 2);
        $dba->getDefaultConnection()->rollback();
    }

    public function test_getCalcsForPeriod() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $repo \Praxigento\Bonus\Base\Lib\Repo\IModule */
        $repo = $obm->get(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $data = $repo->getCalcsForPeriod(16, '20160101', '20160131', true);
        $this->assertTrue(is_array($data));
    }

    public function test_getFirstDateForPvTransactions() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $repo \Praxigento\Bonus\Base\Lib\Repo\IModule */
        $repo = $obm->get(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $firstDate = $repo->getFirstDateForPvTransactions();
        $this->assertTrue(is_string($firstDate));
    }

    public function test_getLatest() {
        $obm = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $repo \Praxigento\Bonus\Base\Lib\Repo\IModule */
        $repo = $obm->get(\Praxigento\Bonus\Base\Lib\Repo\IModule::class);
        $data = $repo->getLatestPeriod(16);
        $this->assertTrue(is_array($data->getData(IModule::A_PERIOD)));
        $this->assertTrue(is_array($data->getData(IModule::A_CALC)));
    }
}