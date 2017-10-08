<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

class EditOrder extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context                                                       $context
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface                   $orderRepository
     * @param PageFactory                                                   $resultPageFactory
     *
     */
    public function __construct(
        Context $context,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        OrderRepositoryInterface $orderRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SalesIgniter_Rental::manualedit');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SalesIgniter_Rental::rental_reservations');
        $resultPage->addBreadcrumb(__('Rental Reservations'), __('Rental Reservations'));
        return $resultPage;
    }

    /**
     * Edit CMS page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $idOrder = $this->getRequest()->getParam('id');
        if ($idOrder) {
            $order = $this->orderRepository->get($idOrder);

            if (!$order->getId()) {
                $this->messageManager->addErrorMessage(__('This order no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        //$this->_coreRegistry->register('cms_page', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $idOrder ? __('Edit Order Reservation') : __('New Order Reservation'),
            $idOrder ? __('Edit Order Reservation') : __('New Order Reservation')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Reservation'));
        $resultPage->getConfig()->getTitle()
            ->prepend($idOrder ? __('Edited') . $order->getIncrementId() : __('New Order Reservation'));

        return $resultPage;
    }
}
