<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Type\Def;

use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as Entity;
use Praxigento\BonusBase\Repo\Entity\Type\ICalc;
use Praxigento\Core\Repo\Def\Type as BaseType;

class Calc extends BaseType implements ICalc
{

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }
}