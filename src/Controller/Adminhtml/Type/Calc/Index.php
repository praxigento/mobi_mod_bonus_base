<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml\Type\Calc;

use Praxigento\BonusBase\Config as Cfg;

class Index
    extends \Praxigento\BonusBase\Controller\Adminhtml\Base
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $aclResource = Cfg::MODULE . '::' . Cfg::ACL_BONUS_TYPE_CALC;
        $activeMenu = Cfg::MODULE . '::' . Cfg::MENU_BONUS_TYPE_CALC;
        $breadcrumbLabel = 'Bonus Calc. Types';
        $breadcrumbTitle = 'Bonus Calc. Types';
        $pageTitle = 'Bonus Calc Types';
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