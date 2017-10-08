<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

use Magento\Backend\App\Action\Context;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param Context                                                       $context
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     */
    public function __construct(
        Context $context,
        StockManagementInterface $stockManagement,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository
    ) {
        parent::__construct($context);
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->stockManagement = $stockManagement;
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
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $idReservation = $this->getRequest()->getParam('reservationorder_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($idReservation) {
            try {
                $this->stockManagement->deleteReservationById($idReservation);
                // display success message
                $this->messageManager->addSuccessMessage(__('The reservation has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['reservationorder_id' => $idReservation]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a reservation to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
