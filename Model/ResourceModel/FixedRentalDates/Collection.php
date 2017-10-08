<?php

namespace SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates;

/**
 * Class Collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'date_id';

    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\FixedRentalDates', 'SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates');
    }
}
