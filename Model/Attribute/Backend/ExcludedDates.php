<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product tier price backend attribute model.
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ExcludedDates extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
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
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface                       $scopeConfig
     * @param \SalesIgniter\Rental\Helper\Data           $helperRental
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar       $helperCalendar
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperCalendar = $helperCalendar;
        $this->helperRental = $helperRental;
        $this->storeManager = $storeManager;
    }

    /**
     * By default attribute value is considered non-scalar that can be stored in a generic way.
     *
     * @return bool
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }

        return $this->metadataPool;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
            $dataObj = $object->getData($attrCode);
            $key = 0;
            $new = [];
            if (is_array($dataObj)) {
                foreach ($dataObj as $data) {
                    $hasEmptyData = false;
                    foreach ($data as $fieldName => $field) {
                        if ($field == '' && $fieldName != 'all_day') {
                            $hasEmptyData = true;
                            break;
                        }
                    }

                    if ($hasEmptyData || !empty($data['delete'])) {
                        continue;
                    }

                    $new[$key] =
                        [
                            'disabled_from' => $data['disabled_from'],
                            'disabled_to' => $data['disabled_to'],
                            'disabled_type' => $data['disabled_type'],
                            'all_day' => $data['all_day'] == '' || $data['all_day'] == '0' ? false : true,
                            'exclude_dates_from' => isset($data['exclude_dates_from']) ? $data['exclude_dates_from'] : ['calendar'],
                        ];
                    ++$key;
                }
            }

            if (!empty($new)) {
                $valueChangedKey = $this->getAttribute()->getName().'_changed';
                $object->setData($valueChangedKey, 1);
                $object->setData($attrCode, $this->helperRental->serialize($new));
            } else {
                $websiteId = $this->storeManager->getStore($object->getStoreId())->getWebsiteId();
                $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;
                $changed = false;
                if (!$isGlobal) {
                    $productId = $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField());
                    $data = $this->helperRental->unserialize($this->helperRental->getAttributeRawValue($productId, $this->getAttribute()->getName(), 0));
                    if (is_array($data)) {
                        foreach ($data as $k => $field) {
                            foreach ($field as $fieldName => $value) {
                                if ($fieldName == 'all_day') {
                                    $field[$fieldName] = ($value == false ? '0' : '1');
                                    $data[$k] = $field;
                                }
                            }
                        }
                        $valueChangedKey = $this->getAttribute()->getName().'_changed';
                        $object->setData($valueChangedKey, 1);
                        $object->setData($attrCode, $this->helperRental->serialize($data));
                        $changed = true;
                    }
                }

                if (!$changed) {
                    $valueChangedKey = $this->getAttribute()->getName().'_changed';
                    $object->setData($valueChangedKey, 1);
                    $object->setData($attrCode, '');
                }
            }
        }

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function afterLoad($object)
    {
        if (!is_array($object->getData($this->getAttribute()->getName())) &&
            !is_null($object->getData($this->getAttribute()->getName()))
        ) {
            $data = $this->helperRental->unserialize($object->getData($this->getAttribute()->getName()));
            if (is_array($data)) {
                foreach ($data as $k => $field) {
                    foreach ($field as $fieldName => $value) {
                        if ($fieldName == 'all_day') {
                            $field[$fieldName] = ($value == false ? '0' : '1');
                            $data[$k] = $field;
                        }
                    }
                }
            }
            $object->setData($this->getAttribute()->getName(), $data);
        }

        return $this;
    }
}
