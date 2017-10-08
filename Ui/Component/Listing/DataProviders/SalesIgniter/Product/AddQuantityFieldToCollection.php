<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddQuantityFieldToCollection
 */
class AddQuantityFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'start_date',
            'sirental_reservationorders',
            'start_date',
            'product_id=entity_id',
            /*null,*/
            '{{table}}.order_id=8',
            'left'
        );
    }
}
