<?php

namespace SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames;

/**
 * Class Collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'name_id';

    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\FixedRentalNames', 'SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames');
    }
}
