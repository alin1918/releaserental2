<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Model\Config;

class FixedType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'disabled' => __('Disabled'),
            'select' => __('Drop Down'),
            'radio' => __('Radio Buttons')
        ];
    }
}
