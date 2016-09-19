<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml\Period;

use Praxigento\BonusBase\Config as Cfg;

class Index
    extends \Praxigento\BonusBase\Controller\Adminhtml\Base
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $aclResource = Cfg::MODULE . '::' . Cfg::ACL_BONUS_PERIOD;
        $activeMenu = Cfg::MODULE . '::' . Cfg::MENU_BONUS_PERIOD;
        $breadcrumbLabel = 'Bonus Periods';
        $breadcrumbTitle = 'Bonus Periods';
        $pageTitle = 'Bonus Periods';
        parent::__construct(
            $context,
            $resultPageFactory,
            $aclResource,
            $activeMenu,
            $breadcrumbLabel,
            $breadcrumbTitle,
            $pageTitle
        );
    }
}