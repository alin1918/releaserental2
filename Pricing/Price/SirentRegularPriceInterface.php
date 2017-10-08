<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Configurable regular price interface
 * @api
 */
interface SirentRegularPriceInterface extends BasePriceProviderInterface
{
    /**
     * Get max regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxRegularAmount();

    /**
     * Get min regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinRegularAmount();
}
