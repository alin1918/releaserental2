<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Sources;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Product status functionality model.
 */
class UseTimes extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Reservation Types
     */
    const USE_TIMES_WITH_GRID = 1; // use times with grid shown
    const USE_TIMES_NO_GRID = 3; // use times no grid shown
    const USE_TIMES_ALL_DAY = 2; // use times no grid full day is reserved
    const USE_TIMES_DISABLED = 0; // don't use times

    /**#@-*/

    /**
     * Retrieve option array.
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::USE_TIMES_WITH_GRID => __('Use Times With Grid'),
            self::USE_TIMES_NO_GRID => __('Use Times No Grid'),
            self::USE_TIMES_ALL_DAY => __('Use Times No Grid. Full Day is reserved'),
            self::USE_TIMES_DISABLED => __('Times Disabled'),
        ];
    }

    /**
     * Retrieve option array with empty value.
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value.
     *
     * @param string $optionId
     *
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
