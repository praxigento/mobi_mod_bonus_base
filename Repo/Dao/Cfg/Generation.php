<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao\Cfg;

use Praxigento\BonusBase\Repo\Data\Cfg\Generation as Entity;

class Generation
    extends \Praxigento\Core\App\Repo\Def\Entity
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $daoGeneric
    ) {
        parent::__construct($resource, $daoGeneric, Entity::class);
    }

}