<?php

namespace SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails;

/**
 * Class Collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'serialnumber_details_id';

    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\SerialNumberDetails', 'SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails');
    }
}
