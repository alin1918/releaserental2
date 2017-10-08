<?php
namespace SalesIgniter\Rental\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class InventoryGrid
 *
 * @package SalesIgniter\Rental\Model\ResourceModel
 */
class InventoryGrid extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sirental_inventory_grid', 'id');
    }
}
