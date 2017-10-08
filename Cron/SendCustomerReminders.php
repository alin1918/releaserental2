<?php

namespace SalesIgniter\Rental\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Emails\ReturnReminderSender;

/**
 * Class SendCustomerReminder
 * cron for customer reminders
 *
 * @package SalesIgniter\Rental\Model
 */
class SendCustomerReminders
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
    private $returnReminderSender;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory                 $orderInterfaceFactory
     * @param \SalesIgniter\Rental\Model\Emails\ReturnReminderSender        $returnReminderSender
     * @param \SalesIgniter\Rental\Helper\Calendar                          $calendarHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface                   $orderRepository
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface               $orderItemRepository
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     *
     * @internal param $
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        ReturnReminderSender $returnReminderSender,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderRepository = $orderRepository;
        $this->returnReminderSender = $returnReminderSender;
        $this->orderItemRepository = $orderItemRepository;
        $this->calendarHelper = $calendarHelper;
    }

    public function execute()
    {
        //reminder today+x >= enddate
        //overdue today > enddate
        $today = $this->calendarHelper->getTimeAccordingToTimeZone();
        $today = new \DateTime($today->format('Y-m-d H:i:s'));
        $reminderDate = $today->add(new \DateInterval('P' . $this->returnReminderSender->getReminderDays() . 'D'));
        $this->searchCriteriaBuilder->addFilter('end_date_with_turnover', $reminderDate->format('Y-m-d H:i:s'), 'lteq');
        $this->searchCriteriaBuilder->addFilter('return_date', new \Zend_Db_Expr('null'), 'is');
        $this->searchCriteriaBuilder->addFilter('ship_date', new \Zend_Db_Expr('not null'), 'is');
        $this->searchCriteriaBuilder->addFilter('qty_use_grid', 0, 'gt');
        $criteria = $this->searchCriteriaBuilder->create();
        $items = $this->reservationOrdersRepository->getList($criteria)->getItems();

        $customerList = [];

        foreach ($items as $item) {
            $order = $this->orderRepository->get($item->getOrderId());
            $item = $this->orderItemRepository->get($item->getOrderItemId());
            $customerList[$order->getCustomerEmail()][] = [
                'order' => $order,
                'item' => $item
            ];
        }
        foreach ($customerList as $customerEmail => $orderList) {
            $return = $this->orderInterfaceFactory->create();
            $ordersId = '';
            foreach ($orderList as $orderItems) {
                $order = $orderItems['order'];
                $ordersId .= $order->getIncrementId() . ', ';
                $item = $orderItems['item'];
                $item->setId(null);
                $return->addItem($item);
            }

            $ordersId = substr($ordersId, 0, -2);
            $this->returnReminderSender->send($order, $ordersId, $return);
        }
    }
}
