<?php

namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class ShipmentSaveCommited
 *
 * @package SalesIgniter\Rental\Observer
 */
class ShipmentSaveCommitted implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Injected Dependency Description
     *
     * @var \\SalesIgniter\Rental\Model\ShipmentProcessor
     */
    protected $rentalModelShipmentProcessor;

    /**
     * Injected Dependency Description
     *
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    protected $apiReservationOrdersRepositoryInterface;

    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ShipmentSaveBefore constructor.
     *
     * @param \SalesIgniter\Rental\Helper\Data                              $helperRental
     * @param \Magento\Framework\App\RequestInterface                       $request
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface               $orderItemRepository
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $apiReservationOrdersRepositoryInterface
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \SalesIgniter\Rental\Model\ShipmentProcessor                  $rentalModelShipmentProcessor
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderItemRepositoryInterface $orderItemRepository,
        \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $apiReservationOrdersRepositoryInterface,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \SalesIgniter\Rental\Model\ShipmentProcessor $rentalModelShipmentProcessor)
    {
        $this->rentalModelShipmentProcessor = $rentalModelShipmentProcessor;
        $this->apiReservationOrdersRepositoryInterface = $apiReservationOrdersRepositoryInterface;

        $this->helperRental = $helperRental;
        $this->request = $request;
        $this->stockManagement = $stockManagement;
        $this->orderItemRepository = $orderItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();

        /** @var array $postedData */
        $postedData = $this->request->getParams();

        if (array_key_exists('shipment', $postedData) && array_key_exists('items', $postedData['shipment'])) {
            foreach ($postedData['shipment']['items'] as $orderItemId => $qty) {
                $serialsToShip = [];
                if (array_key_exists('serial', $postedData) && array_key_exists($orderItemId, $postedData['serial'])) {
                    $serialsToShip = explode(',', $postedData['serial'][$orderItemId]);
                }
                $qtyToShip = $qty;
                $reservationOrder = $this->apiReservationOrdersRepositoryInterface->getByOrderItemId($orderItemId);

                if ($reservationOrder !== null) {
                    $this->rentalModelShipmentProcessor->updateQtyAndSerialsBasedOnInput($reservationOrder, $qtyToShip, $serialsToShip);
                    if ($qtyToShip > 0) {
                        $this->stockManagement->shipReservationQty($reservationOrder, $qtyToShip, $serialsToShip);
                    }
                } else {
                    $this->searchCriteriaBuilder->addFilter('parent_item_id', $orderItemId);
                    $criteria = $this->searchCriteriaBuilder->create();
                    $items = $this->orderItemRepository->getList($criteria)->getItems();
                    /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
                    foreach ($items as $orderItem) {
                        $reservationOrder = $this->apiReservationOrdersRepositoryInterface->getByOrderItemId($orderItem->getItemId());
                        $qtyToShip = $qty * $orderItem->getQtyOrdered(); /*- $orderItem->getQtyShipped();*/
                        if ($reservationOrder !== null) {
                            $this->rentalModelShipmentProcessor->updateQtyAndSerialsBasedOnInput($reservationOrder, $qtyToShip, $serialsToShip);
                            if ($qtyToShip > 0) {
                                $this->stockManagement->shipReservationQty($reservationOrder, $qtyToShip, $serialsToShip);
                            }
                        }
                    }
                }
            }
        }
    }
}
