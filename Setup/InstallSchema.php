<?php
/**
 * Create DB schema.
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Setup;

use Praxigento\BonusBase\Repo\Data\Calculation;
use Praxigento\BonusBase\Repo\Data\Cfg\Generation as CfgGeneration;
use Praxigento\BonusBase\Repo\Data\Compress;
use Praxigento\BonusBase\Repo\Data\Level;
use Praxigento\BonusBase\Repo\Data\Log\Customers as LogCustomers;
use Praxigento\BonusBase\Repo\Data\Log\Opers as LogOpers;
use Praxigento\BonusBase\Repo\Data\Log\Rank as LogRank;
use Praxigento\BonusBase\Repo\Data\Log\Sales as LogSales;
use Praxigento\BonusBase\Repo\Data\Period;
use Praxigento\BonusBase\Repo\Data\Rank;
use Praxigento\BonusBase\Repo\Data\Type\Calc as TypeCalc;

class InstallSchema extends \Praxigento\Core\App\Setup\Schema\Base
{

    protected function setup()
    {
        /** Read and parse JSON schema. */
        $pathToFile = __DIR__ . '/../etc/dem.json';
        $pathToNode = '/dBEAR/package/Praxigento/package/Bonus/package/Base';
        $demPackage = $this->toolDem->readDemPackage($pathToFile, $pathToNode);

        /* Type Calculation */
        $demEntity = $demPackage->get('package/Type/entity/Calculation');
        $this->toolDem->createEntity(TypeCalc::ENTITY_NAME, $demEntity);

        /* Period */
        $demEntity = $demPackage->get('entity/Period');
        $this->toolDem->createEntity(Period::ENTITY_NAME, $demEntity);

        /* Calculation */
        $demEntity = $demPackage->get('entity/Calculation');
        $this->toolDem->createEntity(Calculation::ENTITY_NAME, $demEntity);

        /* Compression */
        $demEntity = $demPackage->get('entity/Compression');
        $this->toolDem->createEntity(Compress::ENTITY_NAME, $demEntity);

        /* Level */
        $demEntity = $demPackage->get('entity/Level');
        $this->toolDem->createEntity(Level::ENTITY_NAME, $demEntity);

        /* Rank */
        $demEntity = $demPackage->get('entity/Rank');
        $this->toolDem->createEntity(Rank::ENTITY_NAME, $demEntity);

        /* Cfg Generation */
        $demEntity = $demPackage->get('package/Config/entity/Generation');
        $this->toolDem->createEntity(CfgGeneration::ENTITY_NAME, $demEntity);

        /* Log LogCustomers */
        $demEntity = $demPackage->get('package/Log/entity/Customer');
        $this->toolDem->createEntity(LogCustomers::ENTITY_NAME, $demEntity);

        /* Log Operations */
        $demEntity = $demPackage->get('package/Log/entity/Operation');
        $this->toolDem->createEntity(LogOpers::ENTITY_NAME, $demEntity);

        /* Log Rank */
        $demEntity = $demPackage->get('package/Log/entity/Rank');
        $this->toolDem->createEntity(LogRank::ENTITY_NAME, $demEntity);

        /* Log Sales*/
        $demEntity = $demPackage->get('package/Log/entity/SaleOrder');
        $this->toolDem->createEntity(LogSales::ENTITY_NAME, $demEntity);
    }
}