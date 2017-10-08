<?php
/**
 * Copyright © 2016 SalesIgniter. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProductModel;
use Magento\ImportExport\Model\Import as ImportModel;
use SalesIgniter\Rental\Model\PriceFactory as PriceFactory;
use SalesIgniter\Rental\Model\SerialNumberDetailsFactory as SerialNumberFactory;

/**
 * Class RowCustomizer.
 */
class RowCustomizer implements RowCustomizerInterface
{
    const RENTAL_PRICE_COL = 'sirent_prices';
    const RENTAL_SERIAL_NUMBERS_COL = 'sirent_serials';

    /**
     * Bundle product columns.
     *
     * @var array
     */
    protected $rentalColumns = [
        self::RENTAL_PRICE_COL,
        self::RENTAL_SERIAL_NUMBERS_COL,
    ];

    /**
     * Product's bundle data.
     *
     * @var array
     */
    protected $rentalData = [];

    /**
     * @var PriceFactory
     */
    protected $_priceFactory;

    /**
     * @var SerialNumberFactory
     */
    protected $_serialNumberFactory;

    /**
     * RowCustomizer constructor.
     *
     * @param PriceFactory        $PriceFactory
     * @param SerialNumberFactory $SerialNumberFactory
     */
    public function __construct(
        PriceFactory $PriceFactory,
        SerialNumberFactory $SerialNumberFactory
    ) {
        $this->_priceFactory = $PriceFactory;
        $this->_serialNumberFactory = $SerialNumberFactory;
    }

    /**
     * Retrieve list of bundle specific columns.
     *
     * @return array
     */
    private function getRentalColumns()
    {
        return $this->rentalColumns;
    }

    /**
     * Prepare data for export.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param int[]                                                   $productIds
     *
     * @return $this
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToFilter(
            'type_id',
            ['eq' => \SalesIgniter\Rental\Model\Product\Type\Sirent::TYPE_RENTAL]
        );

        return $this->populateRentalData($productCollection);
    }

    /**
     * Set headers columns.
     *
     * @param array $columns
     *
     * @return array
     */
    public function addHeaderColumns($columns)
    {
        $columns = array_merge($columns, $this->getRentalColumns());

        return $columns;
    }

    /**
     * Add data for export.
     *
     * @param array $dataRow
     * @param int   $productId
     *
     * @return array
     */
    public function addData($dataRow, $productId)
    {
        if (!empty($this->rentalData[$productId])) {
            $dataRow = array_merge($this->cleanNotRentalAdditionalAttributes($dataRow), $this->rentalData[$productId]);
        }

        return $dataRow;
    }

    /**
     * Calculate the largest links block.
     *
     * @param array $additionalRowsCount
     * @param int   $productId
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    /**
     * Populate rental product data.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return $this
     */
    protected function populateRentalData($collection)
    {
        foreach ($collection as $product) {
            $id = $product->getEntityId();

            //Get Prices From Pricing Table
            /** @var \SalesIgniter\Rental\Model\ResourceModel\Price\Collection $Prices */
            $Prices = $this->_priceFactory->create()->getCollection();
            $Prices->addFieldToFilter('entity_id', ['eq' => $id]);

            $rowString = '';
            foreach ($Prices as $Price) {
                $rowData = [
                    'website_id' => $Price->getWebsiteId(),
                    'period' => $Price->getPeriod(),
                    'price' => $Price->getPrice(),
                    'qty_start' => $Price->getQtyStart(),
                    'qty_end' => $Price->getQtyEnd(),
                    'customer_group_id' => $Price->getCustomerGroupId(),
                    'all_groups' => $Price->getAllGroups(),
                    //'pricesbydate_id'   => $Price->getPricesbydateId(),
                    'price_additional' => $Price->getPriceAdditional(),
                    'period_additional' => $Price->getPeriodAdditional(),
                ];

                $rowString .= implode(
                        ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        array_map(function ($value, $key) {
                            return $key.ImportProductModel::PAIR_NAME_VALUE_SEPARATOR.$value;
                        }, $rowData, array_keys($rowData))
                    )
                    .ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR;
            }

            $this->rentalData[$id][self::RENTAL_PRICE_COL] = $rowString;

            //Get Serials From Serials Table
            /** @var \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\Collection $SerialNumbers */
            $SerialNumbers = $this->_serialNumberFactory->create()->getCollection();
            $SerialNumbers->addFieldToFilter('product_id', ['eq' => $id]);

            $rowString = '';
            foreach ($SerialNumbers as $SerialNumber) {
                $rowData = [
                    'serialnumber' => $SerialNumber->getSerialnumber(),
                    'notes' => $SerialNumber->getNotes(),
                    'cost' => $SerialNumber->getCost(),
                    'date_acquired' => $SerialNumber->getDateAcquired(),
                    'status' => $SerialNumber->getStatus(),
                ];

                $rowString .= implode(
                        ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        array_map(function ($value, $key) {
                            return $key.ImportProductModel::PAIR_NAME_VALUE_SEPARATOR.$value;
                        }, $rowData, array_keys($rowData))
                    )
                    .ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR;
            }

            $this->rentalData[$id][self::RENTAL_SERIAL_NUMBERS_COL] = $rowString;
        }

        return $this;
    }

    /**
     * Retrieves additional attributes as array code=>value.
     *
     * @param string $additionalAttributes
     *
     * @return array
     */
    private function parseAdditionalAttributes($additionalAttributes)
    {
        $attributeNameValuePairs = explode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributes);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            if (strpos($attributeData, ImportProductModel::PAIR_NAME_VALUE_SEPARATOR) === false) {
                if (!$code) {
                    continue;
                }
                $preparedAttributes[$code] .= ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR.$attributeData;
                continue;
            }
            list($code, $value) = explode(ImportProductModel::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
            $preparedAttributes[$code] = $value;
        }

        return $preparedAttributes;
    }

    /**
     * Remove bundle specified additional attributes as now they are stored in specified columns.
     *
     * @param array $dataRow
     *
     * @return array
     */
    protected function cleanNotRentalAdditionalAttributes($dataRow)
    {
        if (!empty($dataRow['additional_attributes'])) {
            $additionalAttributes = $this->parseAdditionalAttributes($dataRow['additional_attributes']);
            $dataRow['additional_attributes'] = $this->getNotRentalAttributes($additionalAttributes);
        }

        return $dataRow;
    }

    /**
     * Retrieve not rental additional attributes.
     *
     * @param array $additionalAttributes
     *
     * @return string
     */
    protected function getNotRentalAttributes($additionalAttributes)
    {
        $filteredAttributes = [];
        foreach ($additionalAttributes as $code => $value) {
            if (!in_array('sirent_'.$code, $this->getRentalColumns())) {
                $filteredAttributes[] = $code.ImportProductModel::PAIR_NAME_VALUE_SEPARATOR.$value;
            }
        }

        return implode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $filteredAttributes);
    }
}
