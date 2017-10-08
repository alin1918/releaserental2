<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Rental\Plugin\Magento\Bundle\Model\ResourceModel\Selection;

use SalesIgniter\Rental\Model\Product\Type\Sirent;

class Collection
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $helperRental;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental
    ) {
        $this->helperRental = $helperRental;
    }

    /**
     * Return product base price.
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject
     * @param \Closure                                                 $proceed
     * @param \Magento\Catalog\Model\Product                           $product
     *
     * @return float
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddQuantityFilter(
        \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject,
        \Closure $proceed
    ) {
        $subject->getSelect()
            ->joinInner(
                ['stock' => $subject->getTable('cataloginventory_stock_status')],
                'selection.product_id = stock.product_id',
                []
            )
            ->where(
                '((selection.selection_can_change_qty or selection.selection_qty <= stock.qty) and stock.stock_status) or (e.type_id="'.Sirent::TYPE_RENTAL.'")'
            );

        return $subject;
        //$returnValue = $proceed();

        //return $returnValue;
    }
}
