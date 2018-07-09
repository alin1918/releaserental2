<?php

/**
 * Copyright © 2018 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
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
 * cron for customer reminders.
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
     * $state.
     *
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * Constructor function.
     *
     * @param SearchCriteriaBuilder                             $searchCriteriaBuilder
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory     $orderInterfaceFactory
     * @param ReturnOverdueSender                               $returnOverdueSender
     * @param \SalesIgniter\Rental\Helper\Calendar              $calendarHelper
     * @param OrderRepositoryInterface                          $orderRepository
     * @param OrderItemRepositoryInterface                      $orderItemRepository
     * @param Stock                                             $stock
     * @param \Magento\Framework\App\State                      $state
     * @param FilterBuilder                                     $filterBuilder
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param ReservationOrdersRepositoryInterface              $reservationOrdersRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        ReturnOverdueSender $returnOverdueSender,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        Stock $stock,
        \Magento\Framework\App\State $state,
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
        $this->state = $state;
    }

    public function updateIsReserved()
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

    public function execute()
    {
        $this->state->emulateAreaCode(
            'frontend',
            [$this, 'updateIsReserved']
        );
        $this->updateIsReserved();
    }
}
