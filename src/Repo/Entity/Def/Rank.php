<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\Bonus\Base\Lib\Entity\Rank as Entity;
use Praxigento\BonusBase\Repo\Entity\IRank;
use Praxigento\Core\Repo\Def\Type as BaseType;

class Rank extends BaseType implements IRank
{

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }
}