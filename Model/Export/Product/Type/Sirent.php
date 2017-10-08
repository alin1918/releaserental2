<?php
/**
 * Copyright © 2016 SalesIgniter. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Model\Export\Product\Type;

class Sirent
	extends \Magento\CatalogImportExport\Model\Export\Product\Type\AbstractType
{

	/**
	 * Array of attributes codes which are disabled for export.
	 *
	 * @var string[]
	 */
	protected $_disabledAttrs = [
		'sirent_inv_bydate_serialized',
		'pricesbydate_id'
	];
}
