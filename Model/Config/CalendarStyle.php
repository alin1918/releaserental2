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
class CalendarStyle implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'input' => __('Two inputs for start end dates'),
            'always' => __('Always show'),

        ];
    }
}
