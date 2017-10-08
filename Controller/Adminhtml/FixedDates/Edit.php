<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\FixedDates;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface
     */
    private $fixedRentalNamesRepository;

    /**
     * @param Context                                                      $context
     * @param \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
     * @param PageFactory                                                  $resultPageFactory
     */
    public function __construct(
        Context $context,
        FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;

        $this->fixedRentalNamesRepository = $fixedRentalNamesRepository;
    }

    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SalesIgniter_Rental::fixeddates');
    }

    /**
     * Init actions.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SalesIgniter_Rental::rental_reservations');
        $resultPage->addBreadcrumb(__('Predetermined Dates'), __('Predetermined Dates'));

        return $resultPage;
    }

    /**
     * Edit CMS page.
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $idName = $this->getRequest()->getParam('id');
        if ($idName) {
            $fixedName = $this->fixedRentalNamesRepository->getById($idName);

            if (!$fixedName->getId()) {
                $this->messageManager->addErrorMessage(__('This predetermined date no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        //$this->_coreRegistry->register('cms_page', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $idName ? __('Edit Predetermined Date') : __('New Predetermined Date'),
            $idName ? __('Edit Predetermined Date') : __('New Predetermined Date')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Predetermined Date'));
        $resultPage->getConfig()->getTitle()
            ->prepend($idName ? __('Edited').$idName : __('New Predetermined Date'));

        return $resultPage;
    }
}
