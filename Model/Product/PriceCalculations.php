<?php

namespace SalesIgniter\Rental\Model\Product;

use League\Period\Period;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;
use SalesIgniter\Rental\Model\Attribute\Sources\PricingType;
use SalesIgniter\Rental\Model\Config\GlobalDatesPricingOnListing;

/**
 * Price Calculations Model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class PriceCalculations {
	const NO_DATES_PRICE = - 99999999;
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	protected $_helperRental;

	/**
	 * @var \SalesIgniter\Rental\Model\ResourceModel\PriceFactory
	 */
	protected $_priceResource;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_timeHelper;

	/**
	 * @var PriceCurrencyInterface
	 */
	protected $_priceCurrency;

	/**
	 * Catalog data.
	 *
	 * @var \Magento\Catalog\Helper\Data
	 */
	protected $_catalogData = null;

	/**
	 * @var \Magento\CatalogRule\Model\RuleFactory
	 */
	protected $_ruleFactory;

	/**
	 * @var \Magento\Tax\Helper\Data
	 */
	protected $_taxHelper;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $_localeDate;
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	private $scopeConfig;
	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	private $helperCalendar;
	/**
	 * @var \Magento\Framework\Registry
	 */
	private $coreRegistry;
	/**
	 * @var \SalesIgniter\Rental\Helper\Date
	 */
	private $helperDate;
	/**
	 * @var \SalesIgniter\Rental\Model\Product\Stock
	 */
	private $stock;
	/**
	 * @var \SalesIgniter\Rental\Api\StockManagementInterface
	 */
	private $stockManagement;
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $storeManager;
	/**
	 * @var \Magento\Tax\Model\Config
	 */
	private $taxConfig;
	/**
	 * @var \Magento\Tax\Api\TaxCalculationInterface
	 */
	private $taxCalculation;
	/**
	 * @var \Magento\Catalog\Api\ProductRepositoryInterface
	 */
	private $productRepository;

	/**
	 * FinalPriceBox constructor.
	 *
	 * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
	 * @param \SalesIgniter\Rental\Helper\Date                     $helperDate
	 * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
	 * @param \SalesIgniter\Rental\Model\Product\Stock             $stock
	 * @param \SalesIgniter\Rental\Model\PriceFactory              $priceResource
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime          $timeHelper
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
	 * @param \Magento\Catalog\Helper\Data                         $catalogData
	 * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
	 * @param \Magento\Tax\Model\Config                            $taxConfig
	 * @param \Magento\Tax\Api\TaxCalculationInterface             $taxCalculation
	 * @param \Magento\Tax\Helper\Data                             $taxHelper
	 * @param \Magento\CatalogRule\Model\RuleFactory               $ruleFactory
	 * @param \SalesIgniter\Rental\Api\StockManagementInterface    $stockManagement
	 * @param \Magento\Framework\Registry                          $coreRegistry
	 * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 */
	public function __construct(
		\SalesIgniter\Rental\Helper\Data $helperRental,
		\SalesIgniter\Rental\Helper\Date $helperDate,
		\SalesIgniter\Rental\Helper\Calendar $helperCalendar,
		\SalesIgniter\Rental\Model\Product\Stock $stock,
		\SalesIgniter\Rental\Model\PriceFactory $priceResource,
		\Magento\Framework\Stdlib\DateTime\DateTime $timeHelper,
		PriceCurrencyInterface $priceCurrency,
		\Magento\Catalog\Helper\Data $catalogData,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Tax\Model\Config $taxConfig,
		TaxCalculationInterface $taxCalculation,
		\Magento\Tax\Helper\Data $taxHelper,
		\Magento\CatalogRule\Model\RuleFactory $ruleFactory,
		\SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
		\Magento\Framework\Registry $coreRegistry,
		ProductRepositoryInterface $productRepository,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	) {
		$this->_helperRental     = $helperRental;
		$this->_priceResource    = $priceResource;
		$this->_timeHelper       = $timeHelper;
		$this->_priceCurrency    = $priceCurrency;
		$this->_catalogData      = $catalogData;
		$this->_ruleFactory      = $ruleFactory;
		$this->_taxHelper        = $taxHelper;
		$this->_localeDate       = $localeDate;
		$this->scopeConfig       = $scopeConfig;
		$this->helperCalendar    = $helperCalendar;
		$this->coreRegistry      = $coreRegistry;
		$this->helperDate        = $helperDate;
		$this->stock             = $stock;
		$this->stockManagement   = $stockManagement;
		$this->storeManager      = $storeManager;
		$this->taxConfig         = $taxConfig;
		$this->taxCalculation    = $taxCalculation;
		$this->productRepository = $productRepository;
	}

	/**
	 * Checks if we are on product details page.
	 *
	 * @return bool
	 */
	private function isProductDetailsPage() {
		if ( $this->coreRegistry->registry( 'current_product' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get sort price list config.
	 *
	 * @return int
	 * */
	public function getSortPriceListConfig() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/listing/sort_list_price_period',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get sort price list type.
	 *
	 *
	 * @return int
	 * */
	public function getSortPriceListType() {
		return (int) $this->scopeConfig->getValue(
			'salesigniter_rental/listing/sort_list_price_type',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * In product view and listing shows pricing in a table.
	 *
	 *
	 * @return bool
	 */
	public function priceAsTable() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/price/price_as_table_product_view',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Show buyout price on listing.
	 *
	 * @return bool
	 */
	public function showBuyoutPrice() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/listing/show_buyout_price',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Get number of price points to show on listing.
	 *
	 * @return int
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * */
	public function getPricePointsNumber() {
		if ( $this->_helperRental->isBackend() ) {
			return (int) $this->scopeConfig->getValue(
				'salesigniter_rental/price/grid_points',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		} else {
			return (int) $this->scopeConfig->getValue(
				'salesigniter_rental/listing/price_points',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
	}

	/**
	 * Get additional time display preference.
	 *
	 * @return int
	 * */
	public function getAdditionalTypeDisplayPreference() {
		return $this->scopeConfig->getValue(
			'salesigniter_rental/details/additional_time_display_preference',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Show Min Max details on product details.
	 *
	 * @return int
	 * */
	public function showMinMaxOnProductDetailsPage() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/min_max/show_min_max_details',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Show Next available date on product details.
	 *
	 * @return int
	 * */
	public function showNextAvailableDateOnProductDetails() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/details/next_available_date',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Show Next available date on product details.
	 *
	 * @return int
	 * */
	public function showNextAvailableDateOnListing() {
		return (bool) $this->scopeConfig->getValue(
			'salesigniter_rental/listing/next_available_date',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Returns an array of prices for the product from Reservation Prices collection.
	 *
	 * @param int $productId
	 *
	 * @return array
	 */
	private function getPriceList( $productId, $qty = - 1, $sortType = - 1 ) {
		$customerGroupId = (int) $this->_helperRental->getCustomerGroupId();
		$storeId         = $this->_helperRental->getStoreId();

		// init prices array
		$priceList = [];

		$priceCollection = $this->_priceResource->create()
		                                        ->getCollection()
		                                        ->addFieldToFilter( 'entity_id', $productId )
		                                        ->addFieldToFilter( 'website_id', [ 'in' => [ 0, $storeId ] ] );

		foreach ( $priceCollection as $itemPrice ) {
			if ( (int) $itemPrice->getCustomerGroupId() !== - 1 && ! $itemPrice->getAllGroups() && (int) $itemPrice->getCustomerGroupId() !== $customerGroupId ) {
				continue;
			}

			$qtyStart = (int) $itemPrice->getQtyStart();
			$qtyEnd   = (int) $itemPrice->getQtyEnd();

			if ( $qty > 0 && $qtyStart > 0 && $qtyEnd > 0 && ! ( $qtyStart <= $qty && $qtyEnd >= $qty ) ) {
				continue;
			}

			$priceList[] = [
				'price'             => (float) $itemPrice->getPrice(),
				'period'            => $itemPrice->getPeriod(),
				'qty_start'         => $qtyStart,
				'qty_end'           => $qtyEnd,
				'price_additional'  => (float) $itemPrice->getPriceAdditional(),
				'period_additional' => $itemPrice->getPeriodAdditional(),
				//'pricedate_description' => $itemPrice->getDescription()
			];
		}
		if ( $sortType > - 1 || $this->getSortPriceListConfig( $storeId ) ) {
			$sortTypeConfig = $this->getSortPriceListType( $storeId );
			if ( $sortType > - 1 ) {
				$sortTypeConfig = $sortType;
			}
			if ( $sortTypeConfig === 1 ) {
				usort( $priceList, [ __CLASS__, 'priceMultiSortAsc' ] );
			} else {
				usort( $priceList, [ __CLASS__, 'priceMultiSortDesc' ] );
			}
		}

		return $priceList;
	}

	/**
	 * uasort method for sorting price asc.
	 *
	 * @param $firstPeriod
	 * @param $secondPeriod
	 *
	 * @return int
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function priceMultiSortAsc( $firstPeriod, $secondPeriod ) {
		return $this->helperDate->compareInterval( $firstPeriod['period'], $secondPeriod['period'] );
	}

	/**
	 * uasort method for sorting price asc.
	 *
	 * @param $firstPeriod
	 * @param $secondPeriod
	 *
	 * @return int
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function priceMultiSortDesc( $firstPeriod, $secondPeriod ) {
		return - $this->helperDate->compareInterval( $firstPeriod['period'], $secondPeriod['period'] );
	}

	/**
	 * @param int $productId
	 *
	 * @return bool|float
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function isSpecial( $productId ) {
		$specialPrice        = $this->_helperRental->getAttribute( $productId, 'special_price' );
		$specialFromDate     = $this->_helperRental->getAttribute( $productId, 'special_from_date' );
		$specialToDate       = $this->_helperRental->getAttribute( $productId, 'special_to_date' );
		$store               = $this->_helperRental->getStoreId();
		$specialPricePercent = 0;
		if ( null !== $specialPrice && $specialPrice !== false && $specialFromDate && $specialToDate && $this->_localeDate->isScopeDateInInterval( $store, $specialFromDate, $specialToDate ) ) {
			$specialPricePercent = $specialPrice / 100;
		}
		if ( $specialPricePercent > 0 ) {
			return $specialPricePercent;
		}

		return false;
	}

	private function Translate_DoHTML_GetScripts( $body ) {
		$res = array();
		if ( preg_match_all( '/<script\b[^>]*>([\s\S]*?)<\/script>/m', $body, $matches ) && is_array( $matches ) && isset( $matches[0] ) ) {
			foreach ( $matches[0] as $key => $match ) {
				$res[ '<!-- __SCRIPTBUGFIXER_PLACEHOLDER' . $key . '__ -->' ] = $match;
			}
			$body = str_ireplace( array_values( $res ), array_keys( $res ), $body );
		}

		return array( 'body' => $body, 'scripts' => $res );
	}

	private function Translate_DoHTML_SetScripts( $body, $scripts ) {
		return str_ireplace( array_keys( $scripts ), array_values( $scripts ), $body );
	}

	private function cleanHtml( $html ) {
		$scripts     = $this->Translate_DoHTML_GetScripts( $html );
		$myhtml      = $scripts['body'];
		$htmlCleaned = html5qp( '<div class="si_generated_div">' . $myhtml . '</div>' );
		$domHtml     = $htmlCleaned->find( 'div.si_generated_div' )->first();

		return $this->Translate_DoHTML_SetScripts( $domHtml->innerXML(), $scripts['scripts'] );
	}

	/**
	 * HTML Price list for rental products. Used on product listing, product view, and admin order creator
	 * todo refactor should be separate for listing and product page.
	 *
	 * @param int  $productId
	 * @param bool $simple
	 * @param      $returnValue
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Exception
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function getPriceListHtml(
		$productId, $simple = false, $returnValue = ''
	) {
		$product    = $this->_helperRental->getProductObjectFromId( $productId );
		$priceList  = $this->getPriceList( $productId );
		$buyoutHtml = $this->getBuyoutHtml( $productId );

		$html = '';
		if ( $this->isProductDetailsPage() ) {
			$numberOfPricesListed = - 1;
		} else {
			$numberOfPricesListed = $this->getPricePointsNumber();
		}
		$listedPricePoints = 0;
		$daysRow           = [];
		$priceExclTaxRow   = [];
		$priceInclTaxRow   = [];
		foreach ( $priceList as $price ) {
			if ( $numberOfPricesListed > - 1 && $listedPricePoints >= $numberOfPricesListed ) {
				break;
			}
			$priceAdditionalHtml = $this->getAdditionalPriceHtml( $price, $product );

			list( $priceVal, $priceValInclTax ) = $this->getPriceValuesHtml( $product, $price );
			$qtyText          = $this->getQtyLimitHtml( $price );
			$typeText         = $this->helperCalendar->getTextForType( $price['period'] );
			$priceDescription = '';
			$this->getDaysAndPricesHtml( $priceDescription, $typeText, $priceVal, $qtyText, $priceValInclTax, $html, $priceAdditionalHtml, $daysRow, $priceInclTaxRow, $priceExclTaxRow );
			//$daysRow[] = $dayRow;
			++ $listedPricePoints;
		}

		$html = $this->getListingHtml( $productId, $numberOfPricesListed, $simple, $priceInclTaxRow, $daysRow, $priceExclTaxRow, $buyoutHtml, $html );
		if ( $simple ) {
			$html = str_replace( 'li>', 'p>', $html );

			return $html;
		}
		$startDate = '';
		if ( $this->helperCalendar->globalDatesPricingOnListing() !== GlobalDatesPricingOnListing::NORMAL ) {
			$startDate = $this->helperCalendar->getGlobalDates( 'from' );
			$endDate   = $this->helperCalendar->getGlobalDates( 'to' );
		}
		$listClass = 'pricing-ppr-list';
		if ( $this->coreRegistry->registry( 'current_product' ) ) {
			$listClass = '';
		}
		if ( ! $this->coreRegistry->registry( 'current_product' ) ) {
			if ( ! $this->_helperRental->isBundle( $productId ) || ! $this->_helperRental->isPricePerProduct( $productId ) ) {
				if ( $startDate !== '' ) {
					$priceListValue = $this->calculatePrice( $productId, $startDate, $endDate, 1 );
					if ( $priceListValue > 0 ) {
						$htmlPriceValue = '<div>' . __( 'Price for Selected Dates: ' ) . $this->_priceCurrency->format( $priceListValue ) . '</div>';
						if ( $this->helperCalendar->globalDatesPricingOnListing() === GlobalDatesPricingOnListing::BOTH ) {
							$html = $htmlPriceValue . $html;
						} else {
							$html = $htmlPriceValue;
						}
						$returnValue = '';
					}
				} else {
					$returnValue = '';
				}
			} elseif ( $this->_helperRental->isBundle( $productId ) && $this->_helperRental->isPricePerProduct( $productId ) ) {
				$html = '';
			} else {
				$returnValue = '';
			}
			if ( $returnValue !== '' && $this->_helperRental->isBundle( $productId ) ) {
				/*$scripts     = $this->Translate_DoHTML_GetScripts( $returnValue );
				$myhtml      = $scripts['body'];
				$htmlCleaned = html5qp( '<div class="si_generated_div">' . $myhtml . '</div>' );
				$dom         = $htmlCleaned->find( 'div.si_generated_div' )->first();
				$priceFields = $dom->find( '.price-wrapper' );
				$isChanged   = false;
				foreach ( $priceFields as $priceField ) {
					if ( strpos( $priceField->attr( 'id' ), 'from-' ) !== false ) {
						$priceField->parent()->attr( 'style', 'display:none' );
						$isChanged = true;
					}
				}
				if ( $isChanged ) {
					$returnValue = $this->Translate_DoHTML_SetScripts( $dom->innerHTML5(), $scripts['scripts'] );
				} */
			}
		} else {
			if ( $returnValue !== '' ) {
				/** @var \QueryPath\DOMQuery $dom */
				/*$scripts     = $this->Translate_DoHTML_GetScripts( $returnValue );
				$myhtml      = $scripts['body'];
				$htmlCleaned = html5qp( '<div class="si_generated_div">' . $myhtml . '</div>' );
				$dom         = $htmlCleaned->find( 'div.si_generated_div' )->first();
				$priceFields = $dom->find( '.price-wrapper' );
				foreach ( $priceFields as $priceField ) {
					$priceField->attr( 'style', 'display:none' );
					if ( strpos( $priceField->attr( 'id' ), 'old-price' ) !== false || strpos( $priceField->attr( 'id' ), 'from-' ) !== false ) {
						$priceField->parent()->attr( 'style', 'display:none' );

						$html = '';
					}
				}
				$returnValue = $this->Translate_DoHTML_SetScripts( $dom->innerHTML5(), $scripts['scripts'] );*/
				if ( $this->_helperRental->isBundle( $productId ) && $this->_helperRental->isPricePerProduct( $productId ) ) {
					$html = '';
				}
			}
		}

		$dataType = $this->_helperRental->isBuyout( $productId ) ? 'rental-buyout' : '';

		$result = '<div class="pricing-ppr ' . $listClass . '" data-product-id="' .
		          $productId .
		          '" data-type="' . $dataType . '"> <ul class="ppr-ul-list">' .
		          $html .
		          '</ul></div>';

		return $returnValue . $result;
	}

	/**
	 * @param $productId
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function getBuyoutHtml( $productId ) {
		$buyoutHtml     = '';
		$isRentalBuyout = $this->_helperRental->getAttribute( $productId, 'sirent_enable_buyout' );
		if ( $isRentalBuyout ) {
			$buyoutPrice = (float) $this->_helperRental->getAttribute( $productId, 'sirent_buyout_price' );
			// if ($this->taxConfig->needPriceConversion($this->storeManager->getStore())) {
			if ( is_numeric( $productId ) ) {
				$product = $this->productRepository->getById( $productId );
			}
			$buyoutPrice = $this->_catalogData->getTaxPrice( $product, $buyoutPrice );
			// }
			if ( $buyoutPrice > 0 ) {
				$buyoutHtml = __( 'Buyout: ' ) . $this->_priceCurrency->format( $buyoutPrice );

				return $buyoutHtml;
			}
		}

		return $buyoutHtml;
	}

	/**
	 * @param $price
	 * @param $product
	 *
	 * @return string
	 */
	private function getAdditionalPriceHtml( $price, $product ) {
		$additionalPrice     = '';
		$priceAdditionalHtml = '';
		if ( $price['price_additional'] > 0 ) {
			$additionalPrice      = $this->_priceCurrency->format(
				$this->_catalogData->getTaxPrice( $product, $price['price_additional'] )
			);
			$additionalTimePeriod = $this->helperCalendar->getTextForType(
				$price['period_additional'], true
			);
		}
		if ( $additionalPrice !== '' ) {
			if ( $this->getAdditionalTypeDisplayPreference() == 'plus' ) {
				$priceAdditionalHtml = ' + ' . $additionalPrice . '/' . $additionalTimePeriod;

				return $priceAdditionalHtml;
			} elseif ( $this->getAdditionalTypeDisplayPreference() == 'extra' ) {
				$priceAdditionalHtml = __( ' Extra ' ) . $additionalTimePeriod . ' ' . $additionalPrice;

				return $priceAdditionalHtml;
			}
		}

		return $priceAdditionalHtml;
	}

	/**
	 * @param $price
	 *
	 * @return string
	 */
	private function getQtyLimitHtml( $price ) {
		$qtyText = '';
		if ( $price['qty_start'] ) {
			$qtyText .= ' ' . __( 'if quantity is bigger than' ) . ' '
			            . $price['qty_start'];
		}
		if ( $price['qty_end'] ) {
			if ( $price['qty_end'] ) {
				$qtyText .= ' ' . __( 'and' ) . ' ';
			}
			$qtyText .= ' ' . __( 'if quantity is lower than' ) . ' '
			            . $price['qty_end'];

			return $qtyText;
		}

		return $qtyText;
	}

	/**
	 * @param $product
	 * @param $price
	 *
	 * @return array
	 */
	private function getPriceValuesHtml( $product, $price ) {
		/*$normalPriceNotTax = $this->_catalogData->getTaxPrice($product, $price['price']);
		$specialPriceNotTax = $this->_ruleFactory->create()->calcProductPriceRule($product, $normalPriceNotTax);
		$normalPriceWithTax = $this->_catalogData->getTaxPrice($product, $price['price'], true);
		$specialPriceWithTax = $this->_ruleFactory->create()->calcProductPriceRule($product, $normalPriceWithTax);
		*/
		list( $normalPriceNotTax, $specialPriceNotTax, $normalPriceWithTax, $specialPriceWithTax ) = $this->getAllPricesValues( $product, $price['price'] );
		if ( ! $specialPriceNotTax || $normalPriceNotTax === $specialPriceNotTax ) {
			$priceVal        = $this->_priceCurrency->format( $normalPriceNotTax );
			$priceValInclTax = $this->_priceCurrency->format(
				$normalPriceWithTax
			);

			return [ $priceVal, $priceValInclTax ];
		} else {
			$priceVal        = '<span style="text-decoration: line-through;padding-right: 5px;">' .
			                   $this->_priceCurrency->format( $normalPriceNotTax ) . '</span>' .
			                   $this->_priceCurrency->format( $specialPriceNotTax );
			$priceValInclTax = '<span style="text-decoration: line-through;padding-right: 5px;">' .
			                   $this->_priceCurrency->format( $normalPriceWithTax ) . '</span>' .
			                   $this->_priceCurrency->format(
				                   $specialPriceWithTax
			                   );

			return [ $priceVal, $priceValInclTax ];
		}
	}

	/**
	 * @param array $priceInclTaxRow
	 * @param array $daysRow
	 * @param array $priceExclTaxRow
	 *
	 * @return string
	 */
	private function getPriceAsTableHtml( $priceInclTaxRow, $daysRow, $priceExclTaxRow ) {
		$html = '';
		if ( $this->priceAsTable() ) {
			$html = '<table class="priceTablePpr"><tr><td' . ( count( $priceInclTaxRow ) ? ' class="first">' : '>' );
			if ( count( $priceInclTaxRow ) ) {
				$html .= '</td><td>';
			}
			foreach ( $daysRow as $dayRow ) {
				$html .= $dayRow . '</td><td>';
			}
			$html = substr( $html, 0, strlen( $html ) - 4 );
			$html .= '</tr><tr><td>';
			if ( count( $priceInclTaxRow ) ) {
				$html .= __( 'Price Excl. tax:' ) . '</td><td>';
			}
			foreach ( $priceExclTaxRow as $pRow ) {
				$html .= strip_tags( $pRow ) . '</td><td>';
			}
			$html = substr( $html, 0, strlen( $html ) - 4 );
			$html .= '</tr>';
			if ( count( $priceInclTaxRow ) ) {
				$html .= '<tr><td>' . __( 'Price Incl. tax:' ) . '</td><td>';

				foreach ( $priceInclTaxRow as $pRow ) {
					$html .= strip_tags( $pRow ) . '</td><td>';
				}
				$html = substr( $html, 0, strlen( $html ) - 4 );
				$html .= '</tr>';
			}
			$html .= '</table>';

			return $html;
		}

		return $html;
	}

	/**
	 * @param $priceDescription
	 * @param $typeText
	 * @param $priceVal
	 * @param $qtyText
	 * @param $priceValInclTax
	 * @param $html
	 * @param $priceAdditionalHtml
	 * @param $daysRow
	 * @param $priceInclTaxRow
	 * @param $priceExclTaxRow
	 *
	 * @return array
	 */
	private function getDaysAndPricesHtml( $priceDescription, $typeText, $priceVal, $qtyText, $priceValInclTax, &$html, $priceAdditionalHtml, &$daysRow, &$priceInclTaxRow, &$priceExclTaxRow ) {
		//if ($this->priceAsTable()) {
		//$daysRow = [];
		//$priceExclTaxRow = [];
		//$priceInclTaxRow = [];
		//}
		if ( $this->_taxHelper->displayBothPrices() ) {
			if ( $this->priceAsTable() ) {
				$daysRow[]         = $priceDescription . $typeText;
				$priceExclTaxRow[] = $priceVal . $qtyText;
				$priceInclTaxRow[] = $priceValInclTax . $qtyText;

				return [ $daysRow, $priceExclTaxRow, $priceInclTaxRow, $html ];
			} else {
				$html
					.= '<li>' . $priceDescription . $typeText . ' ' . __( 'Price Excl. Tax:' )
					   . ' ' . $priceVal . $qtyText
					   . '</li>';
				$html
					.= '<li>' . $typeText . ' ' . __( 'Price Incl. tax:' ) . ' ' . $priceValInclTax
					   . $qtyText
					   . '</li>';

				return [ $daysRow, $priceExclTaxRow, $priceInclTaxRow, $html ];
			}
		} else {
			/*if ($this->taxConfig->needPriceConversion($this->storeManager->getStore())) {
				$priceVal = $priceValInclTax;
			}*/

			if ( $this->priceAsTable() ) {
				$daysRow[]         = $priceDescription . $typeText;
				$priceExclTaxRow[] = $priceVal . $priceAdditionalHtml . $qtyText;

				return [ $daysRow, $priceExclTaxRow, $priceInclTaxRow, $html ];
			} else {
				$html .= '<li>' . $priceDescription . $typeText . ':' . ' ' . $priceVal . $priceAdditionalHtml . $qtyText
				         . '</li>';

				return [ $daysRow, $priceExclTaxRow, $priceInclTaxRow, $html ];
			}
		}
	}

	/**
	 * @param $productId
	 * @param $numberOfPricesListed
	 * @param $simple
	 * @param $priceInclTaxRow
	 * @param $daysRow
	 * @param $priceExclTaxRow
	 * @param $buyoutHtml
	 * @param $html
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function getListingHtml( $productId, $numberOfPricesListed, $simple, $priceInclTaxRow, $daysRow, $priceExclTaxRow, $buyoutHtml, $html ) {
		$html .= $this->getPriceAsTableHtml( $priceInclTaxRow, $daysRow, $priceExclTaxRow );
		if ( $numberOfPricesListed === - 1 && ! $simple && $html !== '' ) {
			$html = '<li class="ppr-headline">' . __( 'Pricing:' ) . '</li>' . $html;
		}
		if ( $numberOfPricesListed === - 1 && ! $simple && $buyoutHtml != '' ) {
			$html .= '<li>' . $buyoutHtml . '</li>';
		} else {
			if ( $this->showBuyoutPrice() && $buyoutHtml != '' ) {
				$html .= '<li>' . $buyoutHtml . '</li>';
			}
		}
		if ( ! $this->_helperRental->isBackend() ) {
			$html = $this->getSpecialHtml( $productId, $html );
			$html = $this->getNextAvailableDateHtml( $productId, $html );
		}

		return $html;
	}

	/**
	 * @param $productId
	 * @param $html
	 *
	 * @return string
	 */
	private function getSpecialHtml( $productId, $html ) {
		$specialPricePercent = $this->isSpecial( $productId );
		if ( $specialPricePercent !== false ) {
			$html .= __( 'Discount: ' ) . ( 100 - ( $specialPricePercent * 100 ) ) . '%';
			$html .= '<br/>';

			return $html;
		}

		return $html;
	}

	/**
	 * @param $productId
	 * @param $html
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function getNextAvailableDateHtml( $productId, $html ) {
		if ( $this->isProductDetailsPage() && $this->showMinMaxOnProductDetailsPage() ) {
			$html = $this->getMinimumPeriodHtml( $productId, $html );
			$html = $this->getMaximumPeriodHtml( $productId, $html );
		}
		$availableDate = '';
		if ( ( $this->isProductDetailsPage() && $this->showNextAvailableDateOnProductDetails() ) ||
		     ( ! $this->isProductDetailsPage() && $this->showNextAvailableDateOnListing() )
		) {
			$availableQuantity = $this->stockManagement->getSirentQuantity( $productId );

			$firstDateAvailable = 0;
			if ( $availableQuantity > 0 ) {
				$firstDateAvailable = $this->stockManagement->getFirstDateAvailable( $productId );
			}
			$availableDate = 'Not available';
			if ( $firstDateAvailable !== 0 ) {
				$availableDate = $this->helperCalendar->formatDate( new \DateTime( $firstDateAvailable ) );
			}
		}

		if ( $availableDate !== '' && ! $this->_helperRental->isBackend() ) {
			$html .= '<li><span>' . __( 'Next Available:' ) . ' ' . $availableDate . '</span></li>';

			return $html;
		}

		return $html;
	}

	/**
	 * @param $productId
	 * @param $html
	 *
	 * @return string
	 */
	private function getMinimumPeriodHtml( $productId, $html ) {
		$minimumPeriod = $this->helperCalendar->getMinimumPeriod( $productId );
		if ( $minimumPeriod !== '' && $minimumPeriod !== false && $minimumPeriod !== '0d' ) {
			$html .= __( 'Minimum Period:' ) . ' ' . $this->helperCalendar->getTextForType( $minimumPeriod ) . '<br/>';

			return $html;
		}

		return $html;
	}

	/**
	 * @param $productId
	 * @param $html
	 *
	 * @return string
	 */
	private function getMaximumPeriodHtml( $productId, $html ) {
		$maximumPeriod = $this->helperCalendar->getMaximumPeriod( $productId, false, false );
		if ( $maximumPeriod !== '' && $maximumPeriod !== false && $maximumPeriod !== '0d' ) {
			$html .= __( 'Maximum Period:' ) . ' ' . $this->helperCalendar->getTextForType( $maximumPeriod ) . '<br/>';

			return $html;
		}

		return $html;
	}

	/**
	 * @param $dateDifference
	 * @param $type
	 *
	 * @return int
	 *
	 * @internal param $dateDifferenceNew
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	private function getMultiplicationValue( $dateDifference, $type ) {
		//$dateDifference = clone $dateDifferenceNew;
		switch ( $type ) {
			case 'y':
				if ( $dateDifference->m > 0 || $dateDifference->d > 0 || $dateDifference->h > 0 || $dateDifference->i > 0 ) {
					return $dateDifference->y + 1;
				} else {
					return $dateDifference->y;
				}
				break;
			case 'm':
				$months = 0;
				if ( $dateDifference->y > 0 ) {
					$months += 12 * $dateDifference->y;
				}
				if ( $dateDifference->d > 0 || $dateDifference->h > 0 || $dateDifference->i > 0 ) {
					return $dateDifference->m + 1 + $months;
				} else {
					return $dateDifference->m + $months;
				}
				break;
			case 'd':

				if ( $dateDifference->h > 0 || $dateDifference->i > 0 ) {
					return $dateDifference->d + 1;
				} else {
					return $dateDifference->d;
				}
				break;

			case 'h':
				$hours = 0;
				if ( $dateDifference->d > 0 ) {
					$hours += 24 * $dateDifference->d;
				}
				if ( $dateDifference->i > 0 ) {
					return $dateDifference->h + 1 + $hours;
				} else {
					return $dateDifference->h + $hours;
				}
				break;

			case 'i':
				$hours   = 0;
				$minutes = 0;

				if ( $dateDifference->d > 0 ) {
					$hours += 24 * $dateDifference->d;
				}
				if ( $dateDifference->h > 0 ) {
					$minutes += 60 * $hours;
				}

				return $dateDifference->i + $minutes;

				break;

		}

		return 0;
	}

	/**
	 * @param $period
	 * @param $price
	 * @param $dateDifference
	 * @param $type
	 *
	 * @return bool
	 */
	private function pricePerPeriod( $period, $price, $dateDifference, $type ) {
		$multiplicationValue = $this->getMultiplicationValue( $dateDifference, $type );

		if ( $multiplicationValue > 0 && substr( $period, - 1 ) === $type ) {
			$periodValue = substr( $period, 0, - 1 );
			if ( $periodValue === '' ) {
				$periodValue = 1;
			} else {
				$periodValue = (int) $periodValue;
			}

			return $price * floor( $multiplicationValue / $periodValue );
		}

		return 0;
	}

	private function pricePerDifferencePeriod( $period, $price, $dateDifference ) {
		return $this->pricePerPeriod( $period, $price, $dateDifference, 'y' ) +
		       $this->pricePerPeriod( $period, $price, $dateDifference, 'm' ) +
		       $this->pricePerPeriod( $period, $price, $dateDifference, 'd' ) +
		       $this->pricePerPeriod( $period, $price, $dateDifference, 'h' ) +
		       $this->pricePerPeriod( $period, $price, $dateDifference, 'i' );
	}

	/**
	 * @param       $solutionPriceTemp
	 * @param array $priceList
	 * @param       $currentDate
	 * @param       $currentDateAdditional
	 * @param       $toDate
	 * @param       $isNonProrated
	 *
	 * @return bool|int|\SalesIgniter\Rental\Model\Product\DateInterval
	 *
	 * @throws \Exception
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.LongVariableNames)
	 */
	private function currentPriceSumAdditional( $solutionPriceTemp, $priceList, $currentDate, $currentDateAdditional, $toDate, $isNonProrated ) {
		$sum = 0;
		if ( $isNonProrated ) {
			foreach ( $priceList as $price ) {
				$sum                += $price['price'];
				$normalizedInterval = $this->helperDate->normalizeInterval( $price['period'] );
				$currentDateAdditional->add( $normalizedInterval );

				if ( $currentDateAdditional < $toDate ) {
					/** @var Period $toCheckPeriod */
					$toCheckPeriod = new Period( $currentDateAdditional, $toDate );
					/** @var \DateInterval $dateDifference */
					$dateDifference = $toCheckPeriod->getDateInterval();

					if ( ! empty( $price['period_additional'] ) && ! empty( $price['price_additional'] ) && (float) $price['price_additional'] > 0 && $this->helperCalendar->stringPeriodToMinutes( $price['period_additional'] ) > 0 ) {
						if ( $this->helperDate->compareInterval( $price['period'], $price['period_additional'] ) >= 0 ) {
							$priceDiff = $this->pricePerDifferencePeriod( $price['period_additional'], $price['price_additional'], $dateDifference );
							$sum       += $priceDiff;

							if ( $sum < $solutionPriceTemp ) {
								$currentDate = $currentDateAdditional->add( $dateDifference );

								return $sum;
							}
						}
					}
				}
			}
			$currentDate = $currentDateAdditional;

			return $sum;
		}
	}

	/**
	 * @param array     $priceList
	 * @param \DateTime $currentDate
	 * @param bool      $isNonProrated
	 *
	 * @return float
	 *
	 * @throws \Exception
	 */
	private function currentPriceSum( $priceList, $currentDate, $isNonProrated ) {
		$sum = 0;
		if ( $isNonProrated ) {
			foreach ( $priceList as $price ) {
				$sum                += $price['price'];
				$normalizedInterval = $this->helperDate->normalizeInterval( $price['period'] );
				$currentDate->add( $normalizedInterval );
			}
		}

		return $sum;
	}

	/**
	 * Function return prorated price.
	 *
	 * @param array     $priceList
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 *
	 * @return float
	 *
	 * @throws \Exception
	 */
	private function calculateProratedPrice( $priceList, $fromDate, $toDate ) {
		//Our premise in both pricing algorithms is the fact that the prices are defined correctly so no period has a bigger price that the period before it
		//sort by minimum period defined
		//the pricing period I divide by minimum period and
		$dateDiff          = $fromDate->diff( $toDate );
		$beforePeriodPrice = $priceList[0];
		$isBigger          = false;
		foreach ( $priceList as $price ) {
			if ( $this->helperDate->compareInterval( $dateDiff, $price['period'], true, false ) <= 0 ) {
				$isBigger = true;
				break;
			}
			//if ($this->helperDate->compareInterval($dateDiff, $price['period'], true, false) === 0) {
			//  return $price['price'];
			//}
			$beforePeriodPrice = $price;
		}
		//$price = $priceList[0];
		if ( ! $isBigger ) {
			$normalizedInterval = $this->helperDate->normalizeInterval( $price['period'] );
			$pricePerSecond     = $price['price'] / $this->helperDate->intervalInSeconds( $normalizedInterval );
			$finalPriceAfter    = $this->helperDate->intervalInSeconds( $dateDiff ) * $pricePerSecond;
		} else {
			$finalPriceAfter = $price['price'];
		}
		$normalizedInterval = $this->helperDate->normalizeInterval( $beforePeriodPrice['period'] );
		$pricePerSecond     = $beforePeriodPrice['price'] / $this->helperDate->intervalInSeconds( $normalizedInterval );
		$finalPriceBefore   = $this->helperDate->intervalInSeconds( $dateDiff ) * $pricePerSecond;
		$finalPrice         = min( $finalPriceAfter, $finalPriceBefore );

		return round( $finalPrice, 4 );
	}

	/**
	 * Function return de biggest interval from a DateInterval.
	 *
	 * @param \DateInterval $currentInterval
	 *
	 * @return string
	 */
	private function getBiggestInterval( $currentInterval ) {
		if ( $currentInterval->y > 0 ) {
			return $currentInterval->y . 'y';
		}
		if ( $currentInterval->m > 0 ) {
			return $currentInterval->m . 'M';
		}
		if ( $currentInterval->d > 0 ) {
			return $currentInterval->d . 'd';
		}
		if ( $currentInterval->h > 0 ) {
			return $currentInterval->h . 'h';
		}
		if ( $currentInterval->i > 0 ) {
			return $currentInterval->i . 'm';
		}

		return '0d';
	}

	/**
	 * Its a classic backtracking problem of getting into all the solutions.
	 *
	 * @param array     $priceList
	 * @param           $fromDate
	 * @param \DateTime $toDate
	 * @param bool      $isNonProrated
	 * @param float     $solutionPrice
	 * @param array     $solutionArray
	 *
	 * @SuppressWarnings(PHPMD.LongVariableNames)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @throws \LogicException
	 * @throws \Exception
	 */
	private function checkAllPrices( $priceList, $fromDate, $toDate, $isNonProrated, &$solutionPrice, &$solutionArray ) {
		/*
		 * we calculate the price in the solution array and the date were we are
		 * the idea is to go through every interval and add it one by one.
		 * We consider that prices are correct so 1m price is never lower than 1d
		 * We make the difference between start and end dates and see which is the biggest so if difference is in years
		 * it will only iterate through years calculate the sum and the new start date, then check the difference again
		 * we check 2 prices with and without additional to see which one is lower.
		 * An actual example.
		 * 1d -> $2 / 1w -> $10 / 1m -> $30 + 1d/$1
		 * 04/09/2016 -> 06/09/2016 . difference is 2 days it will eliminate month and week from array and only let
		 * days .It will add 1d->iterate again add another 1d now the currentinterval is equal with finalinterval
		 */

		//check the price without additional
		$currentDate       = $this->helperDate->getCloneDate( $fromDate, false );
		$solutionPriceTemp = $this->currentPriceSum( $solutionArray, $currentDate, $isNonProrated );

		//check the price with additional
		$currentDateAdditional = $this->helperDate->getCloneDate( $fromDate, false );
		$solutionPriceTemp     = $this->currentPriceSumAdditional(
			$solutionPriceTemp,
			$solutionArray,
			$currentDate,
			$currentDateAdditional,
			$toDate,
			$isNonProrated
		);

		//if any of the prices is not minimum not good
		if ( $solutionPriceTemp >= $solutionPrice && $solutionPrice > 0 ) {
			return;
		}
		//else if we came to an fromdate which is highr that endDate and the price is minum we have a solution
		if ( $currentDate >= $toDate && ( $solutionPriceTemp < $solutionPrice || $solutionPrice === 0 ) ) {
			$solutionPrice = $solutionPriceTemp;

			return;
		}

		//we just filter the price_list array and remove not needed periods
		$currentInterval = $toDate->diff( $currentDate );
		$priceListTemp   = [];

		for ( $iVal = 0; $iVal < count( $priceList ); ++ $iVal ) {
			$periodBig       = $this->getBiggestInterval( $currentInterval );
			$compareInterval = $this->helperDate->compareInterval( $priceList[ $iVal ]['period'], $periodBig );
			if ( $compareInterval === 1 || $compareInterval === 0 ) {
				$priceListTemp[] = $priceList[ $iVal ];
			} else {
				break;
			}
		}
		if ( array_key_exists( $iVal, $priceList ) ) {
			$priceListTemp[] = $priceList[ $iVal ];
		} else {
			$priceListTemp[] = $priceList[ $iVal - 1 ];
		}

		//we go through all the values and try to find the minimum solution/ we only send the full price list and the current interval were we are
		//currentinterval is updated always based on solution array
		//we send the same start/end dates and we calculate end interval vbased on solutionarray
		for ( $iVal = 0; $iVal < count( $priceListTemp ); ++ $iVal ) {
			if ( (float) $priceListTemp[ $iVal ]['price'] > 0 ) {
				$solutionArray[] = $priceListTemp[ $iVal ];
				$this->checkAllPrices( $priceList,
					$fromDate,
					$toDate,
					$isNonProrated,
					$solutionPrice,
					$solutionArray
				);
				array_pop( $solutionArray );
			}
		}
	}

	/**
	 * @param $toDate
	 * @param $productId
	 *
	 * @return \DateTime|static
	 */
	private function modifyToDateWithTimeCalculation( $toDate, $productId ) {
		$toDate               = $this->helperDate->getCloneDate( $toDate, false );
		$addToTimeCalculation = $this->helperCalendar->addTimeToCalculation();
		$dateInterval         = $this->helperDate->normalizeInterval( $addToTimeCalculation );
		$toDate->add( $dateInterval );
		$hasTimes = $this->helperCalendar->useTimes( $productId );
		if ( ! $hasTimes && $this->helperCalendar->getHotelMode( $productId ) === 0 && $toDate->format( 'H:i:s' ) === '23:59:00' ) {
			$toDate = $toDate->sub( new \DateInterval( 'PT23H59M' ) );
		}

		return $toDate;
	}

	private function modifyToDateWithDisabledFromPricing( $product, $fromDate, $toDateParam ) {
		$toDateCloned             = $this->helperDate->getCloneDate( $toDateParam, false );
		$fromDateCloned           = $this->helperDate->getCloneDate( $fromDate, false );
		$toDate                   = $this->helperDate->getCloneDate( $toDateParam, false );
		$disabledDaysWeekPricing  = $this->helperCalendar->getDisabledDaysWeek( ExcludedDaysWeekFrom::PRICE, $product );
		$disabledDatesPricing     = $this->helperCalendar->getExcludedDates( ExcludedDaysWeekFrom::PRICE, $product );
		$disabledDatesFullPricing = $this->helperCalendar->getExcludedDates( ExcludedDaysWeekFrom::FULL_PRICE, $product );

		\Underscore\Types\Arrays::each( $disabledDatesFullPricing, function ( $dateElem ) use ( $fromDateCloned, $toDateCloned, $toDate ) {
			$newDate = new \DateTime( $dateElem['s'] );
			if ( $this->helperDate->isRecurringDateBetweenMultiple( $newDate, $newDate, $fromDateCloned, $toDateCloned, $dateElem['r'] ) ) {
				$dateInterval = $this->helperDate->normalizeInterval( '1d' );
				$toDate->sub( $dateInterval );
			}
		} );

		\Underscore\Types\Arrays::each( $disabledDaysWeekPricing, function ( $day ) use ( $fromDateCloned, $toDateCloned, $toDate ) {
			$countDays = $this->helperCalendar->countDays( $day - 1, $fromDateCloned->getTimestamp(), $toDateCloned->getTimestamp() );
			if ( $countDays > 0 ) {
				$dateInterval = $this->helperDate->normalizeInterval( $countDays . 'd' );
				$toDate->sub( $dateInterval );
			}
		} );

		\Underscore\Types\Arrays::each( $disabledDatesPricing, function ( $dateElem ) use ( $fromDateCloned, $toDateCloned, $toDate ) {
			$newDateStart = new \DateTime( $dateElem['s'] );
			$newDateEnd   = new \DateTime( $dateElem['e'] );
			//how many times a date interval can be repeated between 2 dates
			if ( $this->helperDate->isRecurringDateBetweenMultiple( $newDateStart, $newDateEnd, $fromDateCloned, $toDateCloned, $dateElem['r'] ) ) {
				$dateInterval = $newDateEnd->diff( $newDateStart );
				$toDate->sub( $dateInterval );
			}
		} );

		return $toDate;
	}

	public function getAllPricesValues( $product, $priceAmount, $shippingAddress = null, $billingAddress = null ) {
		if ( is_numeric( $product ) ) {
			$product = $this->productRepository->getById( $product );
		}
		$normalPriceNotTax  = $priceAmount;
		$normalPriceWithTax = $this->_catalogData->getTaxPrice( $product, $normalPriceNotTax );

		$specialPriceNotTax  = $this->_ruleFactory->create()->calcProductPriceRule( $product, $normalPriceNotTax );
		$specialPriceWithTax = $this->_ruleFactory->create()->calcProductPriceRule( $product, $normalPriceWithTax );

		return [ $normalPriceNotTax, $specialPriceNotTax, $normalPriceWithTax, $specialPriceWithTax ];
	}

	/**
	 * Calculates Buyout Price.
	 *
	 * @param $productId
	 *
	 * @return float
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function calculateBuyoutPrice( $productId ) {
		return (float) $this->_helperRental->getAttribute( $productId, 'sirent_buyout_price' );
	}

	public function getDiscounted( $price, $discount ) {
		return (float) $price - $this->_helperRental->getAmountFromStringValue( $price, $discount );
	}

	public function modifyPriceListWithDiscounts( $priceList, $discount ) {
		$returnPriceList = [];
		foreach ( $priceList as $iCount => $price ) {
			$priceList[ $iCount ]['price']            = $this->getDiscounted( $price['price'], $discount );
			$priceList[ $iCount ]['price_additional'] = $this->getDiscounted( $price['price_additional'], $discount );
		}

		return $priceList;
	}

	public function getFinalPricing( $productId, $qty, $discount, $fromDate, $toDate ) {
		$solutionPrice = 0;
		$solutionArray = [];
		$priceType     = (int) $this->_helperRental->getAttribute( $productId, 'sirent_pricingtype' );
		$isNonProrated = $priceType === PricingType::PRICING_NONPRORATED;

		if ( $isNonProrated ) {
			$priceList = $this->getPriceList( $productId, $qty, 2 );
			if ( count( $priceList ) > 0 ) {
				$priceList = $this->modifyPriceListWithDiscounts( $priceList, $discount );
				$this->checkAllPrices( $priceList, $fromDate, $toDate, $isNonProrated, $solutionPrice, $solutionArray );
			}
		} else {
			$priceList = $this->getPriceList( $productId, $qty, 1 );
			if ( count( $priceList ) > 0 ) {
				$priceList     = $this->modifyPriceListWithDiscounts( $priceList, $discount );
				$solutionPrice = $this->calculateProratedPrice( $priceList, $fromDate, $toDate );
			}
		}

		list( $finalPrice, $finalPriceSpecial, $finalPriceTax, $finalPriceSpecialTax ) = $this->getAllPricesValues( $productId, $solutionPrice );
		if ( $finalPriceSpecial > 0 ) {
			return $finalPriceSpecial;
		}

		return $finalPrice;
	}

	/**
	 * @param          $specials
	 * @param Period[] $pricePeriods
	 * @param          $productId
	 * @param          $qty
	 * @param          $specialPrice
	 * @param          $originalFrom
	 * @param          $originalTo
	 *
	 * @return array
	 *
	 * @internal param $ []       $specials
	 */
	public function calculatePricingForSpecials( $specials, &$pricePeriods, $productId, $qty, &$specialPrice, $originalFrom, $originalTo ) {
		$remainingPeriods = [];
		$changed          = false;
		foreach ( $pricePeriods as $pricePeriod ) {
			foreach ( $specials as $iCount => $dates ) {
				$specialPeriod = new Period( $dates['date_from'], $dates['date_to'] );

				/*
				 * Discounts cannot be combined so lets say there are multiple rules for the same interval, only first discount will work
				 */
				if ( $specialPeriod->overlaps( $pricePeriod ) ) {
					$intersectPeriod = $pricePeriod->intersect( $specialPeriod );
					$fromDate        = $intersectPeriod->getStartDate();
					$toDate          = $intersectPeriod->getEndDate();
					$toDate          = $this->modifyToDateWithDisabledFromPricing( $productId, $fromDate, $toDate );
					$sum             = $this->getFinalPricing( $productId, $qty, $dates['catalog_rules'], $fromDate, $toDate );
					$specialPrice    += $sum;
					$diffPeriods     = $pricePeriod->diff( $specialPeriod );
					foreach ( $diffPeriods as $pCount => $diffPeriod ) {
						if ( $diffPeriod->getStartDate() < $originalFrom || $diffPeriod->getEndDate() > $originalTo ) {
							unset( $diffPeriods[ $pCount ] );
						}
					}
					unset( $specials[ $iCount ] );
					$changed          = true;
					$remainingPeriods = array_merge( $remainingPeriods, $diffPeriods );

					break;
				}
			}
		}
		if ( $changed ) {
			$pricePeriods = $remainingPeriods;
			$this->calculatePricingForSpecials( $specials, $remainingPeriods, $productId, $qty, $specialPrice, $originalFrom, $originalTo );
		}
	}

	/**
	 * @param $productId
	 * @param $fromDate
	 * @param $toDate
	 * @param $qty
	 *
	 * @return float
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \LogicException
	 * @throws \Exception
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function calculatePrice( $productId, $fromDate, $toDate, $qty ) {
		if ( $this->helperCalendar->useTimes( $productId, true, true ) ) {
			$fromDate = $this->helperDate->getCloneDate( $fromDate );
			$toDate   = $this->helperDate->getCloneDate( $toDate );
			if ( $this->helperDate->compareDates( $fromDate, $toDate ) === 0 ) {
				$toDate = $toDate->add( new \DateInterval( 'PT23H59M' ) );
			}
		}
		/*
		 * date si modified for adding to price, might interfere with special dates prices because it modifies end date. This case is rare
		 */
		$toDate = $this->modifyToDateWithTimeCalculation( $toDate, $productId );

		$specials     = $this->helperCalendar->getSpecials( $productId );
		$pricePeriods = [ new Period( $fromDate, $toDate ) ];
		$specialPrice = 0;
		if ( count( $specials ) > 0 ) {
			$this->calculatePricingForSpecials( $specials, $pricePeriods, $productId, $qty, $specialPrice, $fromDate, $toDate );
		}

		$finalPrice = $specialPrice;
		foreach ( $pricePeriods as $pricePeriod ) {
			$fromDate   = $pricePeriod->getStartDate();
			$toDate     = $pricePeriod->getEndDate();
			$toDate     = $this->modifyToDateWithDisabledFromPricing( $productId, $fromDate, $toDate );
			$finalPrice += $this->getFinalPricing( $productId, $qty, 0, $fromDate, $toDate );
		}

		return $qty * $finalPrice;
	}
}
