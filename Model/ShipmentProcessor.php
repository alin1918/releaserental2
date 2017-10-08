<?php

namespace SalesIgniter\Rental\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

/**
 * Class Calendar
 * Everything related to calendar
 *
 * @package SalesIgniter\Rental\Model
 */
class ShipmentProcessor
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
     */
    private $serialNumberDetailsRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    private $shipmentFactory;
    /**
     * @var \SalesIgniter\Rental\Model\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    private $shipmentSender;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface                                                  $storeManager
     * @param \Magento\Catalog\Model\Session                                                              $catalogSession
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository                                    $serialNumberDetailsRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface                                                 $orderRepository
     * @param \Magento\Framework\ObjectManagerInterface|\SalesIgniter\Rental\Model\ObjectManagerInterface $objectManager
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender                                      $shipmentSender
     * @param \Magento\Sales\Model\Order\ShipmentFactory                                                  $shipmentFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                                $searchCriteriaBuilder
     * @param \Magento\Framework\Registry                                                                 $coreRegistry
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        SerialNumberDetailsRepository $serialNumberDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        ObjectManagerInterface $objectManager,
        ShipmentSender $shipmentSender,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->objectManager = $objectManager;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * @param     $productId
     * @param     $excludedSerials
     *
     * @param int $nrElements
     *
     * @return array
     */
    public function getAvailableSerials($productId, $excludedSerials, $nrElements = 0)
    {
        $this->searchCriteriaBuilder->addFilter('product_id', $productId);
        $this->searchCriteriaBuilder->addFilter('status', 'available');
        if (count($excludedSerials) > 0) {
            $this->searchCriteriaBuilder->addFilter('serialnumber', $excludedSerials, 'nin');
        }

        $criteria = $this->searchCriteriaBuilder->create();
        $returnData = [];
        $items = $this->serialNumberDetailsRepository->getList($criteria)->getItems();
        $cnt = 0;
        foreach ($items as $item) {
            if ($nrElements > 0 && $cnt === $nrElements) {
                break;
            }
            $returnData[] = $item->getSerialnumber();
            $cnt++;
        }
        return $returnData;
    }

    public function shipListOfSerials($serialsToBeShipped)
    {
        //todo implement this
    }

    public function assignListOfSerialsToReservationsNotShipped($serialsToBeShipped)
    {
        //todo implement
    }

    /**
     * @param int   $orderId
     * @param array $itemArray ->orderItemId as key and qty as value
     *
     * @throws \Exception
     */
    public function createShipment($orderId, $itemArray)
    {
        $order = $this->orderRepository->get($orderId);
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentFactory->create($order, $itemArray);
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();
        //todo might need an observer for sales_order_shipment_item_save_before to save resorder data, but not so important
        $this->shipmentSender->send($shipment, true);//if second parameter is not true than cron:run needs to be run
    }

    /**
     * @param $reservationOrder
     * @param $reservationQtyToShip
     * @param $reservationSerialsToShip
     */
    public function updateQtyAndSerialsBasedOnInput($reservationOrder, &$reservationQtyToShip, &$reservationSerialsToShip)
    {
        $qtyToShipFromSerials = $reservationSerialsToShip;
        if (count($qtyToShipFromSerials) > $reservationQtyToShip) {
            $reservationQtyToShip = count($qtyToShipFromSerials);
        }
        if (count($qtyToShipFromSerials) < $reservationQtyToShip) {
            $qtyToShipFromSerials = array_merge($qtyToShipFromSerials,
                $this->getAvailableSerials($reservationOrder->getProductId(), $qtyToShipFromSerials, $reservationQtyToShip - count($qtyToShipFromSerials))
            );

            $reservationSerialsToShip = $qtyToShipFromSerials;
        }
    }
}
