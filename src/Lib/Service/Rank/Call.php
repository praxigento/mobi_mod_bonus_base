<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Bonus\Base\Lib\Service\Rank;


class Call extends \Praxigento\Core\Lib\Service\Type\Base\Call
    implements \Praxigento\Bonus\Base\Lib\Service\IRank {

    protected function _getEntityName() {
        return \Praxigento\Bonus\Base\Lib\Entity\Rank::ENTITY_NAME;
    }

    protected function _getResponse() {
        return new Response\GetByCode();
    }

}