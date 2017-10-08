<?php

namespace SalesIgniter\Rental\Plugin\Sales\Api;

use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\ReservationOrdersRepository;

class OrderRepositoryInterface
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $helperRental
     */
    protected $helperRental;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $stock;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                              $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                          $helperCalendar
     * @param \SalesIgniter\Rental\Model\Product\Stock                      $stock
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \Magento\Catalog\Model\Session                                $catalogSession
     *
     * @internal param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders $reservationOrders
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        Stock $stock,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
        $this->catalogSession = $catalogSession;
        $this->stock = $stock;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->stockManagement = $stockManagement;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $result
     *
     * @return mixed
     * @throws \Exception
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $result
    ) {
        $statuses = $this->stock->reserveInventoryByStatus();
        if ($result->getStatus() == $statuses) {
            $this->stockManagement->reserveOrder($result);
        }
        return $result;
    }
}
