<?php

namespace SalesIgniter\Rental\Model\ResourceModel\ReservationOrders;

//use \Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'reservationorder_id';

    /**
     * @var Attribute
     */
    private $eavAttribute;


    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Attribute $eavAttribute
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Attribute $eavAttribute,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->eavAttribute = $eavAttribute;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

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
       //$filterSubSelect = $this->getSelect()->getConnection()->select();
       //$filterSubSelect->from(
       //    ['cpev' => $this->getTable('catalog_product_entity_varchar')],
       //    ['value as name']
       //)->joinLeft(
       //    ['ea' => $this->getTable('eav_attribute')],
       //    'cpev.attribute_id = ea.attribute_id',
       //    []
       //)->where(
       //    'ea.attribute_code = ?', 'name'
       //)->where(
       //    'cpev.entity_id = main_table.product_id'
       //)->limit(1);

        $attributeId = $this->eavAttribute->getIdByCode('catalog_product', 'name');

        //$columnExpression = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DB\Sql\ColumnValueExpressionFactory')->create([
        //  'expression' => '('.$filterSubSelect->__toString().')',
        //]);
        //$this->getSelect()->columns(['name' => '('.$filterSubSelect->__toString().')']);
        $this->getSelect()->joinLeft(
            ['ot' => $this->getTable('sales_order')],
            'main_table.order_id = ot.entity_id',
            ['increment_id']
        );
        $this->getSelect()->joinLeft(
            ['cpev' => $this->getTable('catalog_product_entity_varchar')],
            'cpev.entity_id = main_table.product_id',
            ['name' => 'cpev.value']
        );
        $this->getSelect()->where('cpev.attribute_id = ?', $attributeId);
        $this->getSelect()->where('cpev.store_id = 0');
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
