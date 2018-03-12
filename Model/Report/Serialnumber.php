<?php

namespace SalesIgniter\Rental\Model\Report;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\UrlInterface as UrlBuilder;
use SalesIgniter\Rental\Api\StockManagementInterface as StockManagement;
use SalesIgniter\Rental\Helper\Report as ReportHelper;
use SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\Collection as SerialDetailsCollection;
use SalesIgniter\Rental\Model\SerialNumberDetailsFactory as SerialNumberFactory;

class Serialnumber {

	/**
	 * @var UrlBuilder
	 */
	protected $_urlBuilder;

	/**
	 * @var ProductCollection|Product[]
	 */
	protected $_collection;

	/**
	 * @var RentalStock
	 */
	protected $_rentalStock;

	/**
	 * @var string
	 */
	protected $_rendererName;

	/**
	 * @var
	 */
	protected $_dateTo;

	/**
	 * @var
	 */
	protected $_dateFrom;

	/**
	 * @var \Magento\Framework\App\RequestInterface
	 */
	protected $_request;

	/**
	 * @var ReportHelper
	 */
	protected $_reportHelper;

	/**
	 * @var StockManagement
	 */
	private $_stockManagement;

	/**
	 * @var SerialNumberFactory
	 */
	private $_serialNumberFactory;

	/**
	 * @var mixed
	 */
	protected $_serialNumberFilter = null;
	/**
	 * Instance of application resource.
	 *
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $resource;
	/**
	 * @var \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\Collection
	 */
	private $serialDetailsColection;
	/**
	 * @var \Magento\Catalog\Api\ProductRepositoryInterface
	 */
	private $productRepository;

	/**
	 * Serialnumber constructor.
	 *
	 * @param UrlBuilder                                                              $urlBuilder
	 * @param \Magento\Framework\App\ResourceConnection                               $resource
	 * @param ProductCollection                                                       $ProductCollection
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface                         $productRepository
	 * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\Collection $serialDetailsCollection
	 * @param StockManagement                                                         $StockManagement
	 * @param SerialNumberFactory                                                     $SerialNumberFactory
	 * @param ReportHelper                                                            $ReportHelper
	 */
	public function __construct(
		UrlBuilder $urlBuilder,
		\Magento\Framework\App\ResourceConnection $resource,
		ProductCollection $ProductCollection,
		ProductRepositoryInterface $productRepository,
		SerialDetailsCollection $serialDetailsCollection,
		StockManagement $StockManagement,
		SerialNumberFactory $SerialNumberFactory,
		ReportHelper $ReportHelper
	) {
		$this->_urlBuilder = $urlBuilder;
		$this->resource    = $resource;
		$this->_collection = $serialDetailsCollection;
		$this->_collection->getSelect()->where( 'type_id = ?', \SalesIgniter\Rental\Model\Product\Type\Sirent::TYPE_RENTAL );
		$this->_collection->getSelect()->joinLeft( [ 'product_table' => $this->resource->getTableName( 'catalog_product_entity' ) ], 'main_table.product_id = product_table.entity_id' );

		$this->_reportHelper        = $ReportHelper;
		$this->_stockManagement     = $StockManagement;
		$this->_serialNumberFactory = $SerialNumberFactory;
		$this->productRepository    = $productRepository;
	}

	/**
	 * @return \Magento\Catalog\Model\Product[]|ProductCollection
	 */
	public function getCollection() {
		return $this->_collection;
	}

	/**
	 * @param $Collection
	 *
	 * @return $this
	 */
	public function setCollection( $Collection ) {
		$this->_collection = $Collection;

		return $this;
	}

	public function getRendererCode() {
		return $this->getRequest()->getParam( 'rendererCode', 'month' );
	}

	/**
	 * @param $DateFrom
	 *
	 * @return $this
	 */
	public function setDateFrom( $DateFrom ) {
		$this->_dateFrom = $DateFrom;

		return $this;
	}

	/**
	 * @param $DateTo
	 *
	 * @return $this
	 */
	public function setDateTo( $DateTo ) {
		$this->_dateTo = $DateTo;

		return $this;
	}

	/**
	 * @param \Magento\Framework\App\RequestInterface $Request
	 *
	 * @return $this
	 */
	public function setRequest( \Magento\Framework\App\RequestInterface $Request ) {
		$this->_request = $Request;

		return $this;
	}

	/**
	 * @return \Magento\Framework\App\RequestInterface
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * @return array
	 */
	public function getData() {
		$RequestParams = $this->getRequest()->getParams();
		$this->setDateFrom( $this->getRequest()
		                         ->getParam( 'dateFrom', date( 'Y-m-d H:i:s', mktime( 0, 0, 0, date( 'm' ), 1, date( 'Y' ) ) ) ) );
		$this->setDateTo( $this->getRequest()
		                       ->getParam( 'dateTo', date( 'Y-m-d H:i:s', mktime( 23, 59, 59, date( 'm' ) + 1, 0, date( 'Y' ) ) ) ) );

		$DataArray = [
			'calendar' => [
				'dateDataUrl'        => $this->_urlBuilder->getUrl( '*/report_serialnumber/getDateReportData' ),
				'dateDataUrlProduct' => $this->_urlBuilder->getUrl( '*/report_inventory/getDateReportData' ),
				'rendererCode'       => $this->getRendererCode()
			],
			'products' => []
		];
		/**
		 * Filtering the number of serials per page is impossible
		 * So we set an infinite size and then filter the return data. Which is not so correct
		 */
		$serialsData = $this->getSerialsData();
		$serialData  = [];
		foreach ( $serialsData as $serialName => $sData ) {
			$serialData[ $sData['id'] ][] = $sData;
		}
		foreach ( $serialData as $productId => $sData ) {
			$DataArray['products'][] = [
				'id'              => $sData[0]['id'],
				'sku'             => $sData[0]['sku'],
				'name'            => $sData[0]['name'],
				'sirent_quantity' => $sData[0]['sirent_quantity'],
				'availability'    => $sData[0]['availability'],
				'serial_numbers'  => $sData
			];
		}

		return $DataArray;
	}

	public function applyFilters() {
		$Filters = $this->getRequest()->getParam( 'filter', null );
		if ( $Filters ) {
			if ( isset( $Filters['option'] ) && empty( $Filters['option'] ) === false ) {
				foreach ( $Filters['option'] as $FilterName ) {
					switch ( $FilterName ) {
						case 'product_name':
							$this->_collection->addAttributeToFilter( 'name', [ 'like' => '%' . $Filters['text'] . '%' ] );
							break;
						case 'product_sku':
							$this->_collection->addFieldToFilter( 'sku', [ 'like' => '%' . $Filters['text'] . '%' ] );
							break;
						case 'serial_number':
							$this->_serialNumberFilter = $Filters['text'];
							$this->_collection->addFieldToFilter( 'serialnumber', [ 'like' => '%' . $this->_serialNumberFilter . '%' ] );
							break;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @param $Timestamp
	 *
	 * @return \DateTime
	 */
	protected function getDateTimeObj( $Timestamp ) {
		$DateTime = new \DateTime();
		$DateTime->setTimezone( new \DateTimeZone( 'UTC' ) );
		$DateTime->setTimestamp( $Timestamp );

		return $DateTime;
	}

	/**
	 * @param $Product
	 *
	 * @return array
	 */
	protected function getAvailabilityDates( $Product ) {
		$Availabilites = [];

		$HourTime = ( 60 * 60 );
		$DayTime  = ( $HourTime * 24 );
		$WeekTime = ( $DayTime * 7 );

		$StartDate = $this->getDateTimeObj( strtotime( $this->_dateFrom ) );
		$StartDate->setTime( 0, 0, 0 );

		$EndDate = $this->getDateTimeObj( strtotime( $this->_dateTo ) );
		$EndDate->setTime( 23, 59, 59 );

		if ( $this->getRendererCode() == 'day' ) {
			$CurrentHour = 0;
			for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $HourTime ) {
				$_checkStartDate = $this->getDateTimeObj( $i );
				$_checkStartDate->setTime( $CurrentHour, 0, 0 );

				$_checkEndDate = $this->getDateTimeObj( $i );
				$_checkEndDate->setTime( $_checkStartDate->format( 'H' ), 59, 59 );

				$Availabilites[ $i ] = [
					'from'   => $_checkStartDate->format( 'Y-m-d H:i:s' ),
					'to'     => $_checkEndDate->format( 'Y-m-d H:i:s' ),
					'result' => $this->_stockManagement->getAvailableQuantity( $Product, $_checkStartDate, $_checkEndDate )
				];

				$CurrentHour ++;
				if ( $CurrentHour > 23 ) {
					$CurrentHour = 0;
				}
			}
		} elseif ( $this->getRendererCode() == 'week' ) {
			for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime ) {
				$_checkStartDate = $this->getDateTimeObj( $i );
				$_checkStartDate->setTime( 0, 0, 0 );

				$_checkEndDate = $this->getDateTimeObj( $i );
				$_checkEndDate->setTime( 23, 59, 59 );

				$Availabilites[ $i ] = [
					'from'   => $_checkStartDate->format( 'Y-m-d H:i:s' ),
					'to'     => $_checkEndDate->format( 'Y-m-d H:i:s' ),
					'result' => $this->_stockManagement->getAvailableQuantity( $Product, $_checkStartDate, $_checkEndDate )
				];
			}
		} elseif ( $this->getRendererCode() == 'month' ) {
			for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime ) {
				$_checkStartDate = $this->getDateTimeObj( $i );
				$_checkStartDate->setTime( 0, 0, 0 );

				$_checkEndDate = $this->getDateTimeObj( $i );
				$_checkEndDate->setTime( 23, 59, 59 );

				$Availabilites[ $i ] = [
					'from'   => $_checkStartDate->format( 'Y-m-d H:i:s' ),
					'to'     => $_checkEndDate->format( 'Y-m-d H:i:s' ),
					'result' => $this->_stockManagement->getAvailableQuantity( $Product, $_checkStartDate, $_checkEndDate )
				];
			}
		}

		return $Availabilites;
	}

	public function getSerialsData() {
		$SerialsData = [];

		$Availabilites = [];

		$HourTime = ( 60 * 60 );
		$DayTime  = ( $HourTime * 24 );
		$WeekTime = ( $DayTime * 7 );

		$StartDate = $this->getDateTimeObj( strtotime( $this->_dateFrom ) );
		$StartDate->setTime( 0, 0, 0 );

		$EndDate = $this->getDateTimeObj( strtotime( $this->_dateTo ) );
		$EndDate->setTime( 23, 59, 59 );

		foreach ( $this->_collection as $SerialNumber ) {
			$Product = $this->productRepository->getById( $SerialNumber->getProductId(), false, 0 );
			if ( $this->getRendererCode() == 'day' ) {
				$CurrentHour = 0;
				for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $HourTime ) {
					$_checkStartDate = $this->getDateTimeObj( $i );
					$_checkStartDate->setTime( $CurrentHour, 0, 0 );

					$_checkEndDate = $this->getDateTimeObj( $i );
					$_checkEndDate->setTime( $_checkStartDate->format( 'H' ), 59, 59 );

					$Availabilites[ $i ] = [
						'from'                => $_checkStartDate->format( 'Y-m-d H:i:s' ),
						'to'                  => $_checkEndDate->format( 'Y-m-d H:i:s' ),
						'result'              => 'available',
						'reservationorder_id' => null
					];

					$CurrentHour ++;
					if ( $CurrentHour > 23 ) {
						$CurrentHour = 0;
					}
				}
			} elseif ( $this->getRendererCode() == 'week' ) {
				for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime ) {
					$_checkStartDate = $this->getDateTimeObj( $i );
					$_checkStartDate->setTime( 0, 0, 0 );

					$_checkEndDate = $this->getDateTimeObj( $i );
					$_checkEndDate->setTime( 23, 59, 59 );

					$Availabilites[ $i ] = [
						'from'                => $_checkStartDate->format( 'Y-m-d H:i:s' ),
						'to'                  => $_checkEndDate->format( 'Y-m-d H:i:s' ),
						'result'              => 'available',
						'reservationorder_id' => null
					];
				}
			} elseif ( $this->getRendererCode() == 'month' ) {
				for ( $i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime ) {
					$_checkStartDate = $this->getDateTimeObj( $i );
					$_checkStartDate->setTime( 0, 0, 0 );

					$_checkEndDate = $this->getDateTimeObj( $i );
					$_checkEndDate->setTime( 23, 59, 59 );

					$Availabilites[ $i ] = [
						'from'                => $_checkStartDate->format( 'Y-m-d H:i:s' ),
						'to'                  => $_checkEndDate->format( 'Y-m-d H:i:s' ),
						'result'              => 'available',
						'reservationorder_id' => null
					];
				}
			}

			$SerialsData[ $SerialNumber->getSerialnumber() ] = [
				'id'              => $Product->getId(),
				'sku'             => $Product->getSku(),
				'name'            => $Product->getName(),
				'sirent_quantity' => $Product->getSirentQuantity(),
				'availability'    => $this->getAvailabilityDates( $Product ),
				'serial_number'   => $SerialNumber->getSerialnumber(),
				'notes'           => $SerialNumber->getNotes(),
				'cost'            => $SerialNumber->getCost(),
				'date_acquired'   => $SerialNumber->getDateAcquired(),
				'status'          => $Availabilites
			];
		}

		if ( empty( $SerialsData ) ) {
			return false;
		}

		$Reservations = $this->_reportHelper->getRentalOrders( [
			'use_turnover_date' => true,
			'start_date'        => $StartDate,
			'end_date'          => $EndDate,
			'conditions'        => [
				'serials_shipped' => [ 'null' => false ],
				// 'qty_use_grid' => ['gt' => 0]
			]
		] );

		foreach ( $Reservations as $Reservation ) {
			$ReservationStartDate = $this->getDateTimeObj( strtotime( $Reservation['start_date_use_grid'] ) );
			$ReservationStartDate->setTime( 0, 0, 0 );

			$ReservationEndDate = $this->getDateTimeObj( strtotime( $Reservation['end_date_use_grid'] ) );
			$ReservationEndDate->setTime( 0, 0, 0 );

			$ShippedSerials = explode( ',', $Reservation['serials_shipped'] );
			foreach ( $ShippedSerials as $SerialNumber ) {
				if ( isset( $SerialsData[ $SerialNumber ] ) ) {
					if ( $this->getRendererCode() == 'day' ) {
						for ( $i = $ReservationStartDate->getTimestamp(); $i < $ReservationEndDate->getTimestamp(); $i += $HourTime ) {
							$SerialsData[ $SerialNumber ]['status'][ $i ]['result']              = 'out';
							$SerialsData[ $SerialNumber ]['status'][ $i ]['reservationorder_id'] = $Reservation['reservationorder_id'];
						}
					} elseif ( $this->getRendererCode() == 'week' ) {
						for ( $i = $ReservationStartDate->getTimestamp(); $i < $ReservationEndDate->getTimestamp(); $i += $DayTime ) {
							$SerialsData[ $SerialNumber ]['status'][ $i ]['result']              = 'out';
							$SerialsData[ $SerialNumber ]['status'][ $i ]['reservationorder_id'] = $Reservation['reservationorder_id'];
						}
					} elseif ( $this->getRendererCode() == 'month' ) {
						for ( $i = $ReservationStartDate->getTimestamp(); $i < $ReservationEndDate->getTimestamp(); $i += $DayTime ) {
							$SerialsData[ $SerialNumber ]['status'][ $i ]['result']              = 'out';
							$SerialsData[ $SerialNumber ]['status'][ $i ]['reservationorder_id'] = $Reservation['reservationorder_id'];
						}
					}
				}
			}
		}

		sort( $SerialsData );

		return $SerialsData;
	}

	/**
	 * @param $timestamp
	 *
	 * @return bool|string
	 */
	protected function getDateFormatted( $timestamp ) {
		return date( 'm/d', $timestamp );
	}
}
