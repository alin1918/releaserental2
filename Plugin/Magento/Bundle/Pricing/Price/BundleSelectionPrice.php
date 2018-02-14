<?php
/**
 * Copyright © 2018 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 *
 */

namespace SalesIgniter\Rental\Plugin\Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\BundleRegularPrice;
use Magento\Bundle\Pricing\Price\DiscountCalculator;
use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;

class BundleSelectionPrice extends \Magento\Bundle\Pricing\Price\BundleSelectionPrice {
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	private $helperRental;
	/**
	 * @var \Magento\Framework\Registry
	 */
	private $registry;
	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	private $helperCalendar;

	/**
	 * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
	 */
	private $priceCalculations;

	/**
	 * @param Product                                              $saleableItem
	 * @param float                                                $quantity
	 * @param CalculatorInterface                                  $calculator
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
	 * @param SaleableInterface                                    $bundleProduct
	 * @param ManagerInterface                                     $eventManager
	 * @param DiscountCalculator                                   $discountCalculator
	 * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
	 * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
	 * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
	 * @param \Magento\Framework\Registry                          $registry
	 * @param bool                                                 $useRegularPrice
	 * @param array                                                $excludeAdjustment
	 */
	public function __construct(
		Product $saleableItem,
		$quantity,
		CalculatorInterface $calculator,
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		SaleableInterface $bundleProduct,
		ManagerInterface $eventManager,
		DiscountCalculator $discountCalculator,
		\SalesIgniter\Rental\Helper\Data $helperRental,
		\SalesIgniter\Rental\Helper\Calendar $helperCalendar,
		\SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
		\Magento\Framework\Registry $registry,
		$useRegularPrice = false,
		$excludeAdjustment = null
	) {
		parent::__construct( $saleableItem, $quantity, $calculator, $priceCurrency, $bundleProduct, $eventManager, $discountCalculator, $useRegularPrice, $excludeAdjustment );
		$this->helperRental      = $helperRental;
		$this->registry          = $registry;
		$this->helperCalendar    = $helperCalendar;
		$this->priceCalculations = $priceCalculations;
	}

	public function getValue() {
		if ( null !== $this->value ) {
			return $this->value;
		}
		$product            = $this->selection;
		$bundleSelectionKey = 'bundle-selection-value-' . $product->getSelectionId();
		if ( $product->hasData( $bundleSelectionKey ) ) {
			return $product->getData( $bundleSelectionKey );
		}

		$priceCode = $this->useRegularPrice ? BundleRegularPrice::PRICE_CODE : FinalPrice::PRICE_CODE;
		if ( $this->bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC || ( $this->bundleProduct->getPriceType() == Price::PRICE_TYPE_FIXED && $this->helperRental->isRentalType( $this->bundleProduct ) && ! $this->helperRental->isRentalType( $this->selection ) ) ) {
			// just return whatever the product's value is
			$value = $this->priceInfo
				->getPrice( $priceCode )
				->getValue();
		} else {
			// don't multiply by quantity.  Instead just keep as quantity = 1
			$selectionPriceValue = $this->selection->getSelectionPriceValue();
			if ( $this->product->getSelectionPriceType() ) {
				// calculate price for selection type percent
				$price   = $this->bundleProduct->getPriceInfo()
				                               ->getPrice( CatalogPrice\RegularPrice::PRICE_CODE )
				                               ->getValue();
				$product = clone $this->bundleProduct;
				$product->setFinalPrice( $price );
				$this->eventManager->dispatch(
					'catalog_product_get_final_price',
					[ 'product' => $product, 'qty' => $this->bundleProduct->getQty() ]
				);
				$value = $product->getData( 'final_price' ) * ( $selectionPriceValue / 100 );
			} else {
				// calculate price for selection type fixed
				$value = $this->priceCurrency->convert( $selectionPriceValue );
			}
		}
		if ( ! $this->useRegularPrice ) {
			$value = $this->discountCalculator->calculateDiscount( $this->bundleProduct, $value );
		}
		$this->value = $this->priceCurrency->round( $value );
		$product->setData( $bundleSelectionKey, $this->value );

		return $this->value;
	}

}
