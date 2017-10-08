<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use SalesIgniter\Rental\Model\Product\Price;

class FinalPriceResolver implements PriceResolverInterface
{

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     *
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        return Price::NO_DATES_PRICE;
    }
}
