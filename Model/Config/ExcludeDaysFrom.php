<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Model\Config;

use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;

/**
 * AdminNotification update frequency source
 *
 * @codeCoverageIgnore
 */
class ExcludeDaysFrom implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => ExcludedDaysWeekFrom::CALENDAR, 'label' => __('Calendar')],
            ['value' => ExcludedDaysWeekFrom::PRICE, 'label' => __('Price')],
            ['value' => ExcludedDaysWeekFrom::TURNOVER, 'label' => __('Turnover')]
        ];
    }
}
