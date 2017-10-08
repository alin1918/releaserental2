<?php

namespace SalesIgniter\Rental\Model\ResourceModel;

class Price extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get table name from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sirental_price', 'price_id');
    }

    /**
     * Load Rental Prices for product
     *
     * @param int $productId
     * @param int $websiteId
     *
     * @return array
     */
    public function loadPriceData($productId, $websiteId = null)
    {
        $connection = $this->getConnection();

        $columns = [
            'price_id' => $this->getIdFieldName(),
            'website_id' => 'website_id',
            'all_groups' => 'all_groups',
            'customer_group_id' => 'customer_group_id',
            'price' => 'price',
            'period' => 'period',
            'price_additional' => 'price_additional',
            'period_additional' => 'period_additional',
            'qty_start' => 'qty_start',
            'qty_end' => 'qty_end',
            /*'pricesbydate_id' => 'pricesbydate_id',*/
        ];

        $columns = $this->_loadPriceDataColumns($columns);

        $productIdFieldName = $this->getProductIdFieldName();
        $select = $connection->select()
            ->from($this->getMainTable(), $columns)
            ->where("{$productIdFieldName} = ?", $productId);

        $this->_loadPriceDataSelect($select);

        if ($websiteId !== null) {
            if ($websiteId === '0') {
                $select->where('website_id = ?', $websiteId);
            } else {
                $select->where('website_id IN(?)', [0, $websiteId]);
            }
        }

        return $connection->fetchAll($select);
    }

    /**
     * @return string
     */
    protected function getProductIdFieldName()
    {
        $table = $this->getTable('catalog_product_entity');
        $indexList = $this->getConnection()->getIndexList($table);
        return $indexList[$this->getConnection()->getPrimaryKeyName($table)]['COLUMNS_LIST'][0];
    }

    /**
     * Load specific sql columns
     *
     * @param array $columns
     *
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        return $columns;
    }

    /**
     * Load specific db-select data
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _loadPriceDataSelect($select)
    {
        return $select;
    }

    /**
     * Delete Rental Prices for product
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $priceId
     *
     * @return int The number of affected rows
     */
    public function deletePriceData($productId, $websiteId = null, $priceId = null)
    {
        $connection = $this->getConnection();

        $conds = [$connection->quoteInto($this->getProductIdFieldName() . ' = ?', $productId)];

        if ($websiteId !== null && $websiteId > 0) {
            $conds[] = $connection->quoteInto('website_id = ?', $websiteId);
        }

        if ($priceId !== null) {
            $conds[] = $connection->quoteInto($this->getIdFieldName() . ' = ?', $priceId);
        }

        $where = implode(' AND ', $conds);

        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * Save Rental price object
     *
     * @param \Magento\Framework\DataObject $priceObject
     *
     * @return \SalesIgniter\Rental\Model\ResourceModel\Price
     */
    public function savePriceData(\Magento\Framework\DataObject $priceObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($priceObject, $this->getMainTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getMainTable(), $data, $where);
        } else {
            $connection->insert($this->getMainTable(), $data);
        }
        return $this;
    }
}
