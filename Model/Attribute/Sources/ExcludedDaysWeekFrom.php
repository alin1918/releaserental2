<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Sources;

/**
 * Product status functionality model.
 */
class ExcludedDaysWeekFrom extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**#@+
     * Excluded Days of Week Constants
     */
    const NONE = '-1';
    const PRICE = 'price';
    const CALENDAR = 'calendar';
    const TURNOVER = 'turnover';
    /*these are needed for the calendar only to exclude full days*/
    const FULL_PRICE = 'full_price';
    const FULL_CALENDAR = 'full_calendar';
    const FULL_TURNOVER = 'full_turnover';

    /**#@-*/

    /**
     * Retrieve option array.
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::NONE => __('None'),
            self::PRICE => __('Price'),
            self::CALENDAR => __('Calendar'),
            self::TURNOVER => __('Turnover'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            foreach (self::getOptionArray() as $index => $value) {
                $this->_options[] = [
                    'label' => $value,
                    'value' => $index,
                ];
            }
        }

        return $this->_options;
    }

    /**
     * Retrieve option array with empty value.
     *
     * @return string[]
     */
    public static function getOptionsArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
