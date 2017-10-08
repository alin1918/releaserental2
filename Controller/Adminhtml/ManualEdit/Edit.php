<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

class Edit extends \Magento\Backend\App\Action
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
     * @param Context                                                       $context
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param PageFactory                                                   $resultPageFactory
     */
    public function __construct(
        Context $context,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
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
        $idReservation = $this->getRequest()->getParam('id');
        if ($idReservation) {
            $reservation = $this->reservationOrdersRepository->getById($idReservation);

            if (!$reservation->getId()) {
                $this->messageManager->addErrorMessage(__('This reservation no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        //$this->_coreRegistry->register('cms_page', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $idReservation ? __('Edit Reservation') : __('New Reservation'),
            $idReservation ? __('Edit Reservation') : __('New Reservation')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Reservation'));
        $resultPage->getConfig()->getTitle()
            ->prepend($idReservation ? __('Edited') . $idReservation : __('New Reservation'));

        return $resultPage;
    }
}
