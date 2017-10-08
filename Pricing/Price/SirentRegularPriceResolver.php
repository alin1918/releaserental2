<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Pricing\Price;

class SirentRegularPriceResolver implements PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     *
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = 1;
        return $price;
    }
}
