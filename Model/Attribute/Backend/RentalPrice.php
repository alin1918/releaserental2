<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog product rental price backend attribute model
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class RentalPrice extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Catalog product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * Catalog product attribute backend rentalprice
     *
     * @var \SalesIgniter\Rental\Model\ResourceModel\Price
     */
    protected $_productRentalPrice;

    /**
     * Website currency codes and rates
     *
     * @var array
     */
    protected $_rates;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory           $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Catalog\Helper\Data                       $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface          $localeFormat
     * @param \Magento\Customer\Api\GroupManagementInterface     $groupManagement
     * @param \Magento\Catalog\Model\Product\Type                $catalogProductType
     * @param \SalesIgniter\Rental\Model\ResourceModel\Price     $productRentalPrice
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \SalesIgniter\Rental\Model\ResourceModel\Price $productRentalPrice
    ) {
        $this->_productRentalPrice = $productRentalPrice;
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
        $this->_groupManagement = $groupManagement;
        $this->_localeFormat = $localeFormat;
        $this->_catalogProductType = $catalogProductType;
    }

    /**
     * Retrieve resource instance
     *
     * @return \SalesIgniter\Rental\Model\ResourceModel\Price
     */
    protected function _getResource()
    {
        return $this->_productRentalPrice;
    }

    /**
     * Get additional unique fields
     *
     * @param array $objectArray
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAdditionalUniqueFields($objectArray)
    {
        return [$objectArray['period']];
    }

    /**
     * Error message when duplicates
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getDuplicateErrorMessage()
    {
        return __('We found a duplicate website, tier price, customer group and quantity.');
    }

    /**
     * Returns whether the value is greater than, or equal to, zero
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isPositiveOrZero($value)
    {
        $value = $this->_localeFormat->getNumber($value);
        $isNegative = $value < 0;
        return !$isNegative;
    }

    /**
     * Validate group price data
     *
     * @param \Magento\Catalog\Model\Product $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Phrase|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $priceRows = $object->getData($attribute->getName());
        $priceRows = array_filter((array)$priceRows);

        if (empty($priceRows)) {
            return true;
        }

        // validate per website
        $duplicates = [];
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            $compare = implode(
                '-',
                array_merge(
                    [$priceRow['website_id'], $priceRow['customer_group_id']],
                    $this->_getAdditionalUniqueFields($priceRow)
                )
            );
            if (isset($duplicates[$compare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }

            if (!$this->isPositiveOrZero($priceRow['price'])) {
                return __('Group price must be a number greater than 0.');
            }

            $duplicates[$compare] = true;
        }

        // if attribute scope is website and edit in store view scope
        // add global group prices for duplicates find
        if (!$attribute->isScopeGlobal() && $object->getStoreId()) {
            $origPrices = $object->getOrigData($attribute->getName());
            if ($origPrices) {
                foreach ($origPrices as $price) {
                    if ($price['website_id'] == 0) {
                        $compare = implode(
                            '-',
                            array_merge(
                                [$price['website_id'], $price['customer_group_id']],
                                $this->_getAdditionalUniqueFields($price)
                            )
                        );
                        $duplicates[$compare] = true;
                    }
                }
            }
        }

        // validate currency
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default');
        $rates = $this->_getWebsiteCurrencyRates();
        foreach ($priceRows as $priceRow) {
            if (!empty($priceRow['delete'])) {
                continue;
            }
            if ($priceRow['website_id'] == 0) {
                continue;
            }

            $globalCompare = implode(
                '-',
                array_merge([0, $priceRow['customer_group_id']], $this->_getAdditionalUniqueFields($priceRow))
            );
            $websiteCurrency = $rates[$priceRow['website_id']]['code'];

            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }
        }

        return true;
    }

    /**
     * Retrieve websites currency rates and base currency codes
     *
     * @return array
     */
    protected function _getWebsiteCurrencyRates()
    {
        if (is_null($this->_rates)) {
            $this->_rates = [];
            $baseCurrency = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                'default'
            );
            foreach ($this->_storeManager->getWebsites() as $website) {
                /* @var $website \Magento\Store\Model\Website */
                if ($website->getBaseCurrencyCode() != $baseCurrency) {
                    $rate = $this->_currencyFactory->create()->load(
                        $baseCurrency
                    )->getRate(
                        $website->getBaseCurrencyCode()
                    );
                    if (!$rate) {
                        $rate = 1;
                    }
                    $this->_rates[$website->getId()] = [
                        'code' => $website->getBaseCurrencyCode(),
                        'rate' => $rate,
                    ];
                } else {
                    $this->_rates[$website->getId()] = ['code' => $baseCurrency, 'rate' => 1];
                }
            }
        }
        return $this->_rates;
    }

    /**
     * Whether group price value fixed or percent of original price
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $priceObject
     *
     * @return bool
     */
    protected function _isPriceFixed($priceObject)
    {
        return true;
        //return $priceObject->isGroupPriceFixed();
    }

    /**
     * Prepare group prices data for website
     *
     * @param array  $priceData
     * @param string $productTypeId
     * @param int    $websiteId
     *
     * @return array
     */
    public function preparePriceData(array $priceData, $productTypeId, $websiteId)
    {
        $rates = $this->_getWebsiteCurrencyRates();
        $data = [];
        $price = $this->_catalogProductType->priceFactory($productTypeId);
        foreach ($priceData as $v) {
            if (!array_filter($v)) {
                continue;
            }
            $key = implode('-', array_merge([$v['customer_group_id']], $this->_getAdditionalUniqueFields($v)));
            if ($v['website_id'] == $websiteId) {
                $data[$key] = $v;
                $data[$key]['website_price'] = $v['price'];
            } elseif ($v['website_id'] == 0 && !isset($data[$key])) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                if ($this->_isPriceFixed($price)) {
                    $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                    $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                }
            }
        }

        return $data;
    }

    /**
     * Assign group prices to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        $storeId = $object->getStoreId();
        $websiteId = null;
        if ($this->getAttribute()->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        $data = $this->_getResource()->loadPriceData(
            $object->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()),
            $websiteId
        );
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['customer_group_id'] = $this->_groupManagement->getAllCustomersGroup()->getId();
            }
        }

        if (!$object->getData('_edit_mode') && $websiteId) {
            $data = $this->preparePriceData($data, $object->getTypeId(), $websiteId);
        }

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * Get resource model instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
     */
    public function getResource()
    {
        return $this->_getResource();
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
     * By default attribute value is considered non-scalar that can be stored in a generic way
     *
     * @return bool
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * After Save Attribute manipulation
     *
     * @param \Magento\Catalog\Model\Product $object
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterSave($object)
    {
        $websiteId = $this->_storeManager->getStore($object->getStoreId())->getWebsiteId();
        $isGlobal = $this->getAttribute()->isScopeGlobal() || $websiteId == 0;

        $priceRows = $object->getData($this->getAttribute()->getName());

        if (is_null($priceRows) || !is_array($priceRows)) {
            return $this;
        }

        $new = [];

        // prepare data for save
        $key = 0;
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['customer_group_id']) || !empty($data['delete'])) {
                continue;
            }

            if ($this->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key++;

            $useForAllGroups = $data['customer_group_id'] == $this->_groupManagement->getAllCustomersGroup()->getId();
            $customerGroupId = !$useForAllGroups ? $data['customer_group_id'] : 0;

            $new[$key] =
                [
                    'website_id' => $data['website_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'price' => $data['price'],
                    'period' => $data['period'],
                    'price_additional' => $data['price_additional'],
                    'period_additional' => $data['period_additional'],
                    'qty_start' => $data['qty_start'],
                    'qty_end' => $data['qty_end'],
                ];
        }

        //$insert = array_diff_key($new, $old);

        $productId = $object->getId();
        $this->_getResource()->deletePriceData($productId, $websiteId, null);
        $isChanged = true;

        if (!empty($new)) {
            foreach ($new as $data) {
                $price = new \Magento\Framework\DataObject($data);
                $price->setEntityId($productId);
                $this->_getResource()->savePriceData($price);
            }
        }

        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }
}
