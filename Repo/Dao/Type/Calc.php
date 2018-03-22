<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao\Type;

class Calc
    extends \Praxigento\Core\App\Repo\Entity\Type
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Repo\Data\Type\Calc::class
        );
    }
}