<?php
namespace SalesIgniter\Rental\Model\ResourceModel\InventoryGrid;

/**
 * Class Collection
 *
 * @package SalesIgniter\Rental\Model\ResourceModel\InventoryGrid
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\InventoryGrid', 'SalesIgniter\Rental\Model\ResourceModel\InventoryGrid');
    }
}
