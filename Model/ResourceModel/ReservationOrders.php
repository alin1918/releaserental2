<?php

namespace SalesIgniter\Rental\Model\ResourceModel;

class ReservationOrders extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $stock;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \SalesIgniter\Rental\Model\Product\Stock          $stock
     * @param string                                            $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \SalesIgniter\Rental\Model\Product\Stock $stock,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->stock = $stock;
    }

    /**
     * Initialize resource model
     * Get table name from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sirental_reservationorders', 'reservationorder_id');
    }

    /**
     * Load Reservation Orders for order item
     *
     * @param int $orderItemId
     *
     * @return array
     */
    public function loadByOrderItemId($orderItemId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from($this->getMainTable())//$columns
            ->where('order_item_id = ?', $orderItemId);

        return $connection->fetchAll($select);
    }

    /**
     * Load Reservation Quotes for quote item
     *
     * @param int $productId
     *
     * @return array
     */
    public function loadReservationDataForProduct($productId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from($this->getMainTable())//$columns
            ->where('product_id=?', $productId);

        return $connection->fetchAll($select);
    }

    /**
     * Delete Reservation Order for orderId
     *
     * @param int $orderId
     *
     * @return int The number of affected rows
     */
    public function deleteByOrderId($orderId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $conds[] = $connection->quoteInto('order_id' . ' = ?', $orderId);
        //$conds[] = $connection->quoteInto('quote_item_parent_id = ?', $quoteItemId);

        if (count($conds) > 1) {
            $where = implode(' OR ', $conds);
        } else {
            $where = implode(' ', $conds);
        }

        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * Save Reservation Order object
     *
     * @param \Magento\Framework\DataObject $quoteObject
     *
     * @return \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders
     */
    public function saveOrderData(\Magento\Framework\DataObject $quoteObject)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($quoteObject, $this->getMainTable());
        $orderItemIdFieldName = $this->getOrderItemIdFieldName();

        if (array_key_exists($orderItemIdFieldName, $data)) {
            $where = $connection->quoteInto($orderItemIdFieldName . ' = ?', $data[$orderItemIdFieldName]);
            unset($data[$orderItemIdFieldName]);
            $connection->update($this->getMainTable(), $data, $where);
        } else {
            $connection->insert($this->getMainTable(), $data);
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    protected function getOrderItemIdFieldName()
    {
        /*$table = $this->getTable('catalog_product_entity');
        $indexList = $this->getConnection()->getIndexList($table);
        return $indexList[$this->getConnection()->getPrimaryKeyName($table)]['COLUMNS_LIST'][0];*/
        return 'reservationorder_id';
    }
}
