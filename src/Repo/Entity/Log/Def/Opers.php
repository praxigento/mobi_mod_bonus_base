<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity\Log\Def;

use Praxigento\BonusBase\Data\Entity\Log\Opers as Entity;

class Opers
    extends \Praxigento\Core\Repo\Def\Entity
    implements \Praxigento\BonusBase\Repo\Entity\Log\IOpers
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct($resource, $repoGeneric, Entity::class);
    }

}