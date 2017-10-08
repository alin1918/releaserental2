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
class TimeIncrement implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '15' => __('15 Minutes'),
            '20' => __('20 Minutes'),
            '25' => __('25 Minutes'),
            '30' => __('30 Minutes'),
            '45' => __('45 Minutes'),
            '60' => __('1 Hour')
        ];
    }
}
