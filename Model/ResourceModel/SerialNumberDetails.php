<?php
namespace SalesIgniter\Rental\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SerialNumberDetails
 *
 * @package SalesIgniter\Rental\Model\ResourceModel
 */
class SerialNumberDetails extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sirental_serialnumber_details', 'serialnumber_details_id');
    }

    /**
     * Fetches serial id by serial number and product id. There should only be one
     *
     * @param $serial
     * @param $productid
     * @return string
     */

    public function loadByProductIdandSerialNumber($serial,$productid)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $serialnumfield = 'serialnumber';
        $quoteIdFieldName = $this->getProductIdFieldName();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from($this->getMainTable())//$columns
            ->where("{$serialnumfield} = ?", $serial)
            ->where("{$quoteIdFieldName} = ?", $productid);

        return $connection->fetchOne($select);
    }

    /**
     * Load Data
     *
     * @param int $itemId
     *
     * @return array
     */
    public function loadByProductId($itemId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $quoteIdFieldName = $this->getProductIdFieldName();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $connection->select()
            ->from($this->getMainTable())//$columns
            ->where("{$quoteIdFieldName} = ?", $itemId);

        return $connection->fetchAll($select);
    }

    /**
     * Delete Data
     *
     * @param int $itemId
     *
     * @return int The number of affected rows
     */
    public function deleteByProductId($itemId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->getConnection();

        $conds[] = $connection->quoteInto($this->getProductIdFieldName() . ' = ?', $itemId);

        if (count($conds) > 1) {
            $where = implode(' OR ', $conds);
        } else {
            $where = implode(' ', $conds);
        }

        return $connection->delete($this->getMainTable(), $where);
    }

    public function updateSerials($productId, $status, $serialList, $reservationId)
    {
        $connection = $this->getConnection();
        $data = [
            'status' => $status,
            'reservationorder_id' => $reservationId
        ];
        $where = [$connection->quoteInto('serialnumber in (?)', $serialList), 'product_id' => $productId];
        return $connection->update($this->getMainTable(), $data, $where);
    }

    /**
     *
     * @return string
     */
    protected function getProductIdFieldName()
    {
        return 'product_id';
    }

    /**
     * Updates serials with a specific maintenance_ticket_id to available
     *
     * @param $maintenanceid
     * @return int
     */

    public function updateMaintenanceIdToAvailable($maintenanceid){
        $connection = $this->getConnection();
        $data = [
            'status' => 'available'
        ];
        $where = ['maintenance_ticket_id' => $maintenanceid];
        return $connection->update($this->getMainTable(), $data, $where);
    }
}
