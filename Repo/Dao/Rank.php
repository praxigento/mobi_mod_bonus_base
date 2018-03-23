<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao;

class Rank
    extends \Praxigento\Core\App\Repo\Entity\Type
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $daoGeneric
    ) {
        parent::__construct(
            $resource,
            $daoGeneric,
            \Praxigento\BonusBase\Repo\Data\Rank::class
        );
    }
}