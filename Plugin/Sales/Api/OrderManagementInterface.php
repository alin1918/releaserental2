<?php

namespace SalesIgniter\Rental\Plugin\Sales\Api;

use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\ReservationOrdersRepository;

class OrderManagementInterface {

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
		$this->helperRental                = $helperRental;
		$this->helperCalendar              = $helperCalendar;
		$this->catalogSession              = $catalogSession;
		$this->stock                       = $stock;
		$this->reservationOrdersRepository = $reservationOrdersRepository;
		$this->stockManagement             = $stockManagement;
	}

	/**
	 * @param \Magento\Sales\Api\OrderManagementInterface $subject
	 * @param \Closure                                    $proceed
	 * @param \Magento\Sales\Api\Data\OrderInterface      $order
	 *
	 * @return \Magento\Sales\Api\Data\OrderInterface
	 */
	public function aroundPlace(
		\Magento\Sales\Api\OrderManagementInterface $subject,
		\Closure $proceed,
		\Magento\Sales\Api\Data\OrderInterface $order
	) {
		/**
		 * For disabled shipping to work. An active free shipping method should be available
		 *
		 */
		if ( $order->getIsVirtual() ) {
			/** @var \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress */
			$billingAddress = $order->getBillingAddress();
			/** @var \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress */
			$shippingAddress = clone $billingAddress;
			$shippingAddress->setId( null );
			$shippingAddress->setAddressType( 'shipping' );
			$order->setIsVirtual( 0 );
			$order->setCanShipPartially( 1 );
			$order->setCanShipPartiallyItem( 1 );
			$order->setShippingAddress( $shippingAddress );
			$order->setShippingMethod( 'free_shipping' );
		}
		$order->setIsReserved( 0 );
		$returnOrder = $proceed( $order );
		$this->catalogSession->unsetStartDateGlobal();
		$this->catalogSession->unsetEndDateGlobal();

		return $returnOrder;
	}
}
