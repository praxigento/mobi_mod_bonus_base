<?php
namespace Praxigento\Bonus\Base\Lib\Entity\Type;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Calc extends \Praxigento\Core\Data\Entity\Type\Base
{
    const ENTITY_NAME = 'prxgt_bon_base_type_calc';

    public function getEntityName()
    {
        return self::ENTITY_NAME;
    }
}