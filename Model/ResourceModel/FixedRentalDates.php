<?php

namespace SalesIgniter\Rental\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class FixedRentalDates.
 */
class FixedRentalDates extends AbstractDb
{
    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $this->_init('sirental_fixed_dates', 'date_id');
    }

    /**
     * Load Data.
     *
     * @param int $itemId
     *
     * @return array
     */
    public function loadByNameId($itemId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $quoteIdFieldName = $this->getDataIdFieldName();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from($this->getMainTable())//$columns
            ->where("{$quoteIdFieldName} = ?", $itemId);

        return $connection->fetchAll($select);
    }

    /**
     * Delete Data.
     *
     * @param int $itemId
     *
     * @return int The number of affected rows
     */
    public function deleteByNameId($itemId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $conds[] = $connection->quoteInto($this->getDataIdFieldName().' = ?', $itemId);

        if (count($conds) > 1) {
            $where = implode(' OR ', $conds);
        } else {
            $where = implode(' ', $conds);
        }

        return $connection->delete($this->getMainTable(), $where);
    }

    /**
     * @return string
     */
    protected function getDataIdFieldName()
    {
        return 'name_id';
    }
}
