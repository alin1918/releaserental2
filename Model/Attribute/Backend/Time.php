<?php
/**
 * Copyright © 2017 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Multiselect extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
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

    /**
     * Before Attribute Save Process.
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $data = $object->getData($attributeCode);
        if ($object->hasData('use_config_'.$attributeCode) &&
            $object->getData('use_config_'.$attributeCode) === '1'
        ) {
            $data = \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT;
        }

        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        } else {
            if (!is_array($data)) {
                $data = [$data];
            }
            $attributeValue = implode(',', $data) ?: '';
            $object->setData($attributeCode, $attributeValue);
        }

        return $this;
    }

    /**
     * After Load Attribute Process.
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();

        $data = $object->getData($attributeCode);
        if ($data) {
            if (!is_array($data)) {
                $data = explode(',', $data);
            }
            //if(in_array(\SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT, $data)){
            //    $data = $this->helperCalendar->
            //}
            $object->setData($attributeCode, $data);
        }

        return $this;
    }
}
