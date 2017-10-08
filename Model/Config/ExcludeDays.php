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
class ExcludeDays implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
       return \SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeek::getOptionsArray();
    }
}
