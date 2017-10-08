<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Report;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Inventory extends BackendAction
{

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SalesIgniter_Rental::inventory');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $ResultPage = $this->_resultPageFactory->create();
        $ResultPage->setActiveMenu('SalesIgniter_Rental::reports');
        $ResultPage->addBreadcrumb(__('Rental'), __('Rental'));
        $ResultPage->addBreadcrumb(__('Reports'), __('Reports'));
        $ResultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $ResultPage->getConfig()->getTitle()->prepend(__('Rental > Reports > Inventory'));

        return $ResultPage;
    }
}
