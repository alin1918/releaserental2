<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Returns;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory;

/**
 * Class MassSend
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Send
 */
class MassReturn extends Action
{
    /**
     * Injected Dependency Description
     *
     * @var \SalesIgniter\Rental\Model\ReturnProcessor
     */
    protected $rentalModelReturnProcessor;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var array|\SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /** @noinspection PhpHierarchyChecksInspection */
    /** @noinspection MoreThanThreeArgumentsInspection
     * @param \Magento\Backend\App\Action\Context                                          $context
     * @param \Magento\Ui\Component\MassAction\Filter                                      $filter
     * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Model\Order\ShipmentFactory                                   $shipmentFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                 $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface                $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface                            $stockManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                  $orderRepository
     * @param \SalesIgniter\Rental\Model\ReturnProcessor                                   $rentalModelReturnProcessor
     *
     * @internal param $ \Magento\Backend\App\Action\Context|\SalesIgniter\Rental\Controller\Adminhtml\\Context $context
     * @internal param $ \Magento\Ui\Component\MassAction\Filter|\SalesIgniter\Rental\Controller\Adminhtml\\Filter $filter
     * @internal param $ \SalesIgniter\Rental\Controller\Adminhtml\\CollectionFactory|\SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory
     * @internal param \SalesIgniter\Rental\Model\ReturnProcessor $rentalModelShipmentProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \SalesIgniter\Rental\Model\ReturnProcessor $rentalModelReturnProcessor
    ) {
        $this->rentalModelReturnProcessor = $rentalModelReturnProcessor;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentFactory = $shipmentFactory;
        parent::__construct($context);
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockManagement = $stockManagement;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            /** @var array $qtyActions */
            $qtyActions = [];

            if ($this->getRequest()->getParam('qty_actions')) {
                $qtyActions = $this->getRequest()->getParam('qty_actions');
            }
            return $this->massReturn($collection->getAllIds(), $qtyActions);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Send selected reservations
     *
     *
     *
     * @param array $reservationIdsArray
     * @param array $qtyActions
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    public function massReturn($reservationIdsArray, $qtyActions)
    {
        /** @var int $totalItems */
        $totalItems = 0;
        $orderItemsReturned = [];
        foreach ($reservationIdsArray as $reservationOrderId) {
            $reservationOrder = $this->reservationOrdersRepository->getById($reservationOrderId);
            $reservationQtyToReturn = 0;
            $reservationSerialsToReturn = '';
            $this->getQtyAndSerials($qtyActions, $reservationOrder, $reservationQtyToReturn, $reservationSerialsToReturn);
            if ($reservationQtyToReturn === 0) {
                break;
            }

            $totalItemsReturned = $this->stockManagement->returnReservationQty(
                $reservationOrder,
                $reservationQtyToReturn,
                $reservationSerialsToReturn
            );
            if ($totalItemsReturned === false) {
                $this->messageManager->addSuccessMessage(__('Some serials appeared to not match reservation. Please check again'));
                $totalItemsReturned = 0;
            } else {
                $orderItemsReturned[$reservationOrder->getOrderId()][$reservationOrder->getOrderItemId()] = $totalItemsReturned;
            }
            $totalItems += $totalItemsReturned;
        }
        //Send Return Email
        foreach ($orderItemsReturned as $orderId => $orderItemsArray) {
            $this->rentalModelReturnProcessor->sendReturnConfirmation($orderId, $orderItemsArray);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 item(s) have been returned.', $totalItems));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $qtyActions
     * @param       $reservationOrder
     * @param       $reservationQtyToReturn
     * @param       $reservationSerialsToReturn
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getQtyAndSerials($qtyActions, $reservationOrder, &$reservationQtyToReturn, &$reservationSerialsToReturn)
    {
        if (array_key_exists('qty_returned', $qtyActions) && array_key_exists($reservationOrder->getId(), $qtyActions['qty_returned'])) {
            $reservationQtyToReturn = $qtyActions['qty_returned'][$reservationOrder->getId()];
        }

        if (array_key_exists('serials_returned', $qtyActions) && array_key_exists($reservationOrder->getId(), $qtyActions['serials_returned'])) {
            $reservationSerialsToReturn = $qtyActions['serials_returned'][$reservationOrder->getId()];
            $this->rentalModelReturnProcessor->updateQtyAndSerialsBasedOnInput($reservationOrder, $reservationQtyToReturn, $reservationSerialsToReturn);
        }
    }
}
