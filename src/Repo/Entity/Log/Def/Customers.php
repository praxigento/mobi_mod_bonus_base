<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Log\Def;

use Praxigento\BonusBase\Data\Entity\Log\Customers as Entity;

class Customers
    extends \Praxigento\Core\Repo\Def\Entity
    implements \Praxigento\BonusBase\Repo\Entity\Log\ICustomers
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }

}