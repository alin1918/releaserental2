<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Send;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Ui\Component\MassAction\Filter;
use SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory;

/**
 * Class MassSend
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Send
 */
class MassSend extends Action
{
    /**
     * Injected Dependency Description
     *
     * @var \\SalesIgniter\Rental\Model\ShipmentProcessor
     */
    protected $rentalModelShipmentProcessor;

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
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    private $shipmentSender;
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
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender                       $shipmentSender
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface                $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface                            $stockManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                  $orderRepository
     * @param \SalesIgniter\Rental\Model\ShipmentProcessor                                 $rentalModelShipmentProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ShipmentSender $shipmentSender,
        \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \SalesIgniter\Rental\Model\ShipmentProcessor $rentalModelShipmentProcessor
    ) {
        $this->rentalModelShipmentProcessor = $rentalModelShipmentProcessor;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentFactory = $shipmentFactory;
        parent::__construct($context);
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentSender = $shipmentSender;
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

            return $this->massShip($collection->getAllIds(), $qtyActions);
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
     * @throws \Exception
     */
    public function massShip($reservationIdsArray, $qtyActions)
    {

        /** @var array $reservationsShipped */
        $reservationsShipped = [];

        /** @var array $reservationSerialsToShip */
        $reservationSerialsToShip = [];

        /** @var int $totalItems */
        $totalItems = 0;
        foreach ($reservationIdsArray as $reservationOrderId) {
            $reservationOrder = $this->reservationOrdersRepository->getById($reservationOrderId);
            $reservationQtyToShip = 0;
            $this->getQtyAndSerials($qtyActions, $reservationOrder, $reservationQtyToShip, $reservationSerialsToShip);
            if ($reservationQtyToShip === 0) {
                break;
            }
            $reservationsShipped = $this->getReservationsShipped($reservationQtyToShip, $reservationOrder, $reservationsShipped, $reservationSerialsToShip);
            $this->stockManagement->shipReservationQty($reservationOrder, $reservationQtyToShip, $reservationSerialsToShip);
        }

        try {
            $itemArray = [];
            foreach ($reservationsShipped as $orderItemId => $qtysArray) {
                $totalItems++;
                $itemArray[$qtysArray['order_id']][$orderItemId] = $qtysArray['qty'];
            }
            foreach ($itemArray as $orderId => $orderItemsArray) {
                $this->rentalModelShipmentProcessor->createShipment($orderId, $orderItemsArray);
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 item(s) have been sent.', $totalItems));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('A total of %1 item(s) have not been sent.', count($reservationIdsArray) - $totalItems));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $reservationQtyToShip
     * @param $reservationOrder
     * @param $reservationsShipped
     * @param $reservationSerialsToShip
     *
     * @return mixed
     */
    private function getReservationsShipped($reservationQtyToShip, $reservationOrder, $reservationsShipped, $reservationSerialsToShip)
    {
        if ($reservationQtyToShip > 0) {
            if (!array_key_exists($reservationOrder->getOrderItemId(), $reservationsShipped)) {
                $reservationsShipped[$reservationOrder->getOrderItemId()] = [
                    'qty' => $reservationQtyToShip,
                    'order_id' => $reservationOrder->getOrderId(),
                    'serials' => implode(',', $reservationSerialsToShip),
                ];
                return $reservationsShipped;
            } else {
                $reservationsShipped[$reservationOrder->getOrderItemId()] = [
                    'qty' => $reservationsShipped[$reservationOrder->getOrderItemId()]['qty'] + $reservationQtyToShip,
                    'order_id' => $reservationOrder->getOrderId(),
                    'serials' => $reservationsShipped[$reservationOrder->getOrderItemId()]['serials'] . ', ' . implode(',', $reservationSerialsToShip),
                ];
                return $reservationsShipped;
            }
        }
        return $reservationsShipped;
    }

    /**
     * @param $qtyActions
     * @param $reservationOrder
     * @param $reservationQtyToShip
     * @param $reservationSerialsToShip
     */
    protected function getQtyAndSerials($qtyActions, $reservationOrder, &$reservationQtyToShip, &$reservationSerialsToShip)
    {
        if (array_key_exists('qty_shipped', $qtyActions) && array_key_exists($reservationOrder->getId(), $qtyActions['qty_shipped'])) {
            $reservationQtyToShip = $qtyActions['qty_shipped'][$reservationOrder->getId()];
        }
        $reservationSerialsToShip = [];
        if (array_key_exists('serials_shipped', $qtyActions) && array_key_exists($reservationOrder->getId(), $qtyActions['serials_shipped'])) {
            $reservationSerialsToShip = $qtyActions['serials_shipped'][$reservationOrder->getId()];
            if (is_array($reservationSerialsToShip) === false) {
                $reservationSerialsToShip = [$reservationSerialsToShip];
            }
            $this->rentalModelShipmentProcessor->updateQtyAndSerialsBasedOnInput($reservationOrder, $reservationQtyToShip, $reservationSerialsToShip);
        }
    }
}
