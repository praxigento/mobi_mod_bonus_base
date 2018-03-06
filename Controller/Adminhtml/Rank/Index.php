<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml\Rank;

use Praxigento\BonusBase\Config as Cfg;

class Index
    extends \Praxigento\BonusBase\Controller\Adminhtml\Base
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        $aclResource = Cfg::MODULE . '::' . Cfg::ACL_BONUS_RANK;
        $activeMenu = Cfg::MODULE . '::' . Cfg::MENU_BONUS_RANK;
        $breadcrumbLabel = 'Bonus Ranks';
        $breadcrumbTitle = 'Bonus Ranks';
        $pageTitle = 'Bonus Ranks';
        parent::__construct(
            $context,
            $aclResource,
            $activeMenu,
            $breadcrumbLabel,
            $breadcrumbTitle,
            $pageTitle
        );
    }
}