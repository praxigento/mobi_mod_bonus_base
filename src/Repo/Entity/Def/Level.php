<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Def;

use Praxigento\BonusBase\Data\Entity\Level as Entity;

class Level
    extends \Praxigento\Core\Repo\Def\Entity
    implements \Praxigento\BonusBase\Repo\Entity\ILevel
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }

}