<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Model\Config;

/**
 * AdminNotification update frequency source
 *
 * @codeCoverageIgnore
 */
class AdditionalTimeDisplayPreference implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'plus' => __('+ price/period'),
            'extra' => __('Extra period: price'),
            'hide' => __('Hide additional price')
        ];
    }
}
