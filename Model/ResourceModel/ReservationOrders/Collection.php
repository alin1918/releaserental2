<?php

namespace SalesIgniter\Rental\Model\ResourceModel\ReservationOrders;

//use \Magento\Eav\Api\AttributeRepositoryInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'reservationorder_id';

    /**
     * Constructor
     * Configures collection.
     */
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ReservationOrders', 'SalesIgniter\Rental\Model\ResourceModel\ReservationOrders');
    }

    /**
     * adding email to customer name column.
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        /*$filterSubSelect = $this->getSelect()->getConnection()->select();
        $filterSubSelect->from(
            ['cpev' => $this->getTable('catalog_product_entity_varchar')],
            ['value as name']
        )->joinLeft(
            ['ea' => $this->getTable('eav_attribute')],
            'cpev.attribute_id = ea.attribute_id',
            []
        )->where(
            'ea.attribute_code = ?', 'name'
        )->where(
            'cpev.entity_id = main_table.product_id'
        )->limit(1);

        $columnExpression = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DB\Sql\ColumnValueExpressionFactory')->create([
            'expression' => '('.$filterSubSelect->__toString().')',
        ]);
        $this->getSelect()->columns(['name' => $columnExpression]);*/
        
        $this->getSelect()->joinLeft(
            ['ot' => $this->getTable('sales_order')],
            'main_table.order_id = ot.entity_id',
            ['increment_id']
        );
                
        $nameAttr = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Magento\Eav\Model\Config')
            ->getAttribute('catalog_product', 'name');
        $this->getSelect()->joinInner(
            ['at_name_default' => $this->getTable('catalog_product_entity_varchar')],
            "at_name_default.entity_id = main_table.product_id "
                . "AND at_name_default.attribute_id = {$nameAttr->getAttributeId()} "
                . "AND at_name_default.store_id = 0"
        );

        $this->getSelect()->joinLeft(
            ['at_name' => $this->getTable('catalog_product_entity_varchar')],
            "at_name.entity_id = main_table.product_id "
                . "AND at_name.attribute_id = {$nameAttr->getAttributeId()} "
                . "AND at_name.store_id = ot.store_id"
        );

        $columnExpression = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DB\Sql\ColumnValueExpressionFactory')->create([
            'expression' => 'IF(at_name.value_id > 0, at_name.value, at_name_default.value)',
        ]);
        $this->getSelect()->columns(['name' => $columnExpression]);
                
        return $this;
    }

    /**
     * Checks if there are still items to ship for the reservation order.
     *
     * @return $this
     */
    public function filterByShipped()
    {
        $this->getSelect()->where('(main_table.parent_id <> 0) AND  
               (main_table.qty_shipped > 0 AND main_table.qty_returned = 0)
        ');

        return $this;
    }

    /**
     * Checks if there are still items to ship for the reservation order.
     *
     * @return $this
     */
    public function filterByShippedOrReturned()
    {
        $this->getSelect()->where('(main_table.parent_id <> 0) AND  
               (main_table.qty_shipped > 0)
        ');

        return $this;
    }

    /**
     * Checks if there are still items to return for the reservation order.
     *
     * @return $this
     */
    public function filterByReturned()
    {
        $this->getSelect()->where('(main_table.parent_id <> 0) AND  
               (main_table.qty_returned > 0)
        ');

        return $this;
    }

    /**
     * Checks if there are still items to ship for the reservation order.
     *
     * @return $this
     */
    public function filterByNoShip()
    {
        $this->getSelect()->where('(main_table.qty-main_table.qty_cancel > 0)AND (main_table.parent_id = 0) AND  
               ((main_table.qty - main_table.qty_cancel) - main_table.qty_shipped > 0 OR main_table.qty_shipped = 0)
        ');

        return $this;
    }

    /**
     * Checks main reservations.
     *
     * @return $this
     */
    public function filterByNotManual()
    {
        $this->getSelect()->where('main_table.order_id <> 0');

        return $this;
    }

    /**
     * Checks if there are still items to return for the reservation order.
     *
     * @return $this
     */
    public function filterByToReturn()
    {
        $this->getSelect()->where('(main_table.qty_shipped - main_table.qty_returned > 0) AND main_table.parent_id = 0'
        );

        return $this;
    }

    /**
     * Checks main reservations.
     *
     * @return $this
     */
    public function filterByMain()
    {
        $this->getSelect()->where('main_table.parent_id = 0');

        return $this;
    }

    /**
     * Checks if there are still items to return for the reservation order.
     *
     * @param $orderId
     *
     * @return $this
     */
    public function filterByOrderId($orderId)
    {
        $this->getSelect()->where('main_table.order_id = ?', $orderId);

        return $this;
    }

    /**
     * Checks if there are still items to return for the reservation order.
     *
     * @param $resOrderId
     *
     * @return $this
     */
    public function filterByResOrderId($resOrderId)
    {
        $this->getSelect()->where('main_table.reservationorder_id = ?', $resOrderId);

        return $this;
    }

    /**
     * Add shipment.
     *
     * @return $this
     */
    public function addShipments()
    {
        $this->getSelect()->joinLeft(
            ['shipment_item_table' => $this->getTable('sales_shipment_item')],
            'main_table.shipment_item_id = shipment_item_table.entity_id'
        );

        return $this;
    }
}
