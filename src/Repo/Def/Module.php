<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Def;

use Praxigento\BonusBase\Repo\IModule;
use Praxigento\Core\Repo\Def\Db;

class Module extends Db implements IModule
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var \Praxigento\Core\Repo\IGeneric */
    protected $_repoBasic;
    /** @var  \Praxigento\Core\Transaction\Database\IManager */
    protected $_manTrans;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Transaction\Database\IManager $manTrans,
        \Praxigento\Core\Repo\IGeneric $repoBasic,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        parent::__construct($resource);
        $this->_manTrans = $manTrans;
        $this->_repoBasic = $repoBasic;
        $this->_toolDate = $toolDate;
    }

}