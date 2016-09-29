<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Controller\Adminhtml;

/**
 * Base class for bonus controllers.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
abstract class Base
    extends \Magento\Backend\App\Action
{
    /** @var  string */
    protected $_aclResource;
    /** @var  string */
    protected $_activeMenu;
    /** @var  string */
    protected $_breadcrumbLabel;
    /** @var  string */
    protected $_breadcrumbTitle;
    /** @var  string */
    protected $_pageTitle;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        $aclResource,
        $activeMenu,
        $breadcrumbLabel,
        $breadcrumbTitle,
        $pageTitle
    ) {
        parent::__construct($context);
        $this->_aclResource = $aclResource;
        $this->_activeMenu = $activeMenu;
        $this->_breadcrumbLabel = $breadcrumbLabel;
        $this->_breadcrumbTitle = $breadcrumbTitle;
        $this->_pageTitle = $pageTitle;
    }

    /**
     * Check user's access rights to the controller.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $result = parent::_isAllowed();
        $result = $result && $this->_authorization->isAllowed($this->_aclResource);
        return $result;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu($this->_activeMenu);
        $this->_addBreadcrumb(__($this->_breadcrumbLabel), __($this->_breadcrumbTitle));
        $resultPage->getConfig()->getTitle()->prepend(__($this->_pageTitle));
        return $resultPage;
    }
}