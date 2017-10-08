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
 * Product status functionality model
 */
class RentalType extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Reservation Types
     */
    const STATUS_RENTALANDRESERVATION = 4;//reservation and rental queue
    const STATUS_ENABLED = 1;//reservation
    const STATUS_RENTAL = 2;//rental queue
    const STATUS_DISABLED = 5;//disabled
    const STATUS_NOTSET = null;//not set

    /**#@-*/

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_ENABLED => __('Reservation (Calendar)'),
            //self::STATUS_RENTAL => __('Rental Queue (Membership)'),
            //self::STATUS_RENTALANDRESERVATION => __('Reservation & Rental Queue'),
            self::STATUS_DISABLED => __('Disabled')
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
