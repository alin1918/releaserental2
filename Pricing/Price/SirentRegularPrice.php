<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Pricing\Price;

use Magento\Catalog\Model\Product;
use SalesIgniter\Rental\Model\Product\Price;
use SalesIgniter\Rental\Model\Product\Type\Sirent;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class RegularPrice
 */
class SirentRegularPrice extends AbstractPrice implements SirentRegularPriceInterface
{
    /**
     * Price type
     */
    const PRICE_CODE = 'regular_price';

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maxRegularAmount;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minRegularAmount;

    /**
     * @var array
     */
    protected $values = [];

    /** @var PriceResolverInterface */
    protected $priceResolver;

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface              $saleableItem
     * @param float                                                     $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface         $priceCurrency
     * @param PriceResolverInterface                                    $priceResolver
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PriceResolverInterface $priceResolver
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->priceResolver = $priceResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!isset($this->values[$this->product->getId()])) {
            $this->values[$this->product->getId()] = $this->priceResolver->resolvePrice($this->product);
        }

        return $this->values[$this->product->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getMinRegularAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxRegularAmount()
    {
        if (null === $this->maxRegularAmount) {
            $this->maxRegularAmount = $this->doGetMaxRegularAmount();
            $this->maxRegularAmount = $this->doGetMaxRegularAmount() ?: false;
        }
        return $this->maxRegularAmount;
    }

    /**
     * Get max regular amount. Template method
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMaxRegularAmount()
    {
        $price = Price::NO_DATES_PRICE;
        $maxAmount = $this->calculator->getAmount($price, $this->product);

        return $maxAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinRegularAmount()
    {
        if (null === $this->minRegularAmount) {
            $this->minRegularAmount = $this->doGetMinRegularAmount() ?: false;
        }
        return $this->minRegularAmount;
    }

    /**
     * Get min regular amount. Template method
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMinRegularAmount()
    {
        $price = Price::NO_DATES_PRICE;
        $minAmount = $this->calculator->getAmount($price, $this->product);

        return $minAmount;
    }
}
