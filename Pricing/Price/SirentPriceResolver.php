<?php
/**
 * Copyright (c) 2016.
 */

namespace SalesIgniter\Rental\Pricing\Price;

use Magento\Catalog\Model\Product;
use SalesIgniter\Rental\Model\Product\Price;
use SalesIgniter\Rental\Model\Product\Type\Sirent;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class SirentPriceResolver implements PriceResolverInterface
{
    /** @var PriceResolverInterface */
    protected $priceResolver;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var Sirent */
    protected $sirent;

    /**
     * @param PriceResolverInterface $priceResolver
     * @param Sirent                 $sirent
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceResolverInterface $priceResolver,
        Sirent $sirent,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceResolver = $priceResolver;
        $this->sirent = $sirent;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = Price::NO_DATES_PRICE;

        return $price;
    }
}
