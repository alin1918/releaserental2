<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Config;

/**
 * AdminNotification update frequency source.
 *
 * @codeCoverageIgnore
 */
class ProductType implements \Magento\Framework\Option\ArrayInterface
{
    protected $productTypeList;

    public function __construct(\Magento\Catalog\Model\ProductTypeList $productTypeList)
    {
        $this->productTypeList = $productTypeList;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $productTypeList = $this->productTypeList;
        $list = $productTypeList->getProductTypes();
        $data = [];
        foreach ($list as $item) {
            $data[] = ['value' => $item->getName(), 'label' => $item->getLabel()];
        }
        return $data;
    }
}
