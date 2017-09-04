<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Repo\Entity;

class Level
    extends \Praxigento\Core\Repo\Def\Entity
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\IGeneric $repoGeneric
    ) {
        parent::__construct(
            $resource,
            $repoGeneric,
            \Praxigento\BonusBase\Repo\Entity\Data\Level::class
        );
    }

    /**
     * Get array ([level=>percent]) of the levels for given calculation type.
     * This structure is used in bonus calculations.
     *
     * @param int $calcTypeId
     * @return array [level=>percent] ordered asc by level.
     */
    public function getByCalcTypeId($calcTypeId)
    {
        $result = [];
        $where = \Praxigento\BonusBase\Repo\Entity\Data\Level::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $order = \Praxigento\BonusBase\Repo\Entity\Data\Level::ATTR_LEVEL . ' ASC';
        $rs = $this->get($where, $order);
        /** @var \Praxigento\BonusBase\Repo\Entity\Data\Level $one */
        foreach ($rs as $one) {
            $level = $one->getLevel();
            $percent = $one->getPercent();
            $result[$level] = $percent;
        }
        return $result;
    }
}