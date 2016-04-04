<?php
namespace Praxigento\Bonus\Base\Lib\Entity;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Rank extends \Praxigento\Core\Data\Entity\Type\Base
{
    const ENTITY_NAME = 'prxgt_bon_base_rank';

    public function getEntityName()
    {
        return self::ENTITY_NAME;
    }
}