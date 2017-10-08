<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Sources;

/**
 * Product status functionality model.
 */
class ExcludedDaysWeek extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**#@+
     * Excluded Days of Week Constants
     */
    const NONE = -1;
    const ALL = 8;
    const MONDAY = 2;
    const TUESDAY = 3;
    const WEDNESDAY = 4;
    const THURSDAY = 5;
    const FRIDAY = 6;
    const SATURDAY = 7;
    const SUNDAY = 1;

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
            self::MONDAY => __('Monday'),
            self::TUESDAY => __('Tuesday'),
            self::WEDNESDAY => __('Wednesday'),
            self::THURSDAY => __('Thursday'),
            self::FRIDAY => __('Friday'),
            self::SATURDAY => __('Saturday'),
            self::SUNDAY => __('Sunday'),
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
     * @param array $excludeDates
     *
     * @return string[]
     */
    public static function getOptionsArray(array $excludeDates = [])
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            if (!in_array($index, $excludeDates, true)) {
                $result[] = ['value' => (string) $index, 'label' => $value];
            }
        }

        return $result;
    }
}
