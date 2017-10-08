<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Event\ObserverInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;

class ProcessProductAfterDeleteEventObserver implements ObserverInterface
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
        $eventProduct = $observer->getEvent()->getProduct();
        if ($eventProduct && $eventProduct->getId()) {
            $this->stockManagement->deleteReservationsByProductId($eventProduct->getId());
        }

        return $this;
    }
}
