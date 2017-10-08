<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Event\ObserverInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;

class SalesOrderDeleteEventObserver implements ObserverInterface
{
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     */
    public function __construct(
        StockManagementInterface $stockManagement

    ) {
        $this->stockManagement = $stockManagement;
    }

    /**
     * Cleanup product reviews after product delete
     *
     * @param   \Magento\Framework\Event\Observer $observer
     *
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventOrder = $observer->getEvent()->getOrder();
        if ($eventOrder && $eventOrder->getId()) {
            $this->stockManagement->deleteReservationsByOrderId($eventOrder->getId());
        }

        return $this;
    }
}
