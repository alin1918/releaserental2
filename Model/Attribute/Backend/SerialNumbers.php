<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Serial Numbers backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace SalesIgniter\Rental\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductInterface;
use SalesIgniter\Rental\Model\SerialNumberDetailsRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SerialNumbers extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
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
     * Catalog product attribute backend serialnumbers
     *
     * @var \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails
     */
    protected $serialNumberDetails;

    /**
     * Website currency codes and rates
     *
     * @var array
     */
    protected $_rates;
    /**
     * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
     */
    private $serialNumberDetailsRepository;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory                     $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Catalog\Helper\Data                                 $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $config
     * @param \Magento\Framework\Locale\FormatInterface                    $localeFormat
     * @param \Magento\Customer\Api\GroupManagementInterface               $groupManagement
     * @param \Magento\Catalog\Model\Product\Type                          $catalogProductType
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository     $serialNumberDetailsRepository
     * @param \Magento\Framework\EntityManager\MetadataPool                $metadataPool
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails $serialNumberDetails
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \SalesIgniter\Rental\Model\SerialNumberDetailsRepository $serialNumberDetailsRepository,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails $serialNumberDetails
    ) {
        $this->serialNumberDetails = $serialNumberDetails;
        $this->_currencyFactory = $currencyFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $catalogData;
        $this->_config = $config;
        $this->_groupManagement = $groupManagement;
        $this->_localeFormat = $localeFormat;
        $this->_catalogProductType = $catalogProductType;
        $this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
        $this->metadataPool = $metadataPool;
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
        return [$objectArray['serialnumber']];
    }

    /**
     * Error message when duplicates
     *
     * @return \Magento\Framework\Phrase
     */
    protected function _getDuplicateErrorMessage()
    {
        return __('We found a duplicate serial number.');
    }

    /**
     * Validate group data
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
        $dataRows = $object->getData($attribute->getName());
        $dataRows = array_filter((array)$dataRows);

        if (empty($dataRows)) {
            return true;
        }

        // validate per website
        $duplicates = [];
        foreach ($dataRows as $dataRow) {
            if (!empty($dataRow['delete'])) {
                continue;
            }
            $compare = implode(
                '-',
                array_merge(
                    [/*$dataRow['unique_field']*/],
                    $this->_getAdditionalUniqueFields($dataRow)
                )
            );
            if (isset($duplicates[$compare])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($this->_getDuplicateErrorMessage()));
            }

            $duplicates[$compare] = true;
        }

        return true;
    }

    /**
     * Assign group serials to product data
     *
     * @param \Magento\Catalog\Model\Product $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        $productId = $object->getData($this->metadataPool->getMetadata(ProductInterface::class)->getLinkField());

        $data = $this->serialNumberDetailsRepository->getByProductIdAsArray(
            $productId
        );

        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
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
        $dataRows = $object->getData($this->getAttribute()->getName());

        if (is_null($dataRows) || !is_array($dataRows)) {
            return $this;
        }

        $new = [];

        // prepare data for save
        $key = 0;
        foreach ($dataRows as $data) {
            $hasEmptyData = false;
            foreach ($this->_getAdditionalUniqueFields($data) as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !empty($data['delete'])) {
                continue;
            }

            $key++;

            $new[$key] =
                [
                    'serialnumber' => $data['serialnumber'],
                    'notes' => $data['notes'],
                    'date_acquired' => $data['date_acquired'],
                    'status' => $data['status'],
                ];
        }

        $productId = $object->getId();
        //todo check first if serial is out or in maintenance
        //todo check that sirentquantity is equal with serialnumbers count
        $this->serialNumberDetailsRepository->deleteByProductId($productId);

        if (count($new) > 0) {
            foreach ($new as $data) {
                $dataObject = new \Magento\Framework\DataObject($data);
                $dataObject->setProductId($productId);
                $this->serialNumberDetailsRepository->saveFromObjectData($dataObject);
            }
        }

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setData($valueChangedKey, 1);

        return $this;
    }
}
