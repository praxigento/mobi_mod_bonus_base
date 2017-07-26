<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity;

class Rank
    extends \Praxigento\Core\Repo\Entity\Def\Type
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Data\Entity\Rank::class
        );
    }
}