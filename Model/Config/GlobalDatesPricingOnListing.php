<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Config;

class GlobalDatesPricingOnListing implements \Magento\Framework\Option\ArrayInterface {
	const NORMAL = 'normal';
	const GLOBAL_PRICE = 'global_price';
	const BOTH = 'both';

	/**
	 * @return array
	 */
	public function toOptionArray() {
		return [
			self::NORMAL => __( 'Show only price points' ),
			// self::GLOBAL_PRICE => __('Show price for Selected Global Dates'),
			// self::BOTH => __('Show price points And Price for Selected Global Dates'),
		];
	}
}
