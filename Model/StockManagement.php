<?php

namespace SalesIgniter\Rental\Model;

use League\Period\Period;
use Magento\Catalog\Model\Product\Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\AddressRepository;
use SalesIgniter\Rental\Api\InventoryGridRepositoryInterface;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\Product\Type\Sirent;
use SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory;

/**
 * Class ReservationOrdersRepository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class StockManagement implements \SalesIgniter\Rental\Api\StockManagementInterface {
	/**
	 * @var \SalesIgniter\Rental\Model\ReservationOrdersFactory
	 */
	protected $objectFactory;
	/**
	 * @var \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory
	 */
	protected $collectionFactory;

	/**
	 * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
	 */
	protected $searchResultsFactory;
	/**
	 * @var \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders
	 */
	protected $reservationOrderResource;
	/**
	 * @var \SalesIgniter\Rental\Model\Product\Stock
	 */
	protected $stock;
	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	protected $calendarHelper;
	/**
	 * @var \Magento\Framework\Api\SearchCriteriaBuilder
	 */
	protected $searchCriteriaBuilder;
	/**
	 * @var \Magento\Framework\Api\SortOrderBuilder
	 */
	protected $sortOrderBuilder;
	/**
	 * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
	 */
	protected $serialNumberDetailsRepository;
	/**
	 * @var \Magento\Catalog\Model\ProductRepository
	 */
	protected $productRepository;
	/**
	 * @var \Magento\Catalog\Model\Product\Action
	 */
	protected $attributeAction;
	/**
	 * @var \SalesIgniter\Rental\Api\InventoryGridRepositoryInterface
	 */
	protected $inventoryGridRepository;
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $datetime;
	/**
	 * @var \Magento\Framework\DB\Transaction
	 */
	protected $transaction;
	/**
	 * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
	 */
	private $reservationOrdersRepository;
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	private $rentalHelper;
	/**
	 * @var \SalesIgniter\Rental\Helper\Date
	 */
	private $dateHelper;
	/**
	 * @var \SalesIgniter\Rental\Helper\Product
	 */
	private $productHelper;
	/**
	 * @var \Magento\Sales\Api\OrderItemRepositoryInterface
	 */
	private $orderItemRepository;
	/**
	 * @var \Magento\Sales\Api\Data\OrderAddressInterface
	 */
	private $orderAddress;

	/**
	 * @var \Magento\Sales\Model\Order\AddressFactory
	 */
	private $addressFactory;

	/**
	 * @var \Magento\Sales\Model\Order\AddressRepository
	 */
	private $addressRepository;
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	private $checkoutSession;
	/**
	 * @var \Magento\Backend\Model\Session\Quote
	 */
	private $quoteSession;

	/**
	 * ReservationOrdersRepository constructor.
	 *
	 * @param \SalesIgniter\Rental\Model\ReservationOrdersFactory                                                          $objectFactory
	 * @param \SalesIgniter\Rental\Helper\Calendar                                                                         $calendarHelper
	 * @param \SalesIgniter\Rental\Helper\Data                                                                             $rentalHelper
	 * @param \SalesIgniter\Rental\Helper\Product                                                                          $productHelper
	 * @param \SalesIgniter\Rental\Helper\Date                                                                             $dateHelper
	 * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory                                 $collectionFactory
	 * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders                                                   $reservationOrderResource
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime                                                                  $datetime
	 * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository                                                     $serialNumberDetailsRepository
	 * @param Stock                                                                                                        $stock
	 * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface                                                $reservationOrdersRepository
	 * @param \Magento\Framework\Api\SortOrderBuilder                                                                      $sortOrderBuilder
	 * @param \SalesIgniter\Rental\Api\InventoryGridRepositoryInterface                                                    $inventoryGridRepository
	 * @param \Magento\Catalog\Model\ProductRepository                                                                     $productRepository
	 * @param \Magento\Catalog\Model\Product\Action                                                                        $attributeAction
	 * @param \Magento\Framework\Api\SearchCriteriaBuilder                                                                 $searchCriteriaBuilder
	 * @param \Magento\Framework\DB\Transaction                                                                            $transaction
	 * @param \Magento\Framework\Api\SearchResultsInterfaceFactory                                                         $searchResultsFactory
	 * @param \Magento\Sales\Api\OrderItemRepositoryInterface                                                              $orderItemRepository
	 * @param \Magento\Sales\Api\Data\OrderAddressInterface                                                                $orderAddress
	 * @param \Magento\Sales\Model\Order\AddressRepository                                                                 $addressRepository
	 * @param \Magento\Checkout\Model\Session                                                                              $checkoutSession
	 * @param \Magento\Backend\Model\Session\Quote                                                                         $quoteSession
	 * @param \Magento\Sales\Model\Order\AddressFactory|\Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressFactory
	 */
	public function __construct(
		\SalesIgniter\Rental\Model\ReservationOrdersFactory $objectFactory,
		\SalesIgniter\Rental\Helper\Calendar $calendarHelper,
		\SalesIgniter\Rental\Helper\Data $rentalHelper,
		\SalesIgniter\Rental\Helper\Product $productHelper,
		\SalesIgniter\Rental\Helper\Date $dateHelper,
		\SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory,
		\SalesIgniter\Rental\Model\ResourceModel\ReservationOrders $reservationOrderResource,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		SerialNumberDetailsRepository $serialNumberDetailsRepository,
		Stock $stock,
		ReservationOrdersRepositoryInterface $reservationOrdersRepository,
		SortOrderBuilder $sortOrderBuilder,
		InventoryGridRepositoryInterface $inventoryGridRepository,
		ProductRepository $productRepository,
		\Magento\Catalog\Model\Product\Action $attributeAction,
		SearchCriteriaBuilder $searchCriteriaBuilder,
		\Magento\Framework\DB\Transaction $transaction,
		SearchResultsInterfaceFactory $searchResultsFactory,
		OrderItemRepositoryInterface $orderItemRepository,
		OrderAddressInterface $orderAddress,
		AddressRepository $addressRepository,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Backend\Model\Session\Quote $quoteSession,
		\Magento\Sales\Model\Order\AddressFactory $addressFactory
	) {
		$this->searchResultsFactory          = $searchResultsFactory;
		$this->objectFactory                 = $objectFactory;
		$this->collectionFactory             = $collectionFactory;
		$this->reservationOrderResource      = $reservationOrderResource;
		$this->stock                         = $stock;
		$this->calendarHelper                = $calendarHelper;
		$this->searchCriteriaBuilder         = $searchCriteriaBuilder;
		$this->sortOrderBuilder              = $sortOrderBuilder;
		$this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
		$this->productRepository             = $productRepository;
		$this->attributeAction               = $attributeAction;
		$this->inventoryGridRepository       = $inventoryGridRepository;
		$this->datetime                      = $datetime;
		$this->transaction                   = $transaction;
		$this->reservationOrdersRepository   = $reservationOrdersRepository;
		$this->rentalHelper                  = $rentalHelper;
		$this->dateHelper                    = $dateHelper;
		$this->productHelper                 = $productHelper;
		$this->orderItemRepository           = $orderItemRepository;
		$this->orderAddress                  = $orderAddress;
		$this->addressFactory                = $addressFactory;
		$this->addressRepository             = $addressRepository;
		$this->checkoutSession               = $checkoutSession;
		$this->quoteSession                  = $quoteSession;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	private function convertToDatetime( $data ) {
		if ( ! is_object( $data['start_date_with_turnover'] ) ) {
			$data['start_date_with_turnover'] = new \DateTime( $data['start_date_with_turnover'] );
		}
		if ( ! is_object( $data['end_date_with_turnover'] ) ) {
			$data['end_date_with_turnover'] = new \DateTime( $data['end_date_with_turnover'] );
		}
		if ( ! is_object( $data['start_date'] ) ) {
			$data['start_date'] = new \DateTime( $data['start_date'] );
		}
		if ( ! is_object( $data['end_date'] ) ) {
			$data['end_date'] = new \DateTime( $data['end_date'] );

			return $data;
		}

		return $data;
	}

	public function saveReservation( \SalesIgniter\Rental\Model\ReservationOrdersInterface $reservation, array $data ) {
		$dataArray = [];
		if ( empty( $data['start_date'] ) || $data['start_date'] == 'Invalid date' ) {
			$data['start_date'] = '0000-00-00 00:00:00';
		}
		if ( empty( $data['end_date'] ) || $data['end_date'] == 'Invalid date' ) {
			$data['end_date'] = '0000-00-00 00:00:00';
		}
		if ( empty( $data['reservationorder_id'] ) ) {
			$data['reservationorder_id'] = null;
		}
		unset( $data['start_date_with_turnover'] );
		unset( $data['end_date_with_turnover'] );
		unset( $data['start_date_use_grid'] );
		unset( $data['end_date_use_grid'] );
		unset( $data['qty_use_grid'] );

		$data['qty_use_grid'] = $data['qty'] - $data['qty_shipped'] - $data['qty_cancel'];

		$dataArray[] = $data;
		if ( $reservation->getReservationorderId() && $reservation->getReservationorderId() !== null ) {
			$this->searchCriteriaBuilder->addFilter( 'parent_id', $reservation->getReservationorderId() );
			$this->searchCriteriaBuilder->addFilter( 'qty_use_grid', 0, 'gt' );
			$criteria = $this->searchCriteriaBuilder->create();
			$items    = $this->reservationOrdersRepository->getList( $criteria )->getItems();
			$this->cancelReservationQty( $reservation, $reservation->getQtyUseGrid(), false );

			foreach ( $items as $item ) {
				$this->cancelReservationQty( $item, $item->getQtyUseGrid(), false );
				$dataItem             = $item->toArray( [] );
				$dataItem['end_date'] = $data['end_date'];
				unset( $dataItem['end_date_with_turnover'] );
				unset( $dataItem['end_date_use_grid'] );
				$dataArray[] = $dataItem;
			}
		}
		foreach ( $dataArray as $iData ) {
			$this->saveFromArray( $iData, true, true );
		}
	}

	public function deleteReservation( \SalesIgniter\Rental\Model\ReservationOrdersInterface $reservation ) {
		try {
			$this->searchCriteriaBuilder->addFilter( 'parent_id', $reservation->getReservationorderId() );
			$criteria = $this->searchCriteriaBuilder->create();
			$items    = $this->reservationOrdersRepository->getList( $criteria )->getItems();
			$this->cancelReservationQty( $reservation, $reservation->getQtyUseGrid(), false );
			$reservation->delete();
			foreach ( $items as $item ) {
				$this->cancelReservationQty( $item, $item->getQtyUseGrid(), false );
				$item->delete();
			}
		} catch ( Exception $exception ) {
			throw new CouldNotDeleteException( __( $exception->getMessage() ) );
		}

		return true;
	}

	public function deleteReservationById( $idRes ) {
		return $this->deleteReservation( $this->reservationOrdersRepository->getById( $idRes ) );
	}

	public function deleteReservationsByOrderId( $orderId ) {
		$this->searchCriteriaBuilder->addFilter( 'main_table.order_id', $orderId );
		$criteria = $this->searchCriteriaBuilder->create();
		$items    = $this->reservationOrdersRepository->getList( $criteria )->getItems();

		foreach ( $items as $item ) {
			$this->deleteReservation( $item );
		}

		return count( $items ) > 0;
	}

	public function deleteReservationsByProductId( $productId ) {
		$this->searchCriteriaBuilder->addFilter( 'main_table.product_id', $productId );
		$criteria = $this->searchCriteriaBuilder->create();
		$items    = $this->reservationOrdersRepository->getList( $criteria )->getItems();

		foreach ( $items as $item ) {
			$this->deleteReservation( $item );
		}
		if ( count( $items ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Saves a reservation to the reservationorders table. If you are updating an existing reservation,
	 * you should use the saveReservation method.
	 *
	 * @param array $data
	 *                           $data['start_date'] - YYYY-MM-DD HH:MM:SS
	 *                           $data['end_date'] - YYYY-MM-DD HH:MM:SS
	 *                           $data['start_date_with_turnover'] - YYYY-MM-DD HH:MM:SS optional if not used is auto-calculated
	 *                           $data['end_date_with_turnover'] - YYYY-MM-DD HH:MM:SS optional if not used is auto-calculated
	 *                           $data['qty'] - quantity to reserve
	 *                           $data['product_id'] - product id to reserve
	 *                           $data['serials_shipped'] - optional comma seperated string of serials that are shipped
	 *                           $data['serials_returned'] - optional comma seperated string of serials returned
	 *                           $data['reservationorder_id'] - if updating reservation set the id here
	 * @param bool  $updateStock
	 * @param bool  $useGridData
	 *
	 * @return \SalesIgniter\Rental\Model\ReservationOrdersInterface
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 */
	public function saveFromArray( $data, $updateStock = true, $useGridData = false ) {
		$reservationOrder = $this->objectFactory->create();
		if ( ! array_key_exists( 'end_date', $data ) || empty( $data['end_date'] ) ) {
			$data['end_date'] = '0000-00-00 00:00:00';
		}
		if ( array_key_exists( 'start_date_use_grid', $data ) ) {
			$data['start_date_with_turnover'] = $data['start_date_use_grid'];
		}
		if ( array_key_exists( 'end_date_use_grid', $data ) ) {
			$data['end_date_with_turnover'] = $data['end_date_use_grid'];
		}
		if ( ! array_key_exists( 'start_date_with_turnover', $data ) ) {
			if ( ! array_key_exists( 'not_use_turnover', $data ) ) {
				$data['start_date_with_turnover'] = $this->calendarHelper->getSendDate( $data['product_id'], $data['start_date'] );
			} else {
				$data['start_date_with_turnover'] = $data['start_date'];
			}
		}
		if ( ! array_key_exists( 'end_date_with_turnover', $data ) ) {
			if ( $data['end_date'] === '0000-00-00 00:00:00' ) {
				$data['end_date_with_turnover'] = '0000-00-00 00:00:00';
			} else {
				if ( ! array_key_exists( 'not_use_turnover', $data ) ) {
					$data['end_date_with_turnover'] = $this->calendarHelper->getReturnDate( $data['product_id'], $data['end_date'] );
				} else {
					$data['end_date_with_turnover'] = $data['end_date'];
				}
			}
		}
		if ( ! array_key_exists( 'start_date_use_grid', $data ) ) {
			$data['start_date_use_grid'] = $data['start_date_with_turnover'];
			if ( is_object( $data['start_date_with_turnover'] ) ) {
				$data['start_date_use_grid'] = $data['start_date_with_turnover']->format( 'Y-m-d H:i:s' );
			}
		}
		if ( ! array_key_exists( 'end_date_use_grid', $data ) ) {
			$data['end_date_use_grid'] = $data['end_date_with_turnover'];
			if ( is_object( $data['end_date_with_turnover'] ) ) {
				$data['end_date_use_grid'] = $data['end_date_with_turnover']->format( 'Y-m-d H:i:s' );
			}
		}
		/*
		 * because for some reason my install don't have default values I do the check here
		 * normally upgrade schema should make default values to 0
		 */
		if ( ! array_key_exists( 'qty_shipped', $data ) ) {
			$data['qty_shipped'] = 0;
		}
		if ( ! array_key_exists( 'qty_returned', $data ) ) {
			$data['qty_returned'] = 0;
		}
		if ( ! array_key_exists( 'qty_cancel', $data ) ) {
			$data['qty_cancel'] = 0;
		}
		/*
		 * end of checked
		 */

		if ( ! array_key_exists( 'qty_use_grid', $data ) ) {
			$data['qty_use_grid'] = $data['qty'];
		}
		$reservationOrder->setData( $data );
		try {
			$returnData = $reservationOrder->save();
		} catch ( Exception $e ) {
			throw new CouldNotSaveException( $e->getMessage() );
		}

		if ( $updateStock ) {
			if ( $useGridData ) {
				if ( array_key_exists( 'end_date_use_grid', $data ) ) {
					$data['end_date_with_turnover'] = $data['end_date_use_grid'];
				}
				if ( array_key_exists( 'start_date_use_grid', $data ) ) {
					$data['start_date_with_turnover'] = $data['start_date_use_grid'];
				}
				if ( array_key_exists( 'qty_use_grid', $data ) ) {
					$data['qty']        = $data['qty_use_grid'];
					$data['qty_cancel'] = 0;
				}
			}
			$this->updateInventory( $data );
		}

		return $returnData;
	}

	public function updateStockFromGridData( $data ) {
		$data['not_check_valid'] = true;
		if ( array_key_exists( 'end_date_use_grid', $data ) ) {
			$data['end_date_with_turnover'] = $data['end_date_use_grid'];
		}
		if ( array_key_exists( 'start_date_use_grid', $data ) ) {
			$data['start_date_with_turnover'] = $data['start_date_use_grid'];
		}
		if ( array_key_exists( 'qty_use_grid', $data ) ) {
			$data['qty']        = $data['qty_use_grid'];
			$data['qty_cancel'] = 0;
		}

		$this->updateInventory( $data );
	}

	/**
	 * Updates inventory for a product using $data array
	 * $data must have product_id, start_date_with_turnover, end_date_with_turnover
	 * and optional order_id. This function is used to actually update the inventory in the product serialized field.
	 *
	 * @param \Magento\Framework\DataObject | array $data
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function updateInventory( $data ) {
		if ( is_object( $data ) ) {
			$data = $data->getData();
		}
		/** @var array $data */
		if ( array_key_exists( 'product_id', $data ) &&
		     array_key_exists( 'start_date_with_turnover', $data ) &&
		     array_key_exists( 'end_date_with_turnover', $data )
		) {
			$productId             = $data['product_id'];
			$startDateWithTurnover = $data['start_date_with_turnover'];
			$endDateWithTurnover   = $data['end_date_with_turnover'];

			$data          = $this->convertToDatetime( $data );
			$qty           = array_key_exists( 'qty', $data ) ? $data['qty'] : 0;
			$qtyCancel     = array_key_exists( 'qty_cancel', $data ) ? $data['qty_cancel'] : 0;
			$notCheckValid = array_key_exists( 'not_check_valid', $data ) ? true : false;
			$orderId       = array_key_exists( 'order_id', $data ) ? $data['order_id'] : 0;
			$dates         = new \Magento\Framework\DataObject( $data );

			if ( $notCheckValid || $data['end_date'] === '0000-00-00 00:00:00' || $data['start_date'] === '0000-00-00 00:00:00' || $this->checkIntervalValid( $productId, $dates, $qty ) === Stock::NO_ERROR ) {
				$updatedInventory = $this->getUpdatedInventory( $productId, $startDateWithTurnover, $endDateWithTurnover, $qty, $qtyCancel, $orderId );
				$this->saveInventory( $productId, $updatedInventory );
			}
		}
	}

	/**
	 * Takes a product id and $updatedInventory (serialized array with both
	 * old and new reservations combined) and updates the serialized field.
	 *
	 * @param in $productId
	 * @param    $updatedInventory
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function saveInventory( $productId, $updatedInventory ) {
		foreach ( $this->rentalHelper->getStoreIdsForCurrentWebsite() as $storeId ) {
			try {
				$product = $this->productRepository->getById( $productId, false, $storeId );
			} catch ( NoSuchEntityException $e ) {
				return [];
			}
			$this->updateGridTableWithData( $updatedInventory, $productId );
			$product->setSirentInvBydateSerialized( serialize( $updatedInventory ) );

			$this->attributeAction->updateAttributes(
				[ $product->getId() ],
				[ 'sirent_inv_bydate_serialized' => serialize( $updatedInventory ) ],
				$storeId
			);
		}
	}

	/**
	 * Takes a product id and $updatedInventory (serialized array with both
	 * old and new reservations combined) and updates the serialized field.
	 *
	 * @param int $productId
	 * @param int $qty
	 *
	 * @return bool
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function updateSirentQuantity( $productId, $qty ) {
		foreach ( $this->rentalHelper->getStoreIdsForCurrentWebsite() as $storeId ) {
			try {
				$product = $this->productRepository->getById( $productId, false, $storeId );
			} catch ( NoSuchEntityException $e ) {
				return false;
			}
			$product->setSirentQuantity( $qty );

			$this->attributeAction->updateAttributes(
				[ $product->getId() ],
				[ 'sirent_quantity' => $qty ],
				$storeId
			);
		}

		return true;
	}

	public function cancelReservationQty( $reservationOrder, $qtyCancel, $recordInDb = true ) {
		$qtyCancel = (int) $qtyCancel;
		$data      = $reservationOrder->getData();
		if ( ! $recordInDb ) {
			$data['start_date_with_turnover'] = $data['start_date_use_grid'];
			$data['end_date_with_turnover']   = $data['end_date_use_grid'];
		}
		$data['qty']        = 0;
		$data['qty_cancel'] = $qtyCancel;

		if ( $recordInDb ) {
			$reservationOrder->setQtyCancel( $reservationOrder->getQtyCancel() + $qtyCancel );
			$reservationOrder->setQtyUseGrid( $reservationOrder->getQtyUseGrid() - $qtyCancel );
			try {
				$reservationOrder->save();
			} catch ( Exception $e ) {
				throw new CouldNotSaveException( $e->getMessage() );
			}

			$data['parent_id']    = $reservationOrder->getId();
			$data['qty_use_grid'] = 0;
			unset( $data['reservationorder_id'] );
			$this->saveFromArray( $data );
		} else {
			$this->updateInventory( $data );
		}
	}

	public function shipReservationQty( $reservationOrder, $qtyShip, $serialShip ) {
		$qtyShip             = (int) $qtyShip;
		$data                = $reservationOrder->getData();
		$shipDate            = $this->calendarHelper->approximateDateFromSetting( 'now', $data['product_id'] );
		$shipDateFormatted   = $shipDate->format( 'Y-m-d H:i:s' );
		$data['qty_shipped'] = $qtyShip;
		$data['ship_date']   = $shipDateFormatted;
		$isEarly             = false;
		if ( new \DateTime( $reservationOrder->getStartDateWithTurnover() ) > new \DateTime( $data['ship_date'] ) ) {
			$isEarly = true;
		}

		if ( $isEarly && $this->stock->reserveInventoryEarlySendDate() ) {
			$reservationOrder->setQtyUseGrid( $reservationOrder->getQtyUseGrid() - $qtyShip );

			$this->cancelReservationQty( $reservationOrder, $qtyShip, false );

			$data['start_date_use_grid'] = $shipDateFormatted;
			$data['end_date_use_grid']   = $data['end_date_with_turnover'];
			$data['qty_use_grid']        = $qtyShip;
		}

		$this->updateSerialsShipped( $reservationOrder, $qtyShip, $serialShip, $data );
		try {
			$reservationOrder->save();
		} catch ( Exception $e ) {
			throw new CouldNotSaveException( $e->getMessage() );
		}
		$data['parent_id']  = $reservationOrder->getId();
		$data['qty_cancel'] = 0;
		unset( $data['reservationorder_id'] );
		$this->saveFromArray( $data, false );

		//there is no way to force commit transactions
		//basically here no data is saved into tables until after commit and so the inventory grid can't be updated properly
		//this happens only when transaction object is used, since we do the actions on model save before.
		//doing it into the controller plugin might be the only solution

		if ( $isEarly && $this->stock->reserveInventoryEarlySendDate() ) {
			$dataForInventory                             = $data;
			$dataForInventory['start_date_with_turnover'] = $shipDateFormatted;
			$dataForInventory['qty']                      = $qtyShip;
			$dataForInventory['not_check_valid']          = true;
			$this->updateInventory( $dataForInventory );
		}
	}

	public function returnReservationQty( $reservationOrder, $qtyReturn, $serialReturn ) {
		$qtyReturn  = (int) $qtyReturn;
		$data       = $reservationOrder->getData();
		$returnDate = $this->calendarHelper->approximateDateFromSetting( 'now', $data['product_id'] );
		$returnDate->add( new \DateInterval( 'PT1H' ) );
		$data['return_date']  = $returnDate->format( 'Y-m-d H:i:s' );
		$data['qty_returned'] = $qtyReturn;

		$criteria = $this->getShippedItems( $reservationOrder );

		$items              = $this->reservationOrdersRepository->getList( $criteria )->getItems();
		$totalItemsReturned = 0;
		$qtyReturnOriginal  = $qtyReturn;
		foreach ( $items as $item ) {
			$serialsShippedItem  = array_filter( explode( ',', $item->getSerialsShipped() ), function ( $value ) {
				return $value !== '';
			} );
			$currentSerialReturn = [];
			if ( is_array( $serialReturn ) ) {
				$serialReturn = array_filter( $serialReturn, function ( $value ) {
					return $value !== '';
				} );
			} else {
				$serialReturn = [];
			}
			if ( count( $serialReturn ) > 0 ) {
				$currentSerialReturn = array_intersect( $serialReturn, $serialsShippedItem );
				$currentQtyReturn    = count( $currentSerialReturn );
				if ( $currentQtyReturn === 0 ) {
					continue;
				}
			} else {
				$currentQtyReturn = $item->getQtyShipped();
			}
			if ( $currentQtyReturn > $qtyReturn ) {
				$currentQtyReturn = $qtyReturn;
			}
			$isEarly = false;
			if ( new \DateTime( $reservationOrder->getEndDateWithTurnover() ) > new \DateTime( $data['return_date'] ) ) {
				$isEarly = true;
			}
			$isNew = false;
			if ( $item->getReturnDate() ) {
				$isNew = true;
			}
			$shipDate            = $item->getShipDate();
			$returnDateFormatted = $data['return_date'];

			$item->setQtyReturned( $currentQtyReturn );
			$item->setReturnDate( $returnDateFormatted );
			$item->setQtyUseGrid( 0 );
			/*
			 * todo put into function
			 */
			if ( $isEarly && $this->stock->reserveInventoryEarlyReturnDate() ) {
				$this->cancelReservationQty( $item, $currentQtyReturn, false );

				$item->setQtyReturned( $currentQtyReturn );
				$item->setQtyUseGrid( $currentQtyReturn );
				$item->setStartDateUseGrid( $shipDate );
				$item->setEndDateUseGrid( $returnDateFormatted );
			}

			$this->updateSerials( $reservationOrder, $currentSerialReturn, $currentQtyReturn, $item );

			if ( ! $isNew ) {
				try {
					$item->save();
				} catch ( Exception $e ) {
					throw new CouldNotSaveException( $e->getMessage() );
				}
			} else {
				$newData = $item->getData();
				unset( $newData['reservationorder_id'] );
				$this->saveFromArray( $newData, false );
			}
			try {
				$reservationOrder->save();
			} catch ( Exception $e ) {
				throw new CouldNotSaveException( $e->getMessage() );
			}

			if ( $isEarly && $this->stock->reserveInventoryEarlyReturnDate() ) {
				$dataForInventory                             = $data;
				$dataForInventory['start_date_with_turnover'] = $shipDate;
				$dataForInventory['end_date_with_turnover']   = $returnDateFormatted;
				$dataForInventory['qty']                      = $currentQtyReturn;
				$dataForInventory['qty_cancel']               = 0;
				$dataForInventory['not_check_valid']          = true;
				$this->updateInventory( $dataForInventory );
			}
			++ $totalItemsReturned;
			/****************/

			$qtyReturn    -= $currentQtyReturn;
			$serialReturn = array_diff( $serialReturn, $serialsShippedItem );
			if ( $qtyReturn <= 0 ) {
				break;
			}
		}

		if ( $totalItemsReturned !== $qtyReturnOriginal ) {
			return false;
		}

		return $totalItemsReturned;
	}

	public function reserveOrder( \Magento\Sales\Api\Data\OrderInterface $order ) {

		/** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
		foreach ( $order->getItems() as $item ) {
			$reservationOrderExisting = $this->reservationOrdersRepository->getByOrderItemId( $item->getId() );
			if ( $reservationOrderExisting !== null ) {
				break;
			}
			if ( $item->getProductType() === Sirent::TYPE_RENTAL ) {
				if ( $this->calendarHelper->getDisabledShipping( $item->getProductId() ) ) {
					$this->reserveDisableShipping( $item, $order );
				}
				if ( $item->getParentItem() ) {
					$buyRequest = $this->calendarHelper->prepareBuyRequest( $item->getParentItem() );
				} else {
					$buyRequest = $this->calendarHelper->prepareBuyRequest( $item );
				}

				$dates = $this->calendarHelper->getDatesFromBuyRequest(
					$buyRequest, $item->getProductId()
				);
				$qty   = $item->getQtyOrdered();
				if ( ! $dates->getIsBuyout() ) {
					$data = [
						'order_id'                 => $item->getOrderId(),
						'product_id'               => $item->getProductId(),
						'order_item_id'            => $item->getId(),
						'order_increment_id'       => $order->getIncrementId(),
						'qty'                      => $qty,
						'not_check_valid'          => true,
						'start_date_with_turnover' => $dates->getStartDateWithTurnover()->format( 'Y-m-d H:i:s' ),
						'end_date_with_turnover'   => $dates->getEndDateWithTurnover()->format( 'Y-m-d H:i:s' ),
						'start_date_use_grid'      => $dates->getStartDateWithTurnover()->format( 'Y-m-d H:i:s' ),
						'end_date_use_grid'        => $dates->getEndDateWithTurnover()->format( 'Y-m-d H:i:s' ),
						'start_date'               => $dates->getStartDate()->format( 'Y-m-d H:i:s' ),
						'end_date'                 => $dates->getEndDate()->format( 'Y-m-d H:i:s' ),
					];

					$this->saveFromArray( $data );
				} else {
					$currentQty = $this->rentalHelper->getAttribute( $item->getProductId(), 'sirent_quantity' );
					$this->updateSirentQuantity( $item->getProductId(), $currentQty - $qty );
				}
			}
		}
	}

	/**
	 * Function used to update the inventory with order increments array for every interval
	 * I think this function should be used for reports only and grids.
	 * Maybe should be used from the report, like and indexer. Update with order data.
	 * We never need order increments ids. Should also be a cron.
	 * The main issue is can add some not useful overhead to add it to calculation even if is per product.
	 *
	 * @param array $updatedInventory
	 * @param int   $productId
	 */
	private function updateGridTableWithData( &$updatedInventory, $productId ) {
		foreach ( $updatedInventory as $invTable ) {
			if ( $invTable['q'] > 0 ) {
				$startPeriod = new \DateTime( $invTable['s'] . ':00' );
				$endPeriod   = new \DateTime( $invTable['e'] . ':00' );
				$endPeriod->sub( new \DateInterval( 'PT1S' ) );
				$this->searchCriteriaBuilder->addFilter( 'main_table.product_id', $productId );
				$this->searchCriteriaBuilder->addFilter( 'qty_use_grid', 0, 'gt' );
				$this->searchCriteriaBuilder->addFilter( 'start_date_use_grid', $endPeriod->format( 'Y-m-d H:i:s' ), 'lteq' );
				$this->searchCriteriaBuilder->addFilter( 'end_date_use_grid', $startPeriod->format( 'Y-m-d H:i:s' ), 'gteq' );
				/*$this->searchCriteriaBuilder->addFilter('start_date_use_grid',
					new \Zend_Db_Expr('STR_TO_DATE("' . $endPeriod->format('Y-m-d H:i:s') . '", "%Y-%m-%d %H:%i:%s")'), 'le'
				);
				$this->searchCriteriaBuilder->addFilter('end_date_use_grid',
					new \Zend_Db_Expr('STR_TO_DATE("' . $startPeriod->format('Y-m-d H:i:s') . '", "%Y-%m-%d %H:%i:%s")'), 'ge'
				);*/
				//->addFilter('attribute_id', [$attributeIds['selected']], 'in');
				$criteria      = $this->searchCriteriaBuilder->create();
				$items         = $this->reservationOrdersRepository->getList( $criteria )->getItems();
				$invTable['o'] = [];
				foreach ( $items as $item ) {
					$invTable['o'] = array_unique( array_merge( $invTable['o'], [ $item->getOrderId() ] ) );
				}
				$this->inventoryGridRepository->deleteByProductId( $productId );
				$this->inventoryGridRepository->saveFromArray( [
					'start_date' => $startPeriod->format( 'Y-m-d H:i:s' ),
					'end_date'   => $endPeriod->format( 'Y-m-d H:i:s' ),
					'qty'        => $invTable['q'],
					'product_id' => $productId,
					'order_ids'  => implode( ',', $invTable['o'] ),
				] );
			}
		}
	}

	/**
	 * @param $product
	 *
	 * @return int
	 */
	private function getNonReturnedQtyForProduct( $product ) {
		$productId = $this->rentalHelper->getProductIdFromObject( $product );
		$today     = $this->calendarHelper->getTimeAccordingToTimeZone();
		$today     = new \DateTime( $today->format( 'Y-m-d H:i:s' ) );
		$qty       = 0;
		$this->searchCriteriaBuilder->addFilter( 'main_table.product_id', $productId );
		$this->searchCriteriaBuilder->addFilter( 'qty_use_grid', 0, 'gt' );
		$this->searchCriteriaBuilder->addFilter( 'end_date_with_turnover', $today->format( 'Y-m-d H:i:s' ), 'lt' );
		$this->searchCriteriaBuilder->addFilter( 'return_date', new \Zend_Db_Expr( 'null' ), 'is' );
		$this->searchCriteriaBuilder->addFilter( 'ship_date', new \Zend_Db_Expr( 'not null' ), 'is' );

		$criteria = $this->searchCriteriaBuilder->create();
		/** @var array $items */
		$items = $this->reservationOrdersRepository->getList( $criteria )->getItems();
		foreach ( $items as $item ) {
			$qty += (int) $item->getQtyUseGrid();
		}

		return $qty;
	}

	/**
	 * @param $reservationOrder
	 * @param $currentSerialReturn
	 * @param $currentQtyReturn
	 * @param $item
	 */
	protected function updateSerials( &$reservationOrder, $currentSerialReturn, $currentQtyReturn, &$item ) {
		$reservationOrder->setQtyReturned( $reservationOrder->getQtyReturned() + $currentQtyReturn );

		if ( count( $currentSerialReturn ) > 0 ) {
			$oldSerialsReturned = explode( ',', $reservationOrder->getSerialsReturned() );
			$allSerialReturned  = array_merge( $oldSerialsReturned, $currentSerialReturn );
			$allSerialReturned  = array_filter( $allSerialReturned, function ( $value ) {
				return $value !== '';
			} );
			$reservationOrder->setSerialsReturned( implode( ',', $allSerialReturned ) );
			$item->setSerialsReturned( implode( ',', $currentSerialReturn ) );
			$this->serialNumberDetailsRepository->updateSerials( $reservationOrder->getProductId(), 'available', $currentSerialReturn, null );
		}
	}

	/**
	 * @param $reservationOrder
	 * @param $qtyShip
	 * @param $serialShip
	 * @param $data
	 */
	protected function updateSerialsShipped( $reservationOrder, $qtyShip, $serialShip, &$data ) {
		$reservationOrder->setQtyShipped( $reservationOrder->getQtyShipped() + $qtyShip );
		if ( count( $serialShip ) > 0 ) {
			$oldSerialsShipped = explode( ',', $reservationOrder->getSerialsShipped() );
			$allSerialShipped  = array_merge( $oldSerialsShipped, $serialShip );
			$allSerialShipped  = array_filter( $allSerialShipped, function ( $value ) {
				return $value !== '';
			} );
			$reservationOrder->setSerialsShipped( implode( ',', $allSerialShipped ) );
			$data['serials_shipped'] = implode( ',', $serialShip );
			$this->serialNumberDetailsRepository->updateSerials( $reservationOrder->getProductId(), 'out', $serialShip, $reservationOrder->getId() );
		}
	}

	/**
	 * @param $reservationOrder
	 *
	 * @return \Magento\Framework\Api\SearchCriteria
	 */
	protected function getShippedItems( $reservationOrder ) {
		$this->searchCriteriaBuilder->addFilter( 'parent_id', $reservationOrder->getReservationorderId() );
		$this->searchCriteriaBuilder->addFilter( 'return_date', new \Zend_Db_Expr( 'null' ), 'is' );
		//->addFilter('attribute_id', [$attributeIds['selected']], 'in');
		$this->sortOrderBuilder->setField( 'ship_date' );
		$this->sortOrderBuilder->setAscendingDirection();
		$sortOrder = $this->sortOrderBuilder->create();
		$this->searchCriteriaBuilder->addSortOrder( $sortOrder );
		$criteria = $this->searchCriteriaBuilder->create();

		return $criteria;
	}

	/**
	 * Function returns the Sirent Quantity
	 * Sirent Quantity is the total stock for a rental product without taking into account
	 * future or current reservations or products in maintenance.
	 *
	 * @param \Magento\Catalog\Model\Product|int $product
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getSirentQuantity( $product ) {

		/** @var array $productsArray */
		if ( $this->rentalHelper->isBundle( $product ) ) {
			$productsArray = $this->productHelper->getProductsSelectionsArray( $product );

			$qty = 10000;
			/*
			 * bundle has:
			 * 3XP1
			 * 5XP2
			 * 7XP3
			 * P1->current_stock = 5
			 * P2->current_stock = 3
			 * P3->current_stock = 6
			 * bundle->current_stock = 0
			 */

			foreach ( $productsArray as $selectionProduct => $iProduct ) {
				if ( is_numeric( $iProduct['selection_product_quantity'] ) && $iProduct['selection_product_quantity'] > 0 ) {
					$iProductSirentQty = $this->getSirentQuantity( $iProduct['selection_product_id'] );
					$curQty            = (int) ( $iProductSirentQty / $iProduct['selection_product_quantity'] );
					if ( $curQty < $qty ) {
						$qty = $curQty;
					}
				}
			}
		} else {
			$qty = $this->rentalHelper->getAttribute( $product, 'sirent_quantity' );
			if ( ! $qty ) {
				$qty = 0;
			}
			if ( $this->stock->reserveInventoryUntilReturnDate() ) {
				$qty -= $this->getNonReturnedQtyForProduct( $product );
			}
			$qty -= $this->getNonEndDateQtyForProduct( $product );
		}

		return $qty;
	}

	/**
	 * Function to get the actual array for the inventory. We don't load reservation orders table. We use it just for saving data
	 * For calculations we use the whole serialized array for the product. Since is only for a product the memory needed will be low.
	 *
	 * @param $product
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getInventoryTable( $product ) {
		$currentInventory = [];
		if ( null === $product ) {
			return $currentInventory;
		}
		/** @var array $productsArray */
		if ( $this->rentalHelper->isBundle( $product ) ) {
			$productsArray = $this->productHelper->getProductsSelectionsArray( $product );

			/*
			 * bundle has:
			 * 3XP1
			 * 5XP2
			 * 7XP3
			 * P1->inventory_table = []
			 * P2->inventory_table = []
			 * P3->inventory_table = []
			 * bundle->current_stock = 0
			 */
			foreach ( $productsArray as $selectionProduct => $iProduct ) {
				$curInventoryTable = $this->getInventoryTable( $iProduct['selection_product_id'] );
				$currentInventory  = array_merge( $currentInventory, $curInventoryTable );
			}
		} else {
			$inventorySerialized = $this->rentalHelper->getAttribute( $product, 'sirent_inv_bydate_serialized' );
			if ( $inventorySerialized && $inventorySerialized !== '0.0000' && $inventorySerialized !== 'null' ) {
				$currentInventory = unserialize( $inventorySerialized );
			}
		}

		return $currentInventory;
	}

	/**
	 * Returns the available quantity for the start and end dates for the product
	 * Can work with reservations excluded by id from inventory.
	 *
	 * @param        $product
	 * @param string $startDate
	 * @param string $endDate
	 * @param        $excludingReservationsIds
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getAvailableQuantity( $product, $startDate = '', $endDate = '', $excludingReservationsIds = [] ) {
		$maxQty = $this->getSirentQuantity( $product );
		if ( $startDate === '' || $endDate === '' ) {
			return $maxQty;
		}
		try {
			$toCheckPeriod = new Period( $startDate, $endDate );
		} catch ( \LogicException $e ) {
			return $maxQty;
		}

		if ( ! is_array( $excludingReservationsIds ) ) {
			$excludingReservationsIds = [ $excludingReservationsIds ];
		}
		$items = [];
		if ( count( $excludingReservationsIds ) > 0 ) {
			$this->searchCriteriaBuilder->addFilter( 'reservationorder_id', $excludingReservationsIds, 'in' );
			$this->searchCriteriaBuilder->addFilter( 'main_table.product_id', $this->rentalHelper->getProductIdFromObject( $product ) );
			$this->searchCriteriaBuilder->addFilter( 'qty_use_grid', 0, 'gt' );
			$criteria = $this->searchCriteriaBuilder->create();
			$items    = $this->reservationOrdersRepository->getList( $criteria )->getItems();
		}

		/** @var array $currentInventory */
		$currentInventory = $this->getInventoryTable( $product );
		$reservedQty      = 0;
		/** @var array $reservationObject */
		foreach ( $currentInventory as $reservationObject ) {

			/** @var Period $reservationPeriod */
			$reservationPeriod    = new Period(
				$reservationObject['s'] . ':00',
				$reservationObject['e'] . ':00'
			);
			$reservedAvailableQty = 0;
			foreach ( $items as $item ) {
				/* @var Period $reservationPeriod */
				$reservationPeriodAvailable = new Period( $item->getStartDateUseGrid(), $item->getEndDateUseGrid() );
				if ( $reservationPeriodAvailable->overlaps( $reservationPeriod ) || $reservationPeriodAvailable->sameValueAs( $reservationPeriod ) ) {
					if ( $reservedAvailableQty < (int) $item->getQtyUseGrid() ) {
						$reservedAvailableQty = (int) $item->getQtyUseGrid();
					}
				}
			}

			if ( $reservationPeriod->overlaps( $toCheckPeriod ) || $reservationPeriod->sameValueAs( $toCheckPeriod ) ) {
				if ( $reservedQty < $reservationObject['q'] - $reservedAvailableQty ) {
					$reservedQty = $reservationObject['q'] - $reservedAvailableQty;
				}
			}
		}

		return $maxQty - $reservedQty;
	}

	/**
	 * Function check if selected dates have any errors.
	 *
	 * @param            $product
	 * @param            $dates
	 * @param            $currentQuantity
	 * @param null|array $baseInventory
	 *
	 * @return int
	 *
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function checkIntervalValid( $product, $dates, $currentQuantity, $baseInventory = null ) {
		$errorType = Stock::NO_ERROR;
		if ( $currentQuantity === 0 ) {
			return $errorType;
		}
		$noMinimumPeriodCheck = false;
		if ( $dates->getWithoutMinimumPeriod() ) {
			$noMinimumPeriodCheck = true;
		}
		/** @var \DateTime $fromDate */
		$fromDate = $dates->getStartDate();
		/** @var \DateTime $toDate */
		$toDate = $dates->getEndDate();
		/** @var \DateTime $sendDate */
		$sendDate = $dates->getStartDateWithTurnover();
		/** @var \DateTime $returnDate */
		$returnDate            = $dates->getEndDateWithTurnover();
		$disabledDaysWeekStart = $this->calendarHelper->getDisabledDaysWeekStart( $product );
		$disabledDaysWeekEnd   = $this->calendarHelper->getDisabledDaysWeekEnd( $product );
		$disabledDates         = $this->calendarHelper->getExcludedDates( ExcludedDaysWeekFrom::CALENDAR, $product );
		$disabledDatesFull     = $this->calendarHelper->getExcludedDates( ExcludedDaysWeekFrom::FULL_CALENDAR, $product );
		$minimumPeriod         = $this->calendarHelper->getMinimumPeriod( $product );
		$maximumPeriod         = $this->calendarHelper->getMaximumPeriod( $product );
		$availableQuantity     = $this->getSirentQuantity( $product );
		if ( null === $baseInventory ) {
			$baseInventory = $this->getInventoryTable( $product );
		}

		$diff = $toDate->diff( $fromDate );

		if ( ! $noMinimumPeriodCheck &&
		     $this->dateHelper->compareInterval( '0d', $minimumPeriod ) !== 0 &&
		     $this->dateHelper->compareInterval( $diff, $minimumPeriod, true ) === - 1
		) {
			$errorType = Stock::MINIMUM_PERIOD_ERROR;

			return $errorType;
		}

		if ( $this->dateHelper->compareInterval( '0d', $maximumPeriod ) !== 0 && $this->dateHelper->compareInterval( $diff, $maximumPeriod, true ) === 1 ) {
			$errorType = Stock::MAXIMUM_PERIOD_ERROR;

			return $errorType;
		}
		if ( $currentQuantity > $availableQuantity && ! $this->calendarHelper->allowOverbooking() ) {
			$errorType = Stock::NOT_ENOUGH_QUANTITY_ERROR;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $baseInventory, function ( $dateElem ) use ( $currentQuantity, $availableQuantity, $sendDate, $returnDate ) {
			$startDate = new \DateTime( $dateElem['s'] );
			$endDate   = new \DateTime( $dateElem['e'] );
			if ( $currentQuantity > - 1 && $this->dateHelper->checkDatesOverlap( $startDate, $endDate, $sendDate, $returnDate ) ) {
				if ( $currentQuantity > $availableQuantity - $dateElem['q'] ) {
					return true;
				}
			}
		} );
		if ( null !== $findResult && $findResult !== false && ! $this->calendarHelper->allowOverbooking() ) {
			$errorType = Stock::BOOKED_DATES_ERROR;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDates, function ( $dateElem ) use ( $fromDate ) {
			$startDate = new \DateTime( $dateElem['s'] );
			$endDate   = new \DateTime( $dateElem['e'] );
			$endDate->sub( new \DateInterval( 'PT1S' ) );

			return $this->dateHelper->isRecurringDateBetween( $startDate, $endDate, $fromDate, $dateElem['r'] );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::START_DATE_DISABLED_ERROR;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDates, function ( $dateElem ) use ( $toDate ) {
			$startDate = new \DateTime( $dateElem['s'] );
			$endDate   = new \DateTime( $dateElem['e'] );
			$endDate->sub( new \DateInterval( 'PT1S' ) );

			return $this->dateHelper->isRecurringDateBetween( $startDate, $endDate, $toDate, $dateElem['r'] );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::END_DATE_DISABLED_ERROR;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDatesFull, function ( $dateElem ) use ( $fromDate ) {
			$newDate = new \DateTime( $dateElem['s'] );

			return $this->dateHelper->isRecurringDate( $newDate, $fromDate, $dateElem['r'] );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::START_DATE_DISABLED_ERROR_FULL;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDatesFull, function ( $dateElem ) use ( $toDate ) {
			$newDate = new \DateTime( $dateElem['s'] );

			return $this->dateHelper->isRecurringDate( $newDate, $toDate, $dateElem['r'] );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::END_DATE_DISABLED_ERROR_FULL;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDaysWeekStart, function ( $day ) use ( $fromDate ) {
			return ( $day - 1 ) === (int) $fromDate->format( 'w' );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::START_DATE_DISABLED_ERROR;

			return $errorType;
		}
		$findResult = \Underscore\Types\Arrays::find( $disabledDaysWeekEnd, function ( $day ) use ( $toDate ) {
			return ( $day - 1 ) === (int) $toDate->format( 'w' );
		} );
		if ( null !== $findResult && $findResult !== false ) {
			$errorType = Stock::END_DATE_DISABLED_ERROR;

			return $errorType;
		}

		return $errorType;
	}

	/**
	 * Function return the first available date for the product.
	 *
	 * This is a highly intensive operation because if time increments are used along with minimum periods and hours are used
	 * is checking hour per hour to see what is the next available interval for the minimum period. There is no fast way to check
	 *
	 * @param null $product
	 * @param bool $format
	 *
	 * @return \DateTime
	 *
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \RuntimeException
	 */
	public function getFirstDateAvailable( $product = null, $format = true ) {
		$today = $this->calendarHelper->getTimeAccordingToTimeZone();


		$todayWithTime   = $today;
		$hoursForNextDay = $this->calendarHelper->getNextDayHour();
		$todayCurrent    = new \DateTime( $today->format( 'Y-m-d' ) );
		$todayNoTime     = new \DateTime( $today->format( 'Y-m-d' ) );
		if ( $hoursForNextDay ) {
			$todayCurrent->setTime( $hoursForNextDay[0], $hoursForNextDay[1], $hoursForNextDay[2] );
		} else {
			$todayCurrent->setTime( 23, 59, 59 );
		}
		$today = $todayNoTime;
		if ( $this->dateHelper->compareDateTimeObj( $todayWithTime, $todayCurrent, true ) >= 0 ) {
			$today = $todayNoTime->add( new \DateInterval( 'P1D' ) );
		}

		if ( null === $product || ! $this->rentalHelper->isRentalTypeSimple( $product ) ) {
			return $today->format( 'Y-m-d' );
		}

		$turnoverBefore = $this->calendarHelper->getTurnoverBefore( $product );
		$padding        = $this->calendarHelper->getPadding( $product );
		/** @var Period $periodToday */
		$periodToday = Period::createFromDuration( $today, 60 );

		if ( $this->dateHelper->compareInterval( $padding, '1d' ) > - 1 ) {
			$dateInterval = $this->dateHelper->normalizeInterval( $padding );
			$periodToday  = $periodToday->move( $dateInterval );
			if ( $this->dateHelper->compareInterval( $turnoverBefore, $padding ) === 1 ) {
				$differencePaddingTurnover = $this->calendarHelper->stringPeriodToMinutes( $turnoverBefore ) -
				                             $this->calendarHelper->stringPeriodToMinutes( $padding );
				$periodToday               = $periodToday->move( 60 * $differencePaddingTurnover );
			}
		}
		$iVal  = 0;
		$dates = new DataObject();

		while ( true ) {
			if ( $format === false && $this->calendarHelper->useTimes( $product ) ) {
				$periodToday = $this->getFirstTimeAvailable( $product, false, $periodToday->getStartDate() );
			}

			$dates->setStartDate( $periodToday->getStartDate() );
			$dates->setEndDate( $periodToday->getEndDate() );
			$dates->setStartDateWithTurnover( $this->calendarHelper->getSendDate( $product, $periodToday->getStartDate() ) );
			$dates->setEndDateWithTurnover( $this->calendarHelper->getReturnDate( $product, $periodToday->getEndDate() ) );
			$dates->setWithoutMinimumPeriod( true );
			$checkInterval = $this->checkIntervalValid( $product, $dates, 1 );
			if ( $checkInterval !== Stock::NO_ERROR && $checkInterval !== Stock::END_DATE_DISABLED_ERROR && $checkInterval !== Stock::START_DATE_DISABLED_ERROR ) {
				$dateInterval = $this->dateHelper->normalizeInterval( '1d' );
				$periodToday  = $periodToday->move( $dateInterval );
			} else {
				break;
			}
			++ $iVal;
			if ( $iVal > 200 ) {
				$logger = \Magento\Framework\App\ObjectManager::getInstance()->get( 'Magento\Framework\Logger\Monolog' );
				$logger->pushHandler( new \Monolog\Handler\StreamHandler( BP . '/var/log/pprlog.log' ) );
				$logger->addDebug( 'Might be infinite loop stock class ' );
				break;
			}
		}
		if ( $format ) {
			return $periodToday->getStartDate()->format( 'Y-m-d' );
		} else {
			return $periodToday;
		}
	}

	/**
	 * Function return the first available time for the first available date.
	 *
	 * todo should take into account store hours to be more specific
	 *
	 * @param null           $product
	 * @param bool           $format
	 * @param \DateTime|null $today
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \RuntimeException
	 */
	public function getFirstTimeAvailable( $product = null, $format = true, $today = null ) {
		$currentDay = $this->calendarHelper->getTimeAccordingToTimeZone();
		$currentDay = new \DateTime( $currentDay->format( 'Y-m-d H:i:s' ) );

		if ( $today === null || ! $this->rentalHelper->isRentalTypeSimple( $product ) || $this->dateHelper->compareDateTimeObj( $today, $currentDay, false ) === 0 ) {
			$today = $currentDay;
		}

		if ( null === $product ) {
			return $today->format( 'Y-m-d' );
		}
		$turnoverBefore           = $this->calendarHelper->getTurnoverBefore( $product );
		$padding                  = $this->calendarHelper->getPadding( $product );
		$minimumPeriod            = $this->calendarHelper->getMinimumPeriod( $product );
		$minimumDurationInSeconds = 60;
		if ( $format === false ) {
			if ( $this->dateHelper->compareInterval( $minimumPeriod, '0d' ) !== 0 ) {
				$minimumDurationInSeconds = $this->calendarHelper->stringPeriodToMinutes( $minimumPeriod ) * 60 + 60;
			}
			if ( $minimumDurationInSeconds < 60 * $this->calendarHelper->timeIncrement() ) {
				$minimumDurationInSeconds = 60 * $this->calendarHelper->timeIncrement();
			}
		}
		/** @var Period $periodToday */
		$periodToday = Period::createFromDuration( $today, $minimumDurationInSeconds );

		$minimumDurationInSecondsForTimes = 60 * $this->calendarHelper->timeIncrement();
		if ( $this->dateHelper->compareInterval( $padding, '1d' ) === - 1 ) {
			$dateInterval = $this->dateHelper->normalizeInterval( $padding );
			$periodToday  = $periodToday->move( $dateInterval );
			if ( $this->dateHelper->compareInterval( $turnoverBefore, $padding ) === 1 && $this->dateHelper->compareInterval( $turnoverBefore, '1d' ) === - 1 ) {
				$differencePaddingTurnover = $this->calendarHelper->stringPeriodToMinutes( $turnoverBefore ) -
				                             $this->calendarHelper->stringPeriodToMinutes( $padding );
				$periodToday               = $periodToday->move( 60 * $differencePaddingTurnover );
			}
		}

		$iVal  = 0;
		$dates = new DataObject();

		while ( true ) {
			$dates->setStartDate( $periodToday->getStartDate() );
			$dates->setEndDate( $periodToday->getEndDate() );
			$dates->setStartDateWithTurnover( $this->calendarHelper->getSendDate( $product, $periodToday->getStartDate() ) );
			$dates->setEndDateWithTurnover( $this->calendarHelper->getReturnDate( $product, $periodToday->getEndDate() ) );
			$checkInterval = $this->checkIntervalValid( $product, $dates, 1 );
			if ( $checkInterval !== Stock::NO_ERROR && $checkInterval !== Stock::END_DATE_DISABLED_ERROR && $checkInterval !== Stock::START_DATE_DISABLED_ERROR ) {
				$periodToday = $periodToday->move( $minimumDurationInSecondsForTimes );
			} else {
				break;
			}

			if ( $format === true && $this->dateHelper->compareDateTimeObj( $today, $periodToday->getStartDate(), false ) !== 0 ) {
				break;
			}
			++ $iVal;
			if ( $iVal > 10000 ) {
				$logger = \Magento\Framework\App\ObjectManager::getInstance()->get( 'Magento\Framework\Logger\Monolog' );
				$logger->pushHandler( new \Monolog\Handler\StreamHandler( BP . '/var/log/pprlog.log' ) );
				$logger->addDebug( 'Might be infinite loop stock class ' );
				break;
			}
		}
		if ( $format ) {
			if ( $this->calendarHelper->timeTypeAmpm() ) {
				return $periodToday->getStartDate()->format( 'h:i a' );
			} else {
				return $periodToday->getStartDate()->format( 'H:i' );
			}
		} else {
			return $periodToday;
		}
	}

	/**
	 * Combines a new reservation with the existing reservations for the serialized inventory field
	 * This function just returns how the new Inventory will look like but does not make any updates to the real inventory
	 * For Maintenance when no start end dates should be a plugin to getSirentQuantity to subtract 1.
	 *
	 * @param           $productId
	 * @param \DateTime $startDateWithTurnover
	 * @param \DateTime $endDateWithTurnover
	 * @param int       $qty
	 * @param int       $qtyCancel
	 * @param int       $orderId
	 * @param null      $baseInventory
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getUpdatedInventory( $productId, $startDateWithTurnover, $endDateWithTurnover, $qty, $qtyCancel = 0, $orderId = 0, $baseInventory = null ) {
		try {
			$product = $this->productRepository->getById( $productId );
		} catch ( NoSuchEntityException $e ) {
			return [];
		}
		if ( $qtyCancel > 0 ) {
			$qty = - $qtyCancel;
		}
		$qty              = (int) $qty;
		$currentInventory = $baseInventory;
		if ( null === $baseInventory ) {
			$currentInventory = $this->getInventoryTable( $product );
		}
		if ( empty( $endDateWithTurnover ) || $endDateWithTurnover === '0000-00-00 00:00:00' ) {
			return $currentInventory;
		}

		/** @var array $updatedInventory */
		$updatedInventory = [];
		/** @var Period $toAddPeriod */
		$toAddPeriod = new Period( $startDateWithTurnover, $endDateWithTurnover );
		/** @var array $currentInventory */
		if ( count( $currentInventory ) === 0 ) {
			/*
			 * Inventory is formed from an array like
			 * 's' -> start date , 'e' -> end date , 'q' -> quantity and o-> orders
			 * 's'-> 'e' represents the interval where quantity is 'q' implied by the orders 'o'
			 * we have an array of intervals and we intersect them. The intersection will increase the qty reserved.
			 * And the difference will keep the same qty.
			 */

			$updatedInventory[] = [
				'q' => $qty,
				's' => $toAddPeriod->getStartDate()->format( 'Y-m-d H:i' ),
				'e' => $toAddPeriod->getEndDate()->format( 'Y-m-d H:i' ),
			];
		}
		$overlaps = false;
		/** @var array $reservationObject */
		foreach ( $currentInventory as $reservationObject ) {

			/** @var Period $reservationPeriod */
			$reservationPeriod = new Period(
				$reservationObject['s'] . ':00',
				$reservationObject['e'] . ':00'
			);
			if ( $reservationPeriod->overlaps( $toAddPeriod ) ) {
				/*
				 * We first intersect the periods if they overlaps
				 * This means that on the intersected periods the qty will increase
				 */

				//5-6(1) 7-9(1)   ---->3-10(1)
				//3-5(1) 5-6(2) 6-10(1) 7-9(2) 6-7(1) 9-10(1)

				//3-5(1) 5-6(2) 7-9(2) 9-10(1) --->4-11
				/** @var Period $intersectionPeriod */
				$intersectionPeriod = $reservationPeriod->intersect( $toAddPeriod );

				$updatedInventory[] = [
					'q' => $reservationObject['q'] + $qty,
					's' => $intersectionPeriod->getStartDate()->format( 'Y-m-d H:i' ),
					'e' => $intersectionPeriod->getEndDate()->format( 'Y-m-d H:i' ),
				];

				/*
				 * We make the difference of the periods for the non overlapping ones
				 * The reserved qtys will be the same
				 */
				$diffPeriodArray = $reservationPeriod->diff( $toAddPeriod );
				/** @var Period $diffPeriod */
				foreach ( $diffPeriodArray as $diffPeriod ) {
					$updatedInventory[] = [
						'q' => $qty,
						's' => $diffPeriod->getStartDate()->format( 'Y-m-d H:i' ),
						'e' => $diffPeriod->getEndDate()->format( 'Y-m-d H:i' ),
					];
				}
				$overlaps = true;
			} else {
				$updatedInventory[] = [
					'q' => $reservationObject['q'],
					's' => $reservationPeriod->getStartDate()->format( 'Y-m-d H:i' ),
					'e' => $reservationPeriod->getEndDate()->format( 'Y-m-d H:i' ),
				];
			}
		}
		if ( ! $overlaps ) {
			$updatedInventory[] = [
				'q' => $qty,
				's' => $toAddPeriod->getStartDate()->format( 'Y-m-d H:i' ),
				'e' => $toAddPeriod->getEndDate()->format( 'Y-m-d H:i' ),
			];
		}
		$updatedInventory = $this->stock->normalizeInventory( $updatedInventory );
		$updatedInventory = $this->stock->compactInventory( $updatedInventory );

		return $updatedInventory;
	}

	private function getNonEndDateQtyForProduct( $product ) {
		$productId = $this->rentalHelper->getProductIdFromObject( $product );
		$qty       = 0;
		$this->searchCriteriaBuilder->addFilter( 'main_table.product_id', $productId );
		$this->searchCriteriaBuilder->addFilter( 'qty_use_grid', 0, 'gt' );
		$this->searchCriteriaBuilder->addFilter( 'end_date_with_turnover', '0000-00-00 00:00:00', 'eq' );

		$criteria = $this->searchCriteriaBuilder->create();
		/** @var array $items */
		$items = $this->reservationOrdersRepository->getList( $criteria )->getItems();
		foreach ( $items as $item ) {
			$qty += (int) $item->getQtyUseGrid();
		}

		return $qty;
	}

	/**
	 * @param \Magento\Sales\Api\Data\OrderItemInterface $item
	 * @param \Magento\Sales\Api\Data\OrderInterface     $order
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function reserveDisableShipping( $item, $order ) {
		if ( $item->getIsVirtual() ) {
			$item->setIsVirtual( 0 );
			$item->setFreeShipping( 1 );
			$this->orderItemRepository->save( $item );
		}
		if ( $item->getParentItem() && $item->getParentItem()->getIsVirtual() ) {
			$item->getParentItem()->setIsVirtual( 0 );
			$item->getParentItem()->setFreeShipping( 1 );
			$this->orderItemRepository->save( $item->getParentItem() );
		}
	}
}
