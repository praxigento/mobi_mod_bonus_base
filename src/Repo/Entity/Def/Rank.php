<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

class Rank
    extends \Praxigento\Core\Repo\Entity\Def\Type
    implements \Praxigento\BonusBase\Repo\Entity\IRank
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\Bonus\Base\Lib\Entity\Rank::class
        );
    }
}