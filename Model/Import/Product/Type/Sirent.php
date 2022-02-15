<?php

namespace SalesIgniter\Rental\Model\Import\Product\Type;

use Magento\Catalog\Model\ProductFactory;
use SalesIgniter\Rental\Model\SerialNumberDetailsFactory as SerialNumberFactory;

class Sirent extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Pair value separator.
     */
    const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Instance of database adapter.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Instance of application resource.
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cachedPrices = [];

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cachedSerials = [];

    /**
     * @var array
     */
    protected $_cachedExistingSerials = [];

    /**
     * @var SerialNumberFactory
     */
    protected $_serialNumberFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory  $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection                                $resource
     * @param array                                                                    $params
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        SerialNumberFactory $SerialNumberFactory,
        ProductFactory $ProductFactory,
        array $params
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->_resource = $resource;
        $this->connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->_serialNumberFactory = $SerialNumberFactory;
        $this->_productFactory = $ProductFactory;
    }

    /**
     * Parse pricing.
     *
     * @param array $rowData
     * @param int   $entityId
     *
     * @return array
     */
    protected function parsePricing($rowData, $entityId)
    {
        $_prices = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowData['sirent_prices']
        );
        foreach ($_prices as $_price) {
            $values = explode($this->_entityModel->getMultipleValueSeparator(), $_price);
            $_priceData = $this->parseValues($values);
            if (isset($_priceData['website_id']) && isset($_priceData['period']) && isset($_priceData['price'])) {
                if (!isset($this->_cachedPrices[$entityId])) {
                    $this->_cachedPrices[$entityId] = [];
                }
                $this->_cachedPrices[$entityId][] = $_priceData;
            }
        }

        return $_prices;
    }

    /**
     * Parse serials.
     *
     * @param array $rowData
     * @param int   $entityId
     *
     * @return array
     */
    protected function parseSerials($rowData, $entityId)
    {
        $_serials = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowData['sirent_serials']
        );
        foreach ($_serials as $_serial) {
            $values = explode($this->_entityModel->getMultipleValueSeparator(), $_serial);
            $_serialData = $this->parseValues($values);
            if (isset($_serialData['serialnumber']) && isset($_serialData['status'])) {
                if (!isset($this->_cachedSerials[$entityId])) {
                    $this->_cachedSerials[$entityId] = [];
                }
                $this->_cachedSerials[$entityId][] = $_serialData;
            }
        }

        return $_serials;
    }

    /**
     * Parse the price.
     *
     * @param array $values
     *
     * @return array
     */
    protected function parseValues($values)
    {
        $_result = [];
        foreach ($values as $keyValue) {
            $keyValue = trim($keyValue);
            if ($pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR)) {
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);

                $_result[$key] = $value;
            }
        }

        return $_result;
    }

    /**
     * Populate the price template.
     *
     * @param array $price
     * @param int   $entityId
     * @param int   $index
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populatePriceTemplate($price, $entityId, $index = null)
    {
        $populatedPrice = [
            'entity_id' => $entityId,
            'website_id' => $price['website_id'],
            'price' => $price['price'],
            'qty_start' => (isset($price['customer_group_id']) ? $price['qty_start'] : 0),
            'qty_end' => (isset($price['customer_group_id']) ? $price['qty_end'] : 0),
            'customer_group_id' => (isset($price['customer_group_id']) ? $price['customer_group_id'] : 0),
            'all_groups' => (isset($price['all_groups']) ? $price['all_groups'] : (isset($price['customer_group_id']) ? 0 : 1)),
            'price_additional' => (isset($price['price_additional']) ? $price['price_additional'] : null),
            'period_additional' => (isset($price['period_additional']) ? $price['period_additional'] : null),
            'period' => $price['period'],
        ];

        return $populatedPrice;
    }

    /**
     * Populate the serial template.
     *
     * @param array $serial
     * @param int   $entityId
     * @param int   $index
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populateSerialTemplate($serial, $entityId, $index = null)
    {
        $populatedSerial = [
            'product_id' => $entityId,
            'serialnumber' => $serial['serialnumber'],
            'notes' => (isset($serial['notes']) ? $serial['notes'] : null),
            'cost' => (isset($serial['cost']) ? $serial['cost'] : null),
            'date_acquired' => (isset($serial['date_acquired']) ? $serial['date_acquired'] : null),
            'status' => $serial['status'],
        ];

        if (isset($this->_cachedExistingSerials[$serial['serialnumber']]) === true) {
            $populatedSerial['serialnumber_details_id'] = $this->_cachedExistingSerials[$serial['serialnumber']];
        } else {
            $populatedSerial['serialnumber_details_id'] = null;
        }

        return $populatedSerial;
    }

    /**
     * @param $ProductIds
     */
    protected function prepareExistingSerials($ProductIds)
    {
        $Collection = $this->_serialNumberFactory->create()->getCollection();
        $Collection->addFieldToFilter('product_id', ['in' => $ProductIds]);
        foreach ($Collection as $Serial) {
            $this->_cachedExistingSerials[$Serial->getSerialnumber()] = $Serial->getId();
        }
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveData()
    {
        if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            $productIds = [];
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                foreach ($bunch as $rowNum => $rowData) {
                    $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];
                    $productIds[] = $productData[$this->getProductEntityLinkField()];
                }
                $this->deletePrices($productIds);
                $this->deleteSerials($productIds);
            }
        } else {
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                $productIds = [];
                foreach ($bunch as $rowNum => $rowData) {
                    if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                        continue;
                    }
                    if (isset($newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]])) {
                        $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];
                        if ($this->_type != $productData['type_id']) {
                            continue;
                        }
                        $productIds[] = $productData[$this->getProductEntityLinkField()];
                        $this->parsePricing($rowData, $productData[$this->getProductEntityLinkField()]);
                        $this->parseSerials($rowData, $productData[$this->getProductEntityLinkField()]);
                    }
                }

                $this->prepareExistingSerials($productIds);

                $_clear = false;
                if (!empty($this->_cachedPrices)) {
                    /*
                     * Empty prices for this product since there's no real unique identifier to use to identify the price in the database
                     *
                     * Should this account for update/append and empty column not doing anything??
                     */
                    $this->deletePrices($productIds);
                    $this->insertPrices();
                    $_clear = true;
                }

                if (!empty($this->_cachedSerials)) {
                    //$this->deleteSerials($productIds);
                    $this->insertSerials();
                    $this->fixQuantities($productIds);
                    $_clear = true;
                }

                if ($_clear === true) {
                    $this->clear();
                }
            }
        }

        return $this;
    }

    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @param int   $rowNum
     * @param bool  $isNewProduct
     *
     * @return bool
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        return parent::isRowValid($rowData, $rowNum, $isNewProduct);
    }

    /**
     * Insert options.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function insertPrices()
    {
        $priceTable = $this->_resource->getTableName('sirental_price');
        $productIds = [];
        $insert = [];
        foreach ($this->_cachedPrices as $entityId => $prices) {
            $productIds[] = $entityId;
            foreach ($prices as $key => $price) {
                if ($tmpArray = $this->populatePriceTemplate($price, $entityId, $key)) {
                    $insert[] = $tmpArray;
                }
            }
        }
        $this->connection->insertOnDuplicate($priceTable, $insert);

        return $this;
    }

    /**
     * Insert options.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function insertSerials()
    {
        $serialNumberTable = $this->_resource->getTableName('sirental_serialnumber_details');
        $productIds = [];
        $insert = [];
        foreach ($this->_cachedSerials as $entityId => $serials) {
            $productIds[] = $entityId;
            foreach ($serials as $key => $serial) {
                if ($tmpArray = $this->populateSerialTemplate($serial, $entityId, $key)) {
                    $insert[] = $tmpArray;
                }
            }
        }
        $this->connection->insertOnDuplicate($serialNumberTable, $insert, ['notes', 'cost', 'date_acquired', 'status']);

        return $this;
    }

    /**
     * Delete prices.
     *
     * @param array $productIds
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deletePrices($productIds)
    {
        $priceTable = $this->_resource->getTableName('sirental_price');
        $productIdsInWhere = $this->connection->quoteInto('entity_id IN (?)', $productIds);
        $this->connection->delete($priceTable, $productIdsInWhere);

        return $this;
    }

    /**
     * Delete serial numbers.
     *
     * @param array $productIds
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteSerials($productIds)
    {
        $serialNumbersTable = $this->_resource->getTableName('sirental_serialnumber_details');
        $productIdsInWhere = $this->connection->quoteInto('product_id IN (?)', $productIds);
        $this->connection->delete($serialNumbersTable, $productIdsInWhere);

        return $this;
    }

    /**
     * Clear cached values between bunches.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function clear()
    {
        $this->_cachedPrices = [];
        $this->_cachedSerials = [];

        return $this;
    }

    protected function fixQuantities($ProductIds)
    {
        $Products = $this->_productFactory->create()->getCollection();
        $Products->addFieldToFilter('entity_id', ['in' => $ProductIds]);
        foreach ($Products as $Product) {
            if ($Product->getSirentSerialNumbersUse() == 1) {
                $SerialNumberCount = $this->connection->fetchOne('
					SELECT
						COUNT(*)
					FROM
						' .$this->_resource->getTableName('sirental_serialnumber_details').'
					WHERE
						product_id = "' .$Product->getId().'"
				');
                $Product->setSirentQuantity($SerialNumberCount);
                $Product->save();
            }
        }
    }
}
