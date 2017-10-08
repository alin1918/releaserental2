<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param Action\Context                                                $context
     * @param PostDataProcessor                                             $dataProcessor
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param DataPersistorInterface                                        $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        StockManagementInterface $stockManagement,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
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
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (empty($data['start_date']) || $data['start_date'] == 'Invalid date') {
                $data['start_date'] = '0000-00-00 00:00:00';
            }
            if (empty($data['end_date']) || $data['end_date'] == 'Invalid date') {
                $data['end_date'] = '0000-00-00 00:00:00';
            }

            $data = $this->dataProcessor->filter($data);

            $idReservation = $this->getRequest()->getParam('reservationorder_id');
            if ($idReservation) {
                $reservation = $this->reservationOrdersRepository->getById($idReservation);
            }

            if (!$this->dataProcessor->validate($data)) {
                $this->messageManager->addErrorMessage(__('Start Date cannot be bigger than End Date.'));
                if ($idReservation) {
                    return $resultRedirect->setPath('*/*/edit', ['reservationorder_id' => $reservation->getId(), '_current' => true]);
                } else {
                    return $resultRedirect->setPath('*/*/');
                }
            }

            try {
                $data['not_check_valid'] = true;
                if ($idReservation) {
                    $this->stockManagement->saveReservation($reservation, $data);
                } else {
                    $this->stockManagement->saveFromArray($data);
                }
                $this->messageManager->addSuccessMessage(__('You saved the reservation.'));
                $this->dataPersistor->clear('sirent_reservation');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['reservationorder_id' => $reservation->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            $this->dataPersistor->set('sirent_reservation', $data);
            return $resultRedirect->setPath('*/*/edit', ['reservationorder_id' => $this->getRequest()->getParam('reservationorder_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
