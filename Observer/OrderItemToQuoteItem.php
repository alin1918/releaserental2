<?php
/**
 * Copyright © 2018 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 *
 */

namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Event\ObserverInterface;
//use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * Validator observer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 */
class OrderItemToQuoteItem implements ObserverInterface
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $helperRental;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * @var array
     */
    protected $baseInventory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $quoteSessionFrontend;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $productStock;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * @var ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar $calendarHelper
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $productStock
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Checkout\Model\Session $quoteSessionFrontend
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Catalog\Model\Session $catalogSession
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        StockManagementInterface $productStock,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $quoteSessionFrontend,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Catalog\Model\Session $catalogSession
    )
    {
        $this->helperRental = $helperRental;
        $this->calendarHelper = $calendarHelper;
        $this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->quoteSessionFrontend = $quoteSessionFrontend;
        $this->productStock = $productStock;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->quoteSession = $quoteSession;
        $this->serializer = $serializer;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
    }


    /**
     * Add Data to know when the item is cancelled
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $observer->getEvent()->getOrderItem();
        $reservationOrder = $this->reservationOrdersRepository->getByOrderItemId($orderItem->getId());
        $reservData = $reservationOrder->getData();
        $resData = [
            'start_date_with_turnover' => $reservData['start_date_use_grid'],
            'end_date_with_turnover' => $reservData['end_date_use_grid'],
            'qty' => $orderItem->getQtyOrdered()
        ];

        $quoteItem->addOption(
            new \Magento\Framework\DataObject(
                [
                    'product' => $quoteItem->getProduct(),
                    'code' => 'inventory_to_cancel',
                    'value' => $this->serializer->serialize($resData)
                ]
            )
        );
        return $this;
    }


}
