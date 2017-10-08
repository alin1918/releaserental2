<?php

namespace SalesIgniter\Rental\Observer;

use Magento\Framework\App\RequestInterface;
use SalesIgniter\Rental\Model\Product\Stock;

/**
 * Class ShipmentSaveBefore
 *
 * @package SalesIgniter\Rental\Observer
 */
class OrderStatusUpdate implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Injected Dependency Description
     *
     * @var \SalesIgniter\Rental\Model\ShipmentProcessor
     */
    protected $rentalModelShipmentProcessor;

    /**
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
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $stock;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * OrderStatusUpdate constructor.
     *
     * @param \SalesIgniter\Rental\Helper\Data                              $helperRental
     * @param \Magento\Framework\App\RequestInterface                       $request
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $apiReservationOrdersRepositoryInterface
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \SalesIgniter\Rental\Model\Product\Stock                      $stock
     * @param \SalesIgniter\Rental\Model\ShipmentProcessor                  $rentalModelShipmentProcessor
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        RequestInterface $request,
        \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $apiReservationOrdersRepositoryInterface,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        Stock $stock,
        \SalesIgniter\Rental\Model\ShipmentProcessor $rentalModelShipmentProcessor)
    {
        $this->rentalModelShipmentProcessor = $rentalModelShipmentProcessor;
        $this->apiReservationOrdersRepositoryInterface = $apiReservationOrdersRepositoryInterface;

        $this->helperRental = $helperRental;
        $this->request = $request;
        $this->stock = $stock;
        $this->stockManagement = $stockManagement;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        //$statuses = $this->stock->reserveInventoryByStatus();
        //if ($order->getStatus() === $statuses) {
        //  $this->stockManagement->reserveOrder($order);
        //}
    }
}
