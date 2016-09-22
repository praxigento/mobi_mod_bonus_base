<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Entity;

interface ICompress
    extends \Praxigento\Core\Repo\IEntity
{
    /**
     * Get compressed tree for given calculation.
     *
     * @param $calcId
     * @return array raw data from DB
     */
    public function getTreeByCalcId($calcId);
}