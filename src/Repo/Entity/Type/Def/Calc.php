<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Type\Def;

class Calc
    extends \Praxigento\Core\Repo\Entity\Def\Type
    implements \Praxigento\BonusBase\Repo\Entity\Type\ICalc
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Data\Entity\Type\Calc::class
        );
    }
}