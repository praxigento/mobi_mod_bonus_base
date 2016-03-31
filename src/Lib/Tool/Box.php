<?php
/**
 * Toolbox to get base implementation of tools from \Praxigento\Core\Lib\Tool package.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Tool;

use Praxigento\Core\Lib\Tool\Convert;
use Praxigento\Core\Lib\Tool\Date;
use Praxigento\Core\Lib\Tool\Format;
use Praxigento\Core\Lib\Tool\Period;
use Praxigento\Downline\Lib\Tool\ITree;

/**
 * @deprecated we should depend from concrete tools, not toolboxes.
 */
class Box
    extends \Praxigento\Core\Lib\Tool\Box
    implements \Praxigento\Core\Lib\IToolbox {
    /** @var ITree */
    private $_tree;

    public function __construct(
        Convert $convert,
        Date $date,
        Format $format,
        Period $period,
        ITree $tree
    ) {
        parent::__construct($convert, $date, $format, $period);
        $this->_tree = $tree;
    }

    /**
     * @return ITree
     */
    public function getDownlineTree() {
        return $this->_tree;
    }

}