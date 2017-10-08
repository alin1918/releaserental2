<?php
namespace SalesIgniter\Rental\Controller\Adminhtml\Calendar;

use Magento\Framework\View\Result\PageFactory,
	Magento\Backend\App\Action as BackendAction,
	Magento\Backend\App\Action\Context;

class Index
	extends BackendAction
{

	/**
	 * @var PageFactory
	 */
	protected $_resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory
	)
	{
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
        return $this->_authorization->isAllowed('SalesIgniter_Rental::rentalcal');
    }

	/**
	 * Index action
	 *
	 * @return \Magento\Backend\Model\View\Result\Page
	 */
	public function execute()
	{
		$ResultPage = $this->_resultPageFactory->create();
		$ResultPage->setActiveMenu('SalesIgniter_Rental::calendar');
		$ResultPage->addBreadcrumb(__('Rental'), __('Rental'));
		$ResultPage->addBreadcrumb(__('Reports'), __('Calendar'));
		$ResultPage->getConfig()->getTitle()->prepend(__('Rental > Calendar'));

		return $ResultPage;
	}
}