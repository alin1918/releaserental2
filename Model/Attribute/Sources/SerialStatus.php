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
 * Serial status
 */
class SerialStatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{

	/**
	 * Retrieve option array
	 *
	 * @return string[]
	 */
	public static function getOptionArray()
	{
		return [
			'available' => __('Available'),
			'out' => __('Out'),
			'maintenance' => __('Maintenance'),
			'broken' => __('Broken'),
			'booked' => __('Booked')
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
	 * Retrieve option array with empty value
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
