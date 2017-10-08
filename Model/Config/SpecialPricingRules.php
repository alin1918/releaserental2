<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Config;

/**
 * AdminNotification update frequency source.
 *
 * @codeCoverageIgnore
 */
class SpecialPricingRules implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('\SalesIgniter\Rental\Model\Attribute\Sources\SpecialPricingRules')->getOptionsArray();
    }
}
