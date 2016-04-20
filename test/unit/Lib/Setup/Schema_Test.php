<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Setup;

use Praxigento\Bonus\Base\Lib\Entity\Calculation;
use Praxigento\Bonus\Base\Lib\Entity\Cfg\Generation as CfgGeneration;
use Praxigento\Bonus\Base\Lib\Entity\Compress;
use Praxigento\Bonus\Base\Lib\Entity\Level;
use Praxigento\Bonus\Base\Lib\Entity\Log\Customers as LogCustomers;
use Praxigento\Bonus\Base\Lib\Entity\Log\Opers as LogOpers;
use Praxigento\Bonus\Base\Lib\Entity\Log\Rank as LogRank;
use Praxigento\Bonus\Base\Lib\Entity\Log\Sales as LogSales;
use Praxigento\Bonus\Base\Lib\Entity\Period;
use Praxigento\Bonus\Base\Lib\Entity\Rank;
use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as TypeCalc;

include_once(__DIR__ . '/../../phpunit_bootstrap.php');

class Schema_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->markTestSkipped('Test is deprecated after M1 & M2 merge is done.');
    }

    public function test_install()
    {
        /** === Test Data === */
        /** === Mocks === */
        $mLogger = $this->_mockLogger();
        $mSetupDb = $this->_mockDemSetupDb();
        // $this->setupDb->createEntity($entityAlias, $demEntity);
        $mSetupDb
            ->expects($this->at(0))
            ->method('createEntity')
            ->with($this->equalTo(TypeCalc::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(1))
            ->method('createEntity')
            ->with($this->equalTo(Period::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(2))
            ->method('createEntity')
            ->with($this->equalTo(Calculation::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(3))
            ->method('createEntity')
            ->with($this->equalTo(Compress::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(4))
            ->method('createEntity')
            ->with($this->equalTo(Level::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(5))
            ->method('createEntity')
            ->with($this->equalTo(Rank::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(6))
            ->method('createEntity')
            ->with($this->equalTo(CfgGeneration::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(7))
            ->method('createEntity')
            ->with($this->equalTo(LogCustomers::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(8))
            ->method('createEntity')
            ->with($this->equalTo(LogOpers::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(9))
            ->method('createEntity')
            ->with($this->equalTo(LogRank::ENTITY_NAME), $this->anything());
        $mSetupDb
            ->expects($this->at(10))
            ->method('createEntity')
            ->with($this->equalTo(LogSales::ENTITY_NAME), $this->anything());

        /**
         * Prepare request and perform call.
         */
        $obj = new Schema($mLogger, $mSetupDb);
        $obj->setup();
    }
}