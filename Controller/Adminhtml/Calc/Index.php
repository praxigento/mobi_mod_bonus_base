<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml\Calc;

use Praxigento\BonusBase\Config as Cfg;

class Index
    extends \Praxigento\BonusBase\Controller\Adminhtml\Base
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        $aclResource = Cfg::MODULE . '::' . Cfg::ACL_BONUS_CALC;
        $activeMenu = Cfg::MODULE . '::' . Cfg::MENU_BONUS_CALC;
        $breadcrumbLabel = 'Bonus Calcs';
        $breadcrumbTitle = 'Bonus Calcs';
        $pageTitle = 'Bonus Calcs';
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