<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Event\ObserverInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

class CancelOrderItemObserver implements ObserverInterface
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                              $helperRental
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder           $searchCriteriaBuilder
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_helperRental = $helperRental;

        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockManagement = $stockManagement;
    }

    /**
     * Validates the qty inventory
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $observer->getEvent()->getItem();

        $reservationOrder = $this->reservationOrdersRepository->getByOrderItemId($orderItem->getId());
        if ($reservationOrder !== null) {
            $this->stockManagement->cancelReservationQty($reservationOrder, $orderItem->getQtyOrdered());
        }
        return $this;
    }
}
