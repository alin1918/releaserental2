<?php

/**
 * Copyright © 2018 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 *
 */

namespace SalesIgniter\Rental\Cron;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Emails\ReturnOverdueSender;
use SalesIgniter\Rental\Model\Product\Stock;

/**
 * Class SendCustomerReminder
 * cron for customer reminders
 *
 * @package SalesIgniter\Rental\Model
 */
class AsyncInventoryUpdate
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    private $orderInterfaceFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \SalesIgniter\Rental\Model\Emails\ReturnReminderSender
     */
    private $returnOverdueSender;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $stock;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    private $filterBuilder;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory                 $orderInterfaceFactory
     * @param \SalesIgniter\Rental\Model\Emails\ReturnOverdueSender         $returnOverdueSender
     * @param \SalesIgniter\Rental\Helper\Calendar                          $calendarHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface                   $orderRepository
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface               $orderItemRepository
     * @param \SalesIgniter\Rental\Model\Product\Stock                      $stock
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     *
     * @internal param $
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        ReturnOverdueSender $returnOverdueSender,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        Stock $stock,
        FilterBuilder $filterBuilder,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderRepository = $orderRepository;
        $this->returnOverdueSender = $returnOverdueSender;
        $this->orderItemRepository = $orderItemRepository;
        $this->calendarHelper = $calendarHelper;
        $this->stock = $stock;
        $this->filterBuilder = $filterBuilder;
        $this->stockManagement = $stockManagement;
    }

    public function execute()
    {
        if ($this->stock->reserveInventoryWithoutOrderInvoiced()) {

            /*$this->searchCriteriaBuilder->addFilters(
            [
            $this->filterBuilder
            ->setField('is_reserved')
            ->setValue(0)
            ->create()
            ]
            );*/
            $this->searchCriteriaBuilder->addFilter('is_reserved', 0);
            $criteria = $this->searchCriteriaBuilder->create();
            $items = $this->orderRepository->getList($criteria)->getItems();

            foreach ($items as $item) {
                $this->stockManagement->reserveOrder($item);
                $item->setIsReserved(1);
                $item->save();
            }
        }
    }
}
