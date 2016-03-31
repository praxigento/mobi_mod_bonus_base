<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Type\Calc;


class Call extends \Praxigento\Core\Lib\Service\Type\Base\Call implements \Praxigento\Bonus\Base\Lib\Service\ITypeCalc {

    protected function _getResponse() {
        return new Response\GetByCode();
    }

    protected function _getEntityName() {
        return \Praxigento\Bonus\Base\Lib\Entity\Type\Calc::ENTITY_NAME;
    }

}