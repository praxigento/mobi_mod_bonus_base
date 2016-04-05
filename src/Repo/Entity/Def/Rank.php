<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\Bonus\Base\Lib\Entity\Rank as EntityRank;
use Praxigento\BonusBase\Repo\Entity\IRank;
use Praxigento\Core\Repo\Def\Type as BaseType;

class Rank extends BaseType implements IRank
{

    protected function _getEntityName()
    {
        return EntityRank::ENTITY_NAME;
    }
}