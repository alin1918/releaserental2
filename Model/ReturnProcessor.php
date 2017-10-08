<?php
/**
 * Created by PhpStorm.
 * User: cristian
 * Date: 10/10/2016
 * Time: 7:53 AM
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Emails\ReturnSender;

class ReturnProcessor
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;

    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \SalesIgniter\Rental\Model\Emails\ReturnSender
     */
    private $returnSender;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    private $orderInterfaceFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManager
     * @param \Magento\Catalog\Model\Session                                $catalogSession
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory                 $orderInterfaceFactory
     * @param \SalesIgniter\Rental\Model\Emails\ReturnSender                $returnSender
     * @param \Magento\Sales\Api\OrderRepositoryInterface                   $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param \Magento\Framework\Registry                                   $coreRegistry
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
        ReturnSender $returnSender,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
        $this->stockManagement = $stockManagement;
        $this->returnSender = $returnSender;
        $this->orderRepository = $orderRepository;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
    }

    public function returnListOfSerials($serialsToBeReturned)
    {
        //todo implement this
    }

    public function assignListOfSerialsToReservationsShipped($serialsToBeReturned)
    {
        //todo implement
    }

    /**
     *
     * @param $orderId
     * @param $orderItemsArray
     */
    public function sendReturnConfirmation($orderId, $orderItemsArray)
    {
        $order = $this->orderRepository->get($orderId);
        $return = $this->orderInterfaceFactory->create();
        foreach ($orderItemsArray as $orderItemId => $qty) {
            foreach ($order->getItems() as $item) {
                if ($item->getItemId() == $orderItemId) {
                    $item->setQty($qty);
                    $item->setId(null);
                    $return->addItem($item);
                    break;
                }
            }
        }
        $this->returnSender->send($order, $return);
    }

    /**
     * @param $reservationOrder
     * @param $reservationQtyToReturn
     * @param $reservationSerialsToReturn
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateQtyAndSerialsBasedOnInput($reservationOrder, &$reservationQtyToReturn, &$reservationSerialsToReturn)
    {
        $qtyToReturnFromSerials = $reservationSerialsToReturn;
        if (count($qtyToReturnFromSerials) > $reservationQtyToReturn) {
            $reservationQtyToReturn = count($qtyToReturnFromSerials);
        }
        if (count($qtyToReturnFromSerials) < $reservationQtyToReturn) {
            throw new LocalizedException(__('Quantity is different than the number of serials returned'));
        }
        $reservationSerialsToReturn = explode(',', $reservationSerialsToReturn);
    }
}
