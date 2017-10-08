<?php

namespace SalesIgniter\Rental\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class FixedRentalNames.
 */
class FixedRentalNames extends AbstractDb
{
    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $this->_init('sirental_fixed_names', 'name_id');
    }
}
