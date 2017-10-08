<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;

class SirentBackendConfig extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperRental;

    /**
     * @param ScopeConfigInterface                 $scopeConfig
     * @param \SalesIgniter\Rental\Helper\Calendar $helperRental
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \SalesIgniter\Rental\Helper\Calendar $helperRental
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperRental = $helperRental;
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
            if ($this->getAttribute()->getBackendType() === 'text' || $this->getAttribute()->getBackendType() === 'varchar') {
                $data = (string) \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT;
            } else {
                $data = \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT;
            }
        }

        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        } else {
            $object->setData($attributeCode, $data);
        }

        return $this;
    }
}
