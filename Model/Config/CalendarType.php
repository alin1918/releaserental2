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
class CalendarType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'regular' => __('Regular Calendar On Product Page'),
            'no_calendar' => __('No Calendar On Product Page'),
            'fixed_start_date' => __('Fixed Rental Lengths - Choose Start Date(RTRW)'),
            'fixed_first_available' => __('Fixed Rental Lengths - Start Date Is First Available Date')
        ];
    }
}
