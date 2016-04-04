<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity\Type\Def;

use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as EntityCalc;
use Praxigento\BonusBase\Repo\Entity\Type\ICalc;
use Praxigento\Core\Repo\Def\Type as BaseType;

class Calc extends BaseType implements ICalc
{

    protected function _getEntityName()
    {
        return EntityCalc::ENTITY_NAME;
    }
}