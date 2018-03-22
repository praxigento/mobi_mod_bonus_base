<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Dao;

class Period
    extends \Praxigento\Core\App\Repo\Def\Entity
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Repo\Data\Period::class);
    }

}