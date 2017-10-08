<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

/**
 * Reservation inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var JsonFactory */
    protected $jsonFactory;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;

    /**
     * @param Context                                                       $context
     * @param PostDataProcessor                                             $dataProcessor
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param JsonFactory                                                   $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->jsonFactory = $jsonFactory;
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (count($postItems) && !$this->getRequest()->getParam('isAjax')) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $reservationId) {
            $reservation = $this->reservationOrdersRepository->getById($reservationId);
            try {
                $data = $this->filterPost($postItems[$reservationId]);

                unset($data['start_date_with_turnover']);
                unset($data['end_date_with_turnover']);
                unset($data['start_date_use_grid']);
                unset($data['end_date_use_grid']);
                unset($data['qty_use_grid']);

                $idReservation = $data['reservationorder_id'];
                if ($idReservation) {
                    $reservation = $this->reservationOrdersRepository->getById($idReservation);
                }

                if (!$this->validatePost($data, $reservation, $messages)) {
                    $error = true;
                } else {
                    $this->stockManagement->saveReservation($reservation, $data);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithReservationId($reservation, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithReservationId($reservation, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithReservationId(
                    $reservation,
                    __('Something went wrong while saving the reservation.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error,
        ]);
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     *
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $reservationData = $this->dataProcessor->filter($postData);
        return $reservationData;
    }

    /**
     * Validate post data
     *
     * @param array $reservationData
     * @param       $reservation
     * @param bool  $error
     * @param array $messages
     *
     * @return void
     */
    protected function validatePost(array $reservationData, $reservation, array &$messages)
    {
        $error = false;
        if (!($this->dataProcessor->validate($reservationData) && $this->dataProcessor->validateRequireEntry($reservationData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithReservationId($reservation, $error->getText());
            }
        }
        return $error;
    }

    /**
     * Add page title to error message
     *
     * @param        $reservation
     * @param string $errorText
     *
     * @return string
     */
    protected function getErrorWithReservationId($reservation, $errorText)
    {
        return '[Reservation ID: ' . $reservation->getId() . '] ' . $errorText;
    }
}
