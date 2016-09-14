<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\Warehouse\Data\Entity\Customer as Entity;

class Calculation
    extends \Praxigento\Core\Repo\Def\Entity
    implements \Praxigento\BonusBase\Repo\Entity\ICalculation
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }

}