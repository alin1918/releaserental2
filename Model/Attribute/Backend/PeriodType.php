<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Period Type source
 * todo should be moved to sources folder
 */
class PeriodType extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Period Types
     */
    const MINUTES = 1;
    const HOURS = 2;
    const DAYS = 3;
    const WEEKS = 4;
    const MONTHS = 5;
    const YEARS = 6;


    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::MINUTES => __('Minute'),
            self::HOURS => __('Hour'),
            self::DAYS => __('Day'),
            self::WEEKS => __('Week'),
            self::MONTHS => __('Month'),
            self::YEARS => __('Year')
        ];
    }

    /**
     * Retrieve option array with empty value
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
     * Retrieve option text by option value
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
