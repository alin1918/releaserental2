<?php
/**
 * Copyright © 2017 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Time extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @param ScopeConfigInterface                 $scopeConfig
     * @param \SalesIgniter\Rental\Helper\Calendar $helperCalendar
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperCalendar = $helperCalendar;
    }

    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($object->hasData('use_config_' . $attributeCode) &&
            $object->getData('use_config_' . $attributeCode) === '1'
        ) {
            $object->setData($attributeCode, null);
        } else {
            
            $hr = $object->getData($attributeCode . '_hour');
            $min = $object->getData($attributeCode . '_minute');
            $sec = $object->getData($attributeCode . '_second');
            
            $object->setData($attributeCode, "{$hr}:{$min}:{$sec}");
        }

        return $this;
    }
}
