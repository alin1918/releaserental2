<?php

namespace SalesIgniter\Rental\Helper;

use League\Period\Period;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Backend\PeriodType;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;

/**
 * Calendar Helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 */
class Calendar extends \Magento\Framework\App\Helper\AbstractHelper {
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Catalog\Model\Session
	 */
	protected $catalogSession;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product
	 */
	protected $_resourceProduct;

	/**
	 * @var \Magento\Framework\App\State
	 */
	protected $_appState;

	/**
	 * @var ProductRepositoryInterface
	 */
	protected $_productRepository;

	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	protected $helperRental;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $_localeDate;

	/**
	 * @var DateTimeFormatterInterface
	 */
	protected $dateTimeFormatter;
	/**
	 * @var \Magento\Framework\Locale\ResolverInterface
	 */
	protected $localeResolver;
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $datetime;
	/**
	 * @var \SalesIgniter\Rental\Helper\Date
	 */
	protected $dateHelper;
	/**
	 * @var \Magento\Sales\Model\OrderRepository
	 */
	protected $orderRepository;
	/**
	 * @var \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface
	 */
	private $fixedRentalDatesRepository;
	/**
	 * @var \Magento\Framework\Api\SearchCriteriaBuilder
	 */
	private $searchCriteriaBuilder;
	/**
	 * @var \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface
	 */
	private $fixedRentalNamesRepository;
	/**
	 * @var \Magento\Framework\Pricing\PriceCurrencyInterface
	 */
	private $priceCurrency;

	/**
	 * @var \Magento\Catalog\Model\ProductFactory
	 */
	private $productFactory;

	/**
	 * Calendar constructor.
	 *
	 * @param \Magento\Framework\App\Helper\Context                $context
	 * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
	 * @param \Magento\Catalog\Model\Session                       $catalogSession
	 * @param \Magento\Catalog\Model\ResourceModel\Product         $resourceProduct
	 * @param \Magento\Framework\Registry                          $coreRegistry
	 * @param \Magento\Framework\App\State                         $appState
	 * @param ProductRepositoryInterface                           $productRepository
	 * @param OrderRepository                                      $orderRepository
	 * @param PriceCurrencyInterface                               $priceCurrency
	 * @param FixedRentalDatesRepositoryInterface                  $fixedRentalDatesRepository
	 * @param FixedRentalNamesRepositoryInterface                  $fixedRentalNamesRepository
	 * @param Data                                                 $helperRental
	 * @param SearchCriteriaBuilder                                $searchCriteriaBuilder
	 * @param Date                                                 $dateHelper
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 * @param DateTimeFormatterInterface                           $dateTimeFormatter
	 * @param \Magento\Framework\Locale\ResolverInterface          $localeResolver
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime          $datetime
	 * @param \Magento\Catalog\Model\ProductFactory                $productFactory
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Session $catalogSession,
		\Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\App\State $appState,
		ProductRepositoryInterface $productRepository,
		OrderRepository $orderRepository,
		PriceCurrencyInterface $priceCurrency,
		FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository,
		FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository,
		\SalesIgniter\Rental\Helper\Data $helperRental,
		SearchCriteriaBuilder $searchCriteriaBuilder,
		\SalesIgniter\Rental\Helper\Date $dateHelper,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		DateTimeFormatterInterface $dateTimeFormatter,
		\Magento\Framework\Locale\ResolverInterface $localeResolver,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magento\Catalog\Model\ProductFactory $productFactory
	) {
		$this->_storeManager      = $storeManager;
		$this->catalogSession     = $catalogSession;
		$this->_coreRegistry      = $coreRegistry;
		$this->_resourceProduct   = $resourceProduct;
		$this->_appState          = $appState;
		$this->_productRepository = $productRepository;
		$this->helperRental       = $helperRental;
		$this->_localeDate        = $localeDate;
		$this->localeResolver     = $localeResolver;
		$this->dateTimeFormatter  = $dateTimeFormatter;
		parent::__construct( $context );
		$this->localeResolver             = $localeResolver;
		$this->datetime                   = $datetime;
		$this->dateHelper                 = $dateHelper;
		$this->orderRepository            = $orderRepository;
		$this->fixedRentalDatesRepository = $fixedRentalDatesRepository;
		$this->searchCriteriaBuilder      = $searchCriteriaBuilder;
		$this->fixedRentalNamesRepository = $fixedRentalNamesRepository;
		$this->priceCurrency              = $priceCurrency;
		$this->productFactory             = $productFactory;
	}

	/**
	 * Turnovers With Legend on calendar.
	 *
	 * @return bool
	 */
	public function showTurnovers() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/turnover/show_turnover',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Keep Selected Dates.
	 *
	 * @return bool
	 */
	public function keepSelectedDates() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/calendar_options/keep_selected_dates',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function sameDatesEnforce() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/calendar_options/same_dates_enforce',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function globalDatesPricingOnListing() {
		return $this->scopeConfig->getValue(
			'salesigniter_rental/calendar_options/global_dates_pricing_on_listing',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Function to get time increment for the hours dropdown.
	 *
	 * @return int
	 */
	public function timeIncrement() {
		return (int) $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/time_increment',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * @param string $type
	 * @param string $day
	 * @param string $hoursStart
	 * @param string $hoursEnd
	 *
	 * @return string
	 */
	private function storeHoursPerDay( $type, $day, $hoursStart, $hoursEnd ) {
		$hours = $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/store_' . $type . '_time_' . $day,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		if ( $hours == '00,00,00' || $hours === null ) {
			$hours = $hoursEnd;
			if ( $type == 'open' ) {
				$hours = $hoursStart;
			}
		}

		return $hours;
	}

	/**
	 * @return string
	 */
	public function getCalendarTimeFormat() {
		$timeFormat = 'HH:mm';
		if ( $this->timeTypeAmpm() ) {
			$timeFormat = 'hh:mm tt';

			return $timeFormat;
		}

		return $timeFormat;
	}

	/**
	 * Function to normalize an hour base on settings
	 * Accepts array in.
	 *
	 * @param array $hours
	 * @param bool  $forceNotAmPm
	 *
	 * @return array|string
	 */
	public function normalizeHours( $hours, $forceNotAmPm = false ) {
		$timeTypeAmpm = false;
		if ( ! $forceNotAmPm ) {
			$timeTypeAmpm = $this->timeTypeAmpm();
		}

		if ( is_array( $hours ) ) {
			foreach ( $hours as $k => $v ) {
				if ( is_array( $v ) ) {
					$hours[ $k ] = $this->normalizeHours( $v, $forceNotAmPm );
				} else {
					$formattingDate = new \DateTime( '2016-07-19 ' . str_replace( ',', ':', $v ) );
					if ( $timeTypeAmpm ) {
						$hours[ $k ] = $formattingDate->format( 'h:i a' );
					} else {
						$hours[ $k ] = $formattingDate->format( 'H:i' );
					}
				}
			}
		}

		return $hours;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return array
	 */
	public function storeHoursForDate( $date ) {
		$hours = $this->storeHours( true );

		return [
			'start' => $hours['start'][ strtolower( $date->format( 'l' ) ) ],
			'end'   => $hours['end'][ strtolower( $date->format( 'l' ) ) ],
		];
	}

	/**
	 * Function returns an array like
	 *  [['start' => ['monday' => '00:00', 'monday' => '00:00'],
	 *    'end'=> ['monday' => '00:00', 'monday' => '00:00']].
	 *
	 * @param bool $forceNotAmpPm
	 *
	 * @return array
	 */
	public function storeHours( $forceNotAmpPm = false ) {
		$hours          = [];
		$timeIncrement  = $this->timeIncrement();
		$hoursStart     = $this->storeHoursStart();
		$hoursEnd       = $this->storeHoursEnd( $timeIncrement );
		$hours['start'] = [
			'monday'    => $this->storeHoursPerDay( 'open', 'monday', $hoursStart, $hoursEnd ),
			'tuesday'   => $this->storeHoursPerDay( 'open', 'tuesday', $hoursStart, $hoursEnd ),
			'wednesday' => $this->storeHoursPerDay( 'open', 'wednesday', $hoursStart, $hoursEnd ),
			'thursday'  => $this->storeHoursPerDay( 'open', 'thursday', $hoursStart, $hoursEnd ),
			'friday'    => $this->storeHoursPerDay( 'open', 'friday', $hoursStart, $hoursEnd ),
			'saturday'  => $this->storeHoursPerDay( 'open', 'saturday', $hoursStart, $hoursEnd ),
			'sunday'    => $this->storeHoursPerDay( 'open', 'sunday', $hoursStart, $hoursEnd ),
		];

		$hours['end'] = [
			'monday'    => $this->storeHoursPerDay( 'close', 'monday', $hoursStart, $hoursEnd ),
			'tuesday'   => $this->storeHoursPerDay( 'close', 'tuesday', $hoursStart, $hoursEnd ),
			'wednesday' => $this->storeHoursPerDay( 'close', 'wednesday', $hoursStart, $hoursEnd ),
			'thursday'  => $this->storeHoursPerDay( 'close', 'thursday', $hoursStart, $hoursEnd ),
			'friday'    => $this->storeHoursPerDay( 'close', 'friday', $hoursStart, $hoursEnd ),
			'saturday'  => $this->storeHoursPerDay( 'close', 'saturday', $hoursStart, $hoursEnd ),
			'sunday'    => $this->storeHoursPerDay( 'close', 'sunday', $hoursStart, $hoursEnd ),
		];

		return $this->normalizeHours( $hours, $forceNotAmpPm );
	}

	/**
	 * returns theme calendar style.
	 *
	 * @return int
	 * */
	public function getThemeStyle() {
		return $this->helperRental->isFrontend() ? $this->scopeConfig->getValue(
			'salesigniter_rental/calendar_options/theme_style',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		) : \SalesIgniter\Rental\Model\Config\ThemeStyle::DEFAULT_STYLE;
	}

	/**
	 * @return mixed|string
	 */
	private function storeHoursStart() {
		$hoursStart = $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/store_open_time',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		if ( $hoursStart == '00,00,00' ) {
			$hoursStart = '00:00:00';
		}

		return $hoursStart;
	}

	/**
	 * @param $timeIncrement
	 *
	 * @return int|mixed|string
	 */
	private function storeHoursEnd( $timeIncrement ) {
		$hoursEnd = $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/store_close_time',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		if ( $hoursEnd == '00,00,00' ) {
			$hoursEnd = '23:' . ( 60 - $timeIncrement ) > 0 ? '23:' . ( 60 - $timeIncrement ) . ':00' : '23:59:00';
		}

		return $hoursEnd;
	}

	/**
	 * returns the disabled days for both start and end.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDisabledDaysWeekFrom( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$disabledDaysExcludeFrom = explode( ',', $this->scopeConfig->getValue(
				'salesigniter_rental/store_hours/disabled_days_week_from',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			) );
		} else {
			$disabledDaysExcludeFrom = explode( ',', $this->helperRental->getAttribute( $product, 'sirent_excludeddays_from' ) );
		}
		if ( ! is_array( $disabledDaysExcludeFrom ) ) {
			$disabledDaysExcludeFrom = [ $disabledDaysExcludeFrom ];
		}
		if ( in_array( '-1', $disabledDaysExcludeFrom ) ) {
			return [];
		}
		if ( in_array( (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $disabledDaysExcludeFrom ) ) {
			return $this->getDisabledDaysWeekFrom( $product, true );
		}

		return $disabledDaysExcludeFrom;
	}

	/**
	 * returns the disabled days for both start and end.
	 *
	 * @param string (calendar,price,turnover) $type
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDisabledDaysWeek( $type, $product = null, $configOnly = false ) {
		if ( $configOnly || in_array( $type, $this->getDisabledDaysWeekFrom() ) ) {
			if ( null === $product || $configOnly ) {
				$disabledDaysExclude = explode( ',', $this->scopeConfig->getValue(
					'salesigniter_rental/store_hours/disabled_days_week',
					\Magento\Store\Model\ScopeInterface::SCOPE_STORE
				) );
			} else {
				$disabledDaysExclude = explode( ',', $this->helperRental->getAttribute( $product, 'sirent_excluded_days' ) );
			}
			if ( ! is_array( $disabledDaysExclude ) ) {
				$disabledDaysExclude = [ $disabledDaysExclude ];
			}
			if ( in_array( '-1', $disabledDaysExclude ) ) {
				return [];
			}
			if ( in_array( (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $disabledDaysExclude ) ) {
				return $this->getDisabledDaysWeek( $type, $product, true );
			}

			return $disabledDaysExclude;
		}

		return [];
	}

	/**
	 * returns the disabled days for start date. has included the disabled days for both calendar.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDisabledDaysWeekStart( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$disabledDaysWeekStart = explode( ',', $this->scopeConfig->getValue(
				'salesigniter_rental/store_hours/disabled_days_week_start',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			) );
		} else {
			$disabledDaysWeekStart = explode( ',', $this->helperRental->getAttribute( $product, 'sirent_excludeddays_start' ) );
		}

		if ( ! is_array( $disabledDaysWeekStart ) ) {
			$disabledDaysWeekStart = [ $disabledDaysWeekStart ];
		}
		if ( in_array( '-1', $disabledDaysWeekStart ) ) {
			return [];
		}
		if ( in_array( (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $disabledDaysWeekStart ) ) {
			return $this->getDisabledDaysWeekStart( $product, true );
		}

		$disabledDaysWeekCalendar = $this->getDisabledDaysWeek( ExcludedDaysWeekFrom::CALENDAR, $product );

		return array_filter( array_unique( array_merge( $disabledDaysWeekCalendar, $disabledDaysWeekStart ) ) );
	}

	/**
	 * returns the disabled days for end date. has included the disabled days for both calendar.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDisabledDaysWeekEnd( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$disabledDaysWeekEnd = explode( ',', $this->scopeConfig->getValue(
				'salesigniter_rental/store_hours/disabled_days_week_end',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			) );
		} else {
			$disabledDaysWeekEnd = explode( ',', $this->helperRental->getAttribute( $product, 'sirent_excludeddays_end' ) );
		}

		if ( ! is_array( $disabledDaysWeekEnd ) ) {
			$disabledDaysWeekEnd = [ $disabledDaysWeekEnd ];
		}
		if ( in_array( '-1', $disabledDaysWeekEnd ) ) {
			return [];
		}
		if ( in_array( (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $disabledDaysWeekEnd ) ) {
			return $this->getDisabledDaysWeekEnd( $product, true );
		}
		$disabledDaysWeekCalendar = $this->getDisabledDaysWeek( ExcludedDaysWeekFrom::CALENDAR, $product );

		return array_filter( array_unique( array_merge( $disabledDaysWeekCalendar, $disabledDaysWeekEnd ) ) );
	}

	/**
	 * returns the disabled dates.
	 *
	 * @param      $type
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getExcludedDates( $type, $product = null, $configOnly = false ) {
		/** @var array $excludedDates */
		$excludedDates = $this->helperRental->unserialize( ( $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/global_exclude_dates',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		) ) );
		if ( $configOnly ) {
			return $excludedDates;
		}
		if ( null !== $product ) {
			$useGlobalDates = $this->helperRental->getAttribute( $product, 'sirent_global_exclude_dates' );
			if ( ! $useGlobalDates ) {
				$excludedDates = $this->helperRental->unserialize( $this->helperRental->getAttribute( $product, 'sirent_excluded_dates' ) );
				if ( ! $excludedDates ) {
					$excludedDates = [];
				}
			}
		}
		/*
         * A daily recurring interval will take into consideration that start and end date are from same day.
         * If different days are used it will take start date will be taken into consideration
         * e.g 2016-05-18 07:00 2016-05-19 09:00 recurring daily is actually considered 2016-05-18 07:00 2016-05-18 09:00
         */
		$returnExcluded = [];
		foreach ( $excludedDates as $disabledDate ) {
			if ( array_key_exists( 'exclude_dates_from', $disabledDate ) && in_array( $type, $disabledDate['exclude_dates_from'] ) ) {
				if ( ! $disabledDate['all_day'] ) {
					if ( $disabledDate['disabled_type'] != 'daily' ) {
						$startFull = ( new \DateTime( $disabledDate['disabled_from'] ) )->format( 'Y-m-d' );
						$endFull   = ( new \DateTime( $disabledDate['disabled_to'] ) )->format( 'Y-m-d' );
						$start     = ( new \DateTime( $disabledDate['disabled_from'] ) )->format( 'Y-m-d H:i' );
						$end       = ( new \DateTime( $disabledDate['disabled_to'] ) )->format( 'Y-m-d H:i' );
						if ( $startFull === $endFull ) {
							$returnExcluded[] = [
								's' => $start,
								'e' => $end,
								'r' => $disabledDate['disabled_type'],
							];
						} else {
							$returnExcluded[] = [
								's' => $start,
								'e' => $endFull . '23:59',
								'r' => $disabledDate['disabled_type'],
							];
							/** @var Period $period */
							$period = new Period( $startFull, $endFull );
							foreach ( $period->getDatePeriod( '1 DAY', 1 ) as $repeatDay ) {
								$returnExcluded[] = [
									's' => $repeatDay->format( 'Y-m-d' ) . ' 00:00',
									'e' => $repeatDay->format( 'Y-m-d' ) . ' 23:59',
									'r' => $disabledDate['disabled_type'],
								];
							}
							$returnExcluded[] = [
								's' => $endFull,
								'e' => $end,
								'r' => $disabledDate['disabled_type'],
							];
						}
					} else {
						$start            = ( new \DateTime( $disabledDate['disabled_from'] ) )->format( 'Y-m-d H:i' );
						$end              = ( new \DateTime( $disabledDate['disabled_from'] ) )->format( 'Y-m-d' ) . ' ' .
						                    ( new \DateTime( $disabledDate['disabled_to'] ) )->format( 'H:i' );
						$returnExcluded[] = [
							's' => $start,
							'e' => $end,
							'r' => $disabledDate['disabled_type'],
						];
					}
				}
			} elseif ( array_key_exists( 'exclude_dates_from', $disabledDate ) && in_array( substr( $type, 5 ), $disabledDate['exclude_dates_from'] ) ) {
				if ( $disabledDate['all_day'] ) {
					$start            = ( new \DateTime( $disabledDate['disabled_from'] ) )->format( 'Y-m-d' ) . ' 00:00';
					$end              = ( new \DateTime( $disabledDate['disabled_to'] ) )->format( 'Y-m-d' ) . ' 00:00';
					$returnExcluded[] = [
						's' => $start,
						'e' => $start,
						'r' => $disabledDate['disabled_type'],
					];
					/** @var Period $period */
					$period = new Period( $start, $end );
					foreach ( $period->getDatePeriod( '1 DAY', 1 ) as $repeatDay ) {
						$returnExcluded[] = [
							's' => $repeatDay->format( 'Y-m-d H:i' ),
							'e' => $repeatDay->format( 'Y-m-d H:i' ),
							'r' => $disabledDate['disabled_type'],
						];
					}
				}
			}
		}

		return $returnExcluded;
	}

	/**
	 * @param $period
	 *
	 * @return int
	 */
	public function stringPeriodToMinutes( $period ) {
		$periodArray = [
			'm' => 1,
			'h' => 60,
			'd' => 1440,
			'w' => 1440 * 7,
			'M' => 1440 * 30,
			'y' => 1440 * 365,
		];
		$returnValue = 0;
		$lastChar    = substr( $period, - 1 );
		if ( array_key_exists( $lastChar, $periodArray ) ) {
			$value       = substr( $period, 0, - 1 ); //remove last char for string
			$returnValue = (int) $value * $periodArray[ $lastChar ];
		}

		return $returnValue;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getMinimumPeriod( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$minimumPeriod = $this->scopeConfig->getValue(
				'salesigniter_rental/min_max/min_period',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$minimumPeriod = $this->helperRental->getAttribute( $product, 'sirent_min' );
		}

		if ( $minimumPeriod === '' || (int) $minimumPeriod === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$minimumPeriod = $this->getMinimumPeriod( $product, true );
		}
		if ( ! $minimumPeriod || $minimumPeriod === '' ) {
			$minimumPeriod = '0d';
		}
		if ( $minimumPeriod === '0d' && $product !== null && $this->useTimes( $product ) === false && (int) $this->getHotelMode( $product ) === 1 ) {
			$minimumPeriod = '1d';
		}

		return $minimumPeriod;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getMaximumPeriod( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$maximumPeriod = $this->scopeConfig->getValue(
				'salesigniter_rental/min_max/max_period',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$maximumPeriod = $this->helperRental->getAttribute( $product, 'sirent_max' );
		}

		if ( $maximumPeriod === '' || (int) $maximumPeriod === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$maximumPeriod = $this->getMaximumPeriod( $product, true );
		}
		if ( ! $maximumPeriod || $maximumPeriod === '' ) {
			$maximumPeriod = '0d';
		}

		return $maximumPeriod;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getTurnoverBefore( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$turnoverBefore = $this->scopeConfig->getValue(
				'salesigniter_rental/turnover/turnover_before',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$turnoverBefore = $this->helperRental->getAttribute( $product, 'sirent_turnover_before' );
		}
		if ( $turnoverBefore === '' || (int) $turnoverBefore === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$turnoverBefore = $this->getTurnoverBefore( $product, true );
		}

		if ( ! $turnoverBefore || $turnoverBefore === '' ) {
			$turnoverBefore = '0d';
		}

		return $turnoverBefore;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getTurnoverAfter( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$turnoverAfter = $this->scopeConfig->getValue(
				'salesigniter_rental/turnover/turnover_after',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$turnoverAfter = $this->helperRental->getAttribute( $product, 'sirent_turnover_after' );
		}

		if ( $turnoverAfter === '' || (int) $turnoverAfter === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$turnoverAfter = $this->getTurnoverAfter( $product, true );
		}
		if ( ! $turnoverAfter || $turnoverAfter === '' ) {
			$turnoverAfter = '0d';
		}

		return $turnoverAfter;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getPadding( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$padding = $this->scopeConfig->getValue(
				'salesigniter_rental/turnover/padding',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$padding = $this->helperRental->getAttribute( $product, 'sirent_padding' );
		}

		if ( $padding === '' || (int) $padding === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$padding = $this->getPadding( $product, true );
		}
		if ( ! $padding || $padding === '' ) {
			$padding = '0d';
		}

		return $padding;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getFixedLength( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$fixedLength = $this->scopeConfig->getValue(
				'salesigniter_rental/calendar_options/fixed_rental_length',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$fixedLength = $this->helperRental->getAttribute( $product, 'sirent_fixed_length' );
		}

		if ( $fixedLength === '' || (int) $fixedLength === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$fixedLength = $this->getFixedLength( $product, true );
		}
		if ( ! $fixedLength || $fixedLength === '' ) {
			$fixedLength = '0d';
		}

		return $fixedLength;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array|bool
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getFixedOptions( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$fixedType = $this->scopeConfig->getValue(
				'salesigniter_rental/calendar_options/fixed_type',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$fixedType = $this->helperRental->getAttribute( $product, 'sirent_fixed_type' );
		}

		if ( $fixedType === '' || (int) $fixedType === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$fixedType = $this->getFixedOptions( $product, true );
		}
		if ( ! $fixedType || $fixedType === '' ) {
			$fixedType = 'disabled';
		}
		if ( $fixedType === 'disabled' ) {
			return false;
		}
		$fixedLength = $this->getFixedLength( $product, $configOnly );
		if ( $fixedLength === '0d' ) {
			return false;
		}

		return [
			'type'   => $fixedType,
			'length' => explode( ',', str_replace( ' ', '', $fixedLength ) ),
		];
	}

	public function getFixedTemplate( $product = null ) {
		$fixedOptionsValues = $this->getFixedOptions( $product );
		$fixedOptions       = $fixedOptionsValues['type'];
		/** @var array $fixedValues */
		$fixedValues = $fixedOptionsValues['length'];
		if ( $fixedOptions === false ) {
			return '';
		}
		$template = '';
		if ( $fixedOptions === 'select' ) {
			$template .= '<div class="fixed_length"> ' . __( 'Choose Your Period:' ) . ' <select name="fixedLength">';
			$pCount   = 0;

			foreach ( $fixedValues as $fixedLength ) {
				$selected = '';
				if ( $pCount === 0 ) {
					$selected = ' selected';
				}
				$template .= '<option ' . $selected . ' value="' . $this->stringPeriodToMinutes( $fixedLength ) . '">' . $this->getTextForType( $fixedLength ) . '</option>';
				++ $pCount;
			}
			$template .= '</select></div>';
		} else {
			$template .= '<div class="fixed_length">' . __( 'Choose Your Period:' );
			$pCount   = 0;
			foreach ( $fixedValues as $fixedLength ) {
				$checked = '';
				if ( $pCount === 0 ) {
					$checked = ' checked';
				}
				$template .= '<div class="fixed-date"><input ' . $checked . ' type="radio" name="fixedLength" value="' . $this->stringPeriodToMinutes( $fixedLength ) . '">' . $this->getTextForType( $fixedLength ) . '</div>';
				++ $pCount;
			}
			$template .= '</div>';
		}

		return $template;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getFutureLimit( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$futureLimit = $this->scopeConfig->getValue(
				'salesigniter_rental/min_max/future_limit',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$futureLimit = $this->helperRental->getAttribute( $product, 'sirent_future_limit' );
		}
		if ( $futureLimit === '' || (int) $futureLimit === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$futureLimit = $this->getFutureLimit( $product, true );
		}
		if ( ! $futureLimit || $futureLimit === '' ) {
			$futureLimit = '0d';
		}

		return $futureLimit;
	}

	/**
	 * @return int
	 */
	public function getNumberOfMonths() {
		$numberOfMonth = $this->scopeConfig->getValue(
			'salesigniter_rental/calendar_options/datepicker_months',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		return $numberOfMonth;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getAlwaysShow( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$alwaysShow = $this->scopeConfig->getValue(
				'salesigniter_rental/calendar_options/always_show',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$alwaysShow = $this->helperRental->getAttribute( $product, 'sirent_always_show' );
		}
		if ( $alwaysShow === '' || (int) $alwaysShow === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$alwaysShow = $this->getAlwaysShow( $product, true );
		}
		if ( ! $alwaysShow || $alwaysShow === '' ) {
			$alwaysShow = 0;
		}

		return $alwaysShow;
	}

	/**
	 * @return bool
	 */
	public function timeTypeAmpm() {
		$timeTypeAmpm = (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/time_type_ampm',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		return $timeTypeAmpm;
	}

	/**
	 * Configs per product and store.
	 */

	/**
	 * Get config value data.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return null|string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getHotelMode( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$hotelMode = $this->scopeConfig->getValue(
				'salesigniter_rental/store_hours/hotel_mode',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$hotelMode = $this->helperRental->getAttribute( $product, 'sirent_hotel_mode' );
		}
		if ( $hotelMode === '' || (int) $hotelMode === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$hotelMode = $this->getHotelMode( $product, true );
		}
		if ( ! $hotelMode || $hotelMode === '' ) {
			$hotelMode = 0;
		}

		return $hotelMode;
	}

	/**
	 * Get config value data.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return null|string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDisabledShipping( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$disableShipping = $this->scopeConfig->getValue(
				'salesigniter_rental/checkout_options/disable_shipping',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$disableShipping = $this->helperRental->getAttribute( $product, 'sirent_disable_shipping' );
		}
		if ( $disableShipping === '' || (int) $disableShipping === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$disableShipping = $this->getDisabledShipping( $product, true );
		}
		if ( ! $disableShipping || $disableShipping === '' ) {
			$disableShipping = 0;
		}

		return $disableShipping;
	}

	/**
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array|bool|mixed|string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function allowOverbooking( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$allowOverbooking = $this->scopeConfig->getValue(
				'salesigniter_rental/inventory/allow_overbooking',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			$allowOverbooking = $this->helperRental->getAttribute( $product, 'sirent_allow_overbooking' );
		}

		if ( $allowOverbooking === '' || (int) $allowOverbooking === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$allowOverbooking = $this->allowOverbooking( $product, true );
		}
		if ( ! $allowOverbooking || $allowOverbooking === '' ) {
			$allowOverbooking = 0;
		}

		return $allowOverbooking;
	}

	public function allowOverbookingAdmin() {
		return $this->scopeConfig->getValue(
			'salesigniter_rental/inventory/admin_allow_overbooking',
			ScopeInterface::SCOPE_STORE
		);
	}

	public function allowOverbookingShowWarningAdmin() {
		return $this->scopeConfig->getValue(
			'salesigniter_rental/inventory/admin_show_warning_overbooking',
			ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * @param null $product
	 *                         todo move into damagewaiver extension
	 * @param bool $configOnly
	 *
	 * @return array|bool|mixed|string
	 */
	public function getDamageWaiver( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$damageWaiver = $this->scopeConfig->getValue(
				'damagewaiver/general/damagewaiver_amount',
				ScopeInterface::SCOPE_STORE
			);
		} else {
			$damageWaiver = $this->helperRental->getAttribute( $product, 'sirent_damage_waiver' );
		}
		if ( $damageWaiver === '' || (int) $damageWaiver === \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT ) {
			$damageWaiver = $this->getDamageWaiver( $product, true );
		}
		if ( ! $damageWaiver || $damageWaiver === '' ) {
			$damageWaiver = 0;
		}

		return $damageWaiver;
	}

	/**
	 * Gets Insurance amount.
	 *
	 * @param null | int $product
	 * @param float      $price
	 * @param bool       $formatted
	 *
	 * @return float
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getDamageWaiverAmount( $product = null, $price = 0.0, $formatted = false ) {
		$productType = $this->helperRental->getAttribute( $product, 'type_id' );

		$damageWaiver      = $this->getDamageWaiver( $product );
		$damageWaiverValue = 0;

		$productTypeAllowed = $this->scopeConfig->getValue( 'damagewaiver/general/damage_waiver_product_type' );
		$productTypeAllowed = explode( ',', $productTypeAllowed );

		if ( ! in_array( $productType, $productTypeAllowed ) ) {
			return $damageWaiverValue;
		}

		if ( is_string( $damageWaiver ) ) {
			$damageWaiverValue = $this->helperRental->getAmountFromStringValue( $price, $damageWaiver );
		}
		if ( ! $formatted ) {
			return $damageWaiverValue;
		} else {
			return $this->priceCurrency->format( $damageWaiverValue );
		}
	}

	public function getMaxExtensionPeriod() {
		$maxExtensionPeriod = $this->scopeConfig->getValue(
			'salesigniter_rental/extend_order/max_extension_period',
			ScopeInterface::SCOPE_STORE
		);

		return $maxExtensionPeriod;
	}

	/*From here functions should be checked for other helper/models*/
	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function formatDate( $value ) {
		return $this->dateTimeFormatter->formatObject(
			$value,
			$this->_localeDate->getDateFormat( \IntlDateFormatter::SHORT )
		);
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function formatDateTime( $value ) {
		return $this->dateTimeFormatter->formatObject(
			$value,
			$this->_localeDate->getDateTimeFormat( \IntlDateFormatter::SHORT )
		);
	}

	/**
	 * Convert given date to default UTC format
	 * Remember, UTC is not a timezone, rather a time format https://www.timeanddate.com/time/gmt-utc-time.html.
	 *
	 * @param string $date input format is like 12/16/2016 10:00 am
	 * @param bool   $hasTime
	 * @param null   $locale
	 *
	 * @return \DateTime|null output format is like 2016-12-16 10:00:00.000000
	 */
	public function convertDateToUTC( $date, $hasTime = false, $locale = null ) {
		$timezone = 'UTC';

		$adminTimeZone = new \DateTimeZone( $timezone );
		if ( null === $locale ) {
			$locale = $this->localeResolver->getLocale();
		}
		$formatter = new \IntlDateFormatter(
			$locale,
			\IntlDateFormatter::SHORT,
			\IntlDateFormatter::NONE,
			$adminTimeZone
		);

		$simpleRes = new \DateTime( null, $adminTimeZone );
		$simpleRes->setTimestamp( $formatter->parse( $date ) );
		if ( $hasTime ) {
			$simpleRes = $this->addTimeToUTCDate( $date, $simpleRes );
		}

		return $simpleRes;
	}

	/**
	 * @param string    $date
	 * @param \DateTime $dateFormatted
	 *
	 * @return \DateTime
	 */
	private function addTimeToUTCDate( $date, $dateFormatted ) {
		$dateArr = explode( ' ', $date );
		if ( count( $dateArr ) === 0 ) {
			$dateArr = explode( ', ', $date );
		}
		if ( count( $dateArr ) > 1 ) {
			array_shift( $dateArr );
			$time  = trim( implode( ' ', $dateArr ) );
			$regEx = '/^(\\d|0\\d|1[0-9]|2[0-3]):([0-5]\\d)(?::((?2)))?\\h*([ap]m)?$/mi';

			preg_match_all( $regEx, $time, $matches );
			if ( isset( $matches[4][0] ) && $matches[4][0] !== '' ) {
				$hour = (int) $matches[1][0];
				if ( $hour !== 12 && strtolower( $matches[4][0] ) === 'pm' ) {
					$hour = 12 + (int) $matches[1][0];
				} elseif ( $hour === 12 && strtolower( $matches[4][0] ) === 'am' ) {
					$hour = 0;
				}
			} else {
				$hour = 0;
				if ( isset( $matches[1][0] ) ) {
					$hour = (int) $matches[1][0];
				}
			}
			$dateFormatted->setTime( $hour, $matches[2][0], 0 );
		} else {
			$dateFormatted->setTime( 0, 0, 0 );
		}

		return $dateFormatted;
	}

	/**
	 * Function returns if any product type has calendar enabled.
	 *
	 * @param \Magento\Catalog\Model\Product|int $product
	 * @param bool                               $checkGrid
	 * @param bool                               $fromPricing
	 *
	 * @return bool
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function useTimes( $product, $checkGrid = false, $fromPricing = false ) {
		if ( $product === null ) {
			return false;
		}
		$alwaysShow = $this->getAlwaysShow( $product );
		if ( $alwaysShow ) {
			return false;
		}
		$priceType = $this->helperRental->getAttributeRawValue( $product, 'sirent_use_times' );
		if ( ! $checkGrid ) {
			return (int) $priceType > 0;
		} else {
			if ( ! $fromPricing ) {
				return (int) $priceType === 2 || (int) $priceType === 3;
			} else {
				return (int) $priceType === 2;
			}
		}
	}

	/**
	 * Returns global dates if any.
	 *
	 * @param $type
	 *
	 * @return \DateTime
	 */
	public function getGlobalDates( $type ) {
		if ( $type == 'from' ) {
			if ( $this->catalogSession->getStartDateGlobal() ) {
				return new \DateTime( $this->catalogSession->getStartDateGlobal() );
			}
		} else {
			if ( $this->catalogSession->getEndDateGlobal() ) {
				return new \DateTime( $this->catalogSession->getEndDateGlobal() );
			}
		}

		return '';
	}

	/**
	 * @param $type
	 * @param $buyRequest
	 * @param $product
	 * @param $hasTimes
	 *
	 * @return \DateTime|null
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function getDate( $type, $buyRequest, $product ) {
		/** @var array $calendarSelector */
		$calendarSelector = [];
		if ( array_key_exists( 'calendar_selector', $buyRequest ) ) {
			$calendarSelector = $buyRequest['calendar_selector'];
		}

		/*todo check this part might introduce a bug*/
		//if (array_key_exists('calendar_use_times', $buyRequest)) {
		//$hasTimes = $buyRequest['calendar_use_times'] === '1';
		//}
		$newDate = null;
		if ( array_key_exists( $type, $calendarSelector ) && $calendarSelector[ $type ] !== '' ) {

			/* @var \DateTime $startDate */
			$newDate  = $this->convertDateToUTC( $calendarSelector[ $type ], true, $calendarSelector['locale'] );
			$hasTimes = false;
			if ( null !== $product ) {
				$hasTimes = $this->useTimes( $product, true, true );
			}
			if ( $hasTimes ) {
				$newDate = $this->dateHelper->getCloneDate( $newDate );
			}
		}

		return $newDate;
	}

	/**
	 * Used to get start end dates from custom options.
	 *
	 * @param      $buyRequest
	 * @param      $product
	 * @param bool $hasTimes
	 *
	 * @return \Magento\Framework\DataObject
	 *
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \LogicException
	 */
	public function getDatesFromBuyRequest( $buyRequest, $product = null, $hasTimes = false ) {
		/*
         * We can modify the dates here. The dates are already setup into custom options
         * These values are the working values. So for when times with grid full day I can change it here
         * so into db the values for start end date are full day.
         */
		$dates = new \Magento\Framework\DataObject();

		if ( is_object( $buyRequest ) ) {
			/** @var array $buyRequest */
			$buyRequest = $this->helperRental->unserialize( $buyRequest->getValue() );
		}
		$startDate = $this->getDate( 'from', $buyRequest, $product );
		$endDate   = $this->getDate( 'to', $buyRequest, $product );
		if ( $this->dateHelper->compareDates( $startDate, $endDate ) === 0 ) {
			$endDate = $endDate->add( new \DateInterval( 'PT23H59M' ) );
		}
		$startDateWithTurnover = $this->getDate( 'turnover_from', $buyRequest, $product );
		$endDateWithTurnover   = $this->getDate( 'turnover_to', $buyRequest, $product );

		if ( isset( $buyRequest['is_buyout'] ) ) {
			$dates->setIsBuyout( 1 );
			$startDate = null;
			$endDate   = null;
		}
		if ( null !== $startDate ) {
			$dates->setStartDate( $startDate );
		}
		if ( null !== $endDate ) {
			$dates->setEndDate( $endDate );
		} elseif ( $dates->getStartDate() ) {
			$dates->setEndDate( $startDate );
		}
		if ( null !== $startDateWithTurnover ) {
			$dates->setStartDateWithTurnover( $startDateWithTurnover );
		} elseif ( $dates->getStartDate() ) {
			$startDateWithTurnover = $this->getSendDate( $product, $dates->getStartDate() );
			$dates->setStartDateWithTurnover( $startDateWithTurnover );
		}
		if ( null !== $endDateWithTurnover ) {
			$dates->setEndDateWithTurnover( $endDateWithTurnover );
		} elseif ( $dates->getEndDate() ) {
			$endDateWithTurnover = $this->getReturnDate( $product, $dates->getEndDate() );
			$dates->setEndDateWithTurnover( $endDateWithTurnover );
		}

		return $dates;
	}

	/**
	 * Get the dates from custom option or if no custom options the global dates.
	 *
	 * @param $product
	 *
	 * @return \Magento\Framework\DataObject
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \LogicException
	 */
	public function getCurrentDatesOnFrontend( $product ) {
		$dates = new \Magento\Framework\DataObject();
		if ( $this->_coreRegistry->registry( 'start_date' ) && $this->_coreRegistry->registry( 'end_date' ) ) {
			$dates->setStartDate( $this->_coreRegistry->registry( 'start_date' ) );
			$dates->setEndDate( $this->_coreRegistry->registry( 'end_date' ) );
		} elseif ( $product->hasCustomOptions() &&
		           is_object( $product->getCustomOption( 'info_buyRequest' ) )
		) {
			$dates = $this->getDatesFromBuyRequest(
				$product->getCustomOption( 'info_buyRequest' ), $product
			);
		} elseif ( $this->getGlobalDates( 'from' ) ) {
			$dates->setStartDate( $this->getGlobalDates( 'from' ) );
			$dates->setEndDate( $this->getGlobalDates( 'to' ) );
		}

		return $dates;
	}

	/*get send - return dates. Send - Return dates can be a disabled date from calendar, but not a disabled day from turnover*/
	/**
	 * @param $product
	 * @param $fromDate
	 *
	 * @return \DateTime
	 *
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getSendDate( $product, $fromDate ) {
		if ( empty( $fromDate ) || $fromDate === '0000-00-00 00:00:00' ) {
			return '0000-00-00 00:00:00';
		}
		$sendDate                  = $this->dateHelper->getCloneDate( $fromDate );
		$disabledDaysWeekTurnover  = $this->getDisabledDaysWeek( ExcludedDaysWeekFrom::TURNOVER, $product );
		$disabledDatesTurnover     = $this->getExcludedDates( ExcludedDaysWeekFrom::TURNOVER, $product );
		$disabledDatesFullTurnover = $this->getExcludedDates( ExcludedDaysWeekFrom::FULL_TURNOVER, $product );
		$turnoverBefore            = $this->stringPeriodToMinutes( $this->getTurnoverBefore( $product ) );

		$timeIncrement = $this->timeIncrement();
		if ( $turnoverBefore >= 1440 ) {
			$turnoverBeforeTemp = (int) ( $turnoverBefore / 1440 );

			$initVal = 0;
			while ( $initVal < $turnoverBeforeTemp ) {
				$findResult = \Underscore\Types\Arrays::find( $disabledDatesFullTurnover, function ( $dateElem ) use ( $sendDate ) {
					$newDate = new \DateTime( $dateElem['s'] );

					return $this->dateHelper->isRecurringDate( $newDate, $sendDate, $dateElem['r'] );
				} );

				if ( null !== $findResult && $findResult !== false ) {
					-- $initVal;
				}
				$findResult = \Underscore\Types\Arrays::find( $disabledDaysWeekTurnover, function ( $day ) use ( $sendDate ) {
					return ( $day - 1 ) === (int) $sendDate->format( 'w' );
				} );
				if ( null !== $findResult && $findResult !== false ) {
					-- $initVal;
				}
				$dateInterval = $this->dateHelper->normalizeInterval( '1d' );
				$sendDate->sub( $dateInterval );
				++ $initVal;
			}
		} else {
			$sendDate           = $this->dateHelper->getCloneDate( $fromDate, false );
			$turnoverBeforeTemp = $turnoverBefore;

			$initVal = 0;
			while ( $initVal < $turnoverBeforeTemp ) {
				$findResult = \Underscore\Types\Arrays::find( $disabledDatesTurnover, function ( $dateElem ) use ( $sendDate ) {
					$newDateStart = new \DateTime( $dateElem['s'] );
					$newDateEnd   = new \DateTime( $dateElem['e'] );

					return $this->dateHelper->isRecurringDateBetween( $newDateStart, $newDateEnd, $sendDate, $dateElem['r'] );
				} );
				if ( null !== $findResult && $findResult !== false ) {
					$initVal -= $timeIncrement;
				}
				$dateInterval = $this->dateHelper->normalizeInterval( $timeIncrement . 'm' );
				$sendDate->sub( $dateInterval );
				$initVal += $timeIncrement;
			}
		}

		return $sendDate;
		//get start date with turnover
	}

	public function getReturnDate( $product, $toDate ) {
		if ( empty( $toDate ) || $toDate === '0000-00-00 00:00:00' ) {
			return '0000-00-00 00:00:00';
		}
		$returnDate                = $this->dateHelper->getCloneDate( $toDate );
		$disabledDaysWeekTurnover  = $this->getDisabledDaysWeek( ExcludedDaysWeekFrom::TURNOVER, $product );
		$disabledDatesTurnover     = $this->getExcludedDates( ExcludedDaysWeekFrom::TURNOVER, $product );
		$disabledDatesFullTurnover = $this->getExcludedDates( ExcludedDaysWeekFrom::FULL_TURNOVER, $product );
		$turnoverAfter             = $this->stringPeriodToMinutes( $this->getTurnoverAfter( $product ) );

		$timeIncrement = $this->timeIncrement();

		if ( $turnoverAfter >= 1440 ) {
			$turnoverAfterTemp = (int) ( $turnoverAfter / 1440 );

			$initVal = 0;
			while ( $initVal < $turnoverAfterTemp ) {
				$findResult = \Underscore\Types\Arrays::find( $disabledDatesFullTurnover, function ( $dateElem ) use ( $returnDate ) {
					$newDate = new \DateTime( $dateElem['s'] );

					return $this->dateHelper->isRecurringDate( $newDate, $returnDate, $dateElem['r'] );
				} );
				if ( null !== $findResult && $findResult !== false ) {
					-- $initVal;
				}
				$findResult = \Underscore\Types\Arrays::find( $disabledDaysWeekTurnover, function ( $day ) use ( $returnDate ) {
					return ( $day - 1 ) === (int) $returnDate->format( 'w' );
				} );
				if ( null !== $findResult && $findResult !== false ) {
					-- $initVal;
				}
				$dateInterval = $this->dateHelper->normalizeInterval( '1d' );
				$returnDate->add( $dateInterval );
				++ $initVal;
			}
		} else {
			$returnDate        = $this->dateHelper->getCloneDate( $toDate, false );
			$turnoverAfterTemp = $turnoverAfter;

			$initVal = 0;
			while ( $initVal < $turnoverAfterTemp ) {
				$findResult = \Underscore\Types\Arrays::find( $disabledDatesTurnover, function ( $dateElem ) use ( $returnDate ) {
					$newDateStart = new \DateTime( $dateElem['s'] );
					$newDateEnd   = new \DateTime( $dateElem['e'] );

					return $this->dateHelper->isRecurringDateBetween( $newDateStart, $newDateEnd, $returnDate, $dateElem['r'] );
				} );
				if ( null !== $findResult && $findResult !== false ) {
					$initVal -= $timeIncrement;
				}
				$dateInterval = $this->dateHelper->normalizeInterval( $timeIncrement . 'm' );
				$returnDate->add( $dateInterval );
				$initVal += $timeIncrement;
			}
		}

		return $returnDate;
	}

	/**
	 * @param int|null $order
	 *
	 * @return bool
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\InputException
	 * @throws \LogicException
	 */
	public function isSameDayOrder( $order = null ) {
		$dateForOrder = $this->getDatesForOrder( $order );

		return count( $dateForOrder ) === 1;
	}

	/**
	 * Returns an object with start and end date or and array of objects with start end dates and order_item_id.
	 *
	 * @param int|\Magento\Sales\Api\Data\OrderInterface $order
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\InputException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \LogicException
	 */
	public function getDatesForOrder( $order = null ) {
		/** @var array $datesPerOrderItem */
		$datesPerOrderItem = [];

		if ( null === $order && $this->_coreRegistry->registry( 'current_order' ) ) {
			$order = $this->_coreRegistry->registry( 'current_order' );
		}
		if ( null === $order && $this->_coreRegistry->registry( 'current_shipment' ) ) {
			$order = $this->_coreRegistry->registry( 'current_shipment' )->getOrder();
		}
		if ( null === $order && $this->_coreRegistry->registry( 'current_credit_memo' ) ) {
			$order = $this->_coreRegistry->registry( 'current_order' )->getOrder();
		}
		$orderObj = $order;
		if ( is_numeric( $order ) ) {
			$orderObj = $this->orderRepository->get( $order );
		}
		if ( null === $orderObj ) {
			return $datesPerOrderItem;
		}
		foreach ( $orderObj->getItems() as $oItem ) {
			if ( $oItem->getParentItemId() ) {
				continue;
			}
			$datesOrderItem = $this->getDatesFromBuyRequest(
				$oItem->getProductOptionByCode( 'info_buyRequest' ), $oItem->getProduct()
			);

			$dateExists = false;
			foreach ( $datesPerOrderItem as $dates ) {
				if ( $datesOrderItem->getStartDate() === $dates->getStartDate() &&
				     $datesOrderItem->getEndDate() === $dates->getEndDate()
				) {
					$datesOrderItem->setOrderItems( array_merge( $dates->getOrderItems(), [ $oItem->getId() ] ) );
					$dateExists = true;
					break;
				}
			}
			if ( ! $dateExists && count( $datesOrderItem->getData() ) > 0 ) {
				$datesOrderItem->setOrderItems( [ $oItem->getId() ] );
				$datesPerOrderItem[] = $datesOrderItem;
			}
		}

		return $datesPerOrderItem;
	}

	/**
	 * Hide time period numbers on listing.
	 *
	 * @return bool
	 * */
	public function hideTimePeriodNumbers() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/price/hide_time_periods_numbers',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * @param $periodNumber
	 * @param $hidePeriodNumbers
	 * @param $type
	 *
	 * @return string
	 */
	private function _showTextForPeriodType( $periodNumber, $hidePeriodNumbers, $type ) {
		if ( $periodNumber === 1 || $hidePeriodNumbers ) {
			$text = ( ! $hidePeriodNumbers ? ( $periodNumber . ' ' ) : '' ) . __(
					substr( $type, 0, strlen( $type ) - 1 )
				);
		} else {
			$text = $periodNumber . ' ' . __( $type );
		}

		return $text;
	}

	/**
	 * @param $hidePeriodNumbers
	 * @param $periodType
	 * @param $periodNumber
	 *
	 * @return string
	 */
	private function getPeriodLocalizedText( $hidePeriodNumbers, $periodType, $periodNumber ) {
		$text = '';
		switch ( $periodType ) {
			case PeriodType::MINUTES:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Minutes' );
				break;
			case PeriodType::HOURS:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Hours' );
				break;
			case PeriodType::DAYS:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Days' );
				break;
			case PeriodType::WEEKS:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Weeks' );
				break;
			case PeriodType::MONTHS:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Months' );
				break;
			case PeriodType::YEARS:
				$text = $this->_showTextForPeriodType( $periodNumber, $hidePeriodNumbers, 'Years' );
				break;
		}

		return $text;
	}

	/**
	 * Function to return the text for the periodNumber - periodType pairs.
	 *
	 * @param           $period
	 * @param null|bool $hidePeriodNumbers
	 *
	 * @return string
	 *
	 * @internal param $periodNumber
	 */
	public function getTextForType( $period, $hidePeriodNumbers = null ) {
		if ( null === $hidePeriodNumbers ) {
			$hidePeriodNumbers = $this->hideTimePeriodNumbers();
		}
		$periodArray  = [
			'm' => PeriodType::MINUTES,
			'h' => PeriodType::HOURS,
			'd' => PeriodType::DAYS,
			'w' => PeriodType::WEEKS,
			'M' => PeriodType::MONTHS,
			'y' => PeriodType::YEARS,
		];
		$lastChar     = substr( $period, - 1 );
		$periodType   = 0;
		$periodNumber = 0;
		if ( array_key_exists( $lastChar, $periodArray ) ) {
			$periodNumber = (int) substr( $period, 0, - 1 ); //remove last char for string
			$periodType   = $periodArray[ $lastChar ];
		}
		$text = $this->getPeriodLocalizedText( $hidePeriodNumbers, $periodType, $periodNumber );

		return $text;
	}

	/**
	 * @param \DateTime $datetime
	 * @param int       $precision
	 *
	 * @return \DateTime
	 */
	private function roundTime( \DateTime $datetime, $precision = 30 ) {
		// 1) Set number of seconds to 0 (by rounding up to the nearest minute if necessary)

		$second = (int) $datetime->format( 's' );
		if ( $second > 30 ) {
			// Jumps to the next minute
			$datetime->add( new \DateInterval( 'PT' . ( 60 - $second ) . 'S' ) );
		} elseif ( $second > 0 ) {
			// Back to 0 seconds on current minute
			$datetime->sub( new \DateInterval( 'PT' . $second . 'S' ) );
		}

		$minute = (int) $datetime->format( 'i' );
		$minute = $minute % $precision;
		if ( $minute > 0 ) {
			// 4) Count minutes to next $precision-multiple minutes
			$diff = $precision - $minute;
			// 5) Add the difference to the original date time
			$datetime->add( new \DateInterval( 'PT' . $diff . 'M' ) );
		}

		return $datetime;
	}

	public function getNextDayHour() {
		$hoursNextDay = $this->scopeConfig->getValue(
			'salesigniter_rental/store_hours/hour_next_day',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		if ( $hoursNextDay == '00,00,00' ) {
			return false;
		} else {
			$hoursNextDay = explode( ',', $hoursNextDay );
			foreach ( $hoursNextDay as $key => $hour ) {
				$hoursNextDay[ $key ] = (int) $hour;
			}
		}

		return $hoursNextDay;
	}

	/**
	 * @param string $dateTime
	 *
	 * @return \DateTime $dateTime as time zone
	 */
	public function getTimeAccordingToTimeZone( $dateTime = null ) {
		$currentDateTime = $this->_localeDate->scopeDate( $this->_storeManager->getStore()->getId(), $dateTime, true );

		//$currentDateTime->setTime(17, 10, 0);
		return $currentDateTime;
	}

	/**
	 * @param \DateTime | string $dateObj
	 * @param int                $productId
	 *
	 * @return \DateTime
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function approximateDateFromSetting( $dateObj, $productId ) {
		if ( is_string( $dateObj ) && $dateObj === 'now' ) {
			$dateObj = $this->getTimeAccordingToTimeZone();
		}
		$date     = $this->dateHelper->getCloneDate( $dateObj, false );
		$useTimes = $this->useTimes( $productId );

		if ( $useTimes ) {
			$timeIncrement = $this->timeIncrement();

			return $this->roundTime( $date, $timeIncrement );
		} else {
			$date->add( new \DateInterval( 'P1D' ) );

			return new \DateTime( $date->format( 'Y-m-d' ) . ' 00:00:00' );
		}
	}

	/**
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function addTimeToCalculation() {
		$addTimeCalculation = $this->scopeConfig->getValue(
			'salesigniter_rental/price/add_time',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		if ( ! $addTimeCalculation || $addTimeCalculation === '' ) {
			$addTimeCalculation = '0d';
		}

		return $addTimeCalculation;
	}

	/**
	 * Counts the number occurrences of a certain day of the week between a start and end date
	 * The $start and $end variables must be in UTC format or you will get the wrong number
	 * of days  when crossing daylight savings time.
	 *
	 * @param - $day   - the day of the week such as "Monday", "Tuesday"...
	 * @param - $start - a UTC timestamp representing the start date
	 * @param - $end   - a UTC timestamp representing the end date
	 *
	 * @return Number of occurences of $day between $start and $end
	 */
	public function countDays( $day, $start, $end ) {
		//get the day of the week for start and end dates (0-6)
		$w = [ date( 'w', $start ), date( 'w', $end ) ];

		//get partial week day count
		if ( $w[0] < $w[1] ) {
			$partialWeekCount = ( $day >= $w[0] && $day <= $w[1] );
		} elseif ( $w[0] == $w[1] ) {
			$partialWeekCount = $w[0] == $day;
		} else {
			$partialWeekCount = ( $day >= $w[0] || $day <= $w[1] );
		}

//first count the number of complete weeks, then add 1 if $day falls in a partial week.
		return floor( ( $end - $start ) / 60 / 60 / 24 / 7 ) + $partialWeekCount;
	}

	/**
	 * Function used to reconstruct buyRequest based on options.
	 * Although Magento has its own buyRequest in API calls is impossible
	 * to be accessed so we are using the options of the quote item to
	 * reconstruct the buyRequest object.
	 *
	 * @param \Magento\Quote\Model\Quote\Item $item
	 *
	 * @return array
	 */
	public function prepareBuyRequest( $item ) {
		$buyRequest                                = [];
		$locale                                    = $this->localeResolver->getLocale();
		$buyRequest['calendar_selector']['locale'] = $locale;
		$optionIds                                 = $item->getOptionByCode( 'option_ids' );
		$product                                   = $item->getProduct();
		if ( ! $optionIds ) {
			$optionIds = $item->getCustomOption( 'option_ids' );
			if ( $optionIds ) {
				$product = $item;
			}
		}
		if ( $optionIds ) {
			foreach ( explode( ',', $optionIds->getValue() ) as $optionId ) {
				$option    = $product->getOptionById( $optionId );
				$optionVal = $item->getOptionByCode( 'option_' . $optionId );
				if ( ! $optionVal ) {
					$optionVal = $item->getCustomOption( 'option_' . $optionId );
				}
				if ( $optionVal && $option && $option->getTitle() === 'Start Date:' ) {
					$buyRequest['calendar_selector']['from'] = $this->dateHelper->formatUTCDate( $optionVal->getValue(), $locale );
				}
				if ( $optionVal && $option && $option->getTitle() === 'End Date:' ) {
					$buyRequest['calendar_selector']['to'] = $this->dateHelper->formatUTCDate( $optionVal->getValue(), $locale );
				}
				if ( $optionVal && $option && $option->getTitle() === 'Rental Buyout:' ) {
					$buyRequest['is_buyout'] = 1;
				}
			}
		} else {
			$productOptions = $item->getProductOptions();
			if ( isset( $productOptions['options'] ) ) {
				$options = $productOptions['options'];
			}
			if ( isset( $options ) && is_array( $options ) ) {
				foreach ( $options as $option ) {
					$optionVal = $option['option_value'];

					if ( $optionVal && $option['label'] === 'Start Date:' ) {
						$buyRequest['calendar_selector']['from'] = $this->dateHelper->formatUTCDate( $optionVal, $locale );
					}
					if ( $optionVal && $option['label'] === 'End Date:' ) {
						$buyRequest['calendar_selector']['to'] = $this->dateHelper->formatUTCDate( $optionVal, $locale );
					}
					if ( $optionVal && $option['label'] === 'Rental Buyout:' ) {
						$buyRequest['is_buyout'] = 1;
					}
				}
			}
		}

		return $buyRequest;
	}

	/**
	 * returns Special rules.
	 *
	 * @param null $product
	 * @param bool $configOnly
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getSpecialPricingRules( $product = null, $configOnly = false ) {
		if ( null === $product || $configOnly ) {
			$specialRulesAttribute = explode( ',', $this->scopeConfig->getValue(
				'salesigniter_rental/price/special_pricing_dates',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			) );
		} else {
			$specialRulesAttribute = explode( ',', $this->helperRental->getAttribute( $product, 'sirent_special_rules' ) );
		}
		if ( ! is_array( $specialRulesAttribute ) ) {
			$specialRulesAttribute = [ $specialRulesAttribute ];
		}
		if ( in_array( '-1', $specialRulesAttribute ) ) {
			return [];
		}
		if ( in_array( (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $specialRulesAttribute ) ) {
			return $this->getSpecialPricingRules( $product, true );
		}

		return array_filter( array_unique( $specialRulesAttribute ) );
	}

	/**
	 * @param $product
	 * This will need to create the dates when using daily, monthly etc. Basically will have to use the start_date end_date and create the actual recurring dates based on the selected interval.
	 * this can be done like going per day or and if daily per hour. Needs some concentration but can be done in 5h max
	 *
	 * @return array
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getSpecials( $product ) {
		$specialRules = $this->getSpecialPricingRules( $product );
		$specials     = [];
		foreach ( $specialRules as $ruleName ) {
			$this->searchCriteriaBuilder->addFilter( 'main_table.name_id', $ruleName );
			$criteria = $this->searchCriteriaBuilder->create();
			$items    = $this->fixedRentalDatesRepository->getList( $criteria )->getItems();
			$ruleId   = $this->fixedRentalNamesRepository->getById( $ruleName );
			foreach ( $items as $item ) {
				$specials[] = [
					'date_from'     => $item->getDateFrom(),
					'date_to'       => $item->getDateTo(),
					'catalog_rules' => $ruleId->getCatalogRules(),
				];
			}
		}

		return $specials;
	}
}
