<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Event\ObserverInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

class RefundOrderInventoryObserver implements ObserverInterface
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var ReservationOrdersRepositoryInterface $reservationOrdersRepository
     */
    private $reservationOrdersRepository;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                  $helperRental
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param ReservationOrdersRepositoryInterface              $reservationOrdersRepository
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository
    ) {
        $this->_helperRental = $helperRental;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
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
        /* @var $creditMemo \Magento\Sales\Model\Order\Creditmemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditMemo->getAllItems() as $item) {
            $reservationOrder = $this->reservationOrdersRepository->getByOrderItemId($item->getOrderItemId());

            if ($reservationOrder !== null) {
                $this->stockManagement->cancelReservationQty($reservationOrder, $item->getQty());
            }
        }

        return $this;
    }
}
