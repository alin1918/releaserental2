<?php
/**
 *
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Model\AbstractModel;
use SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface;

class SerialNumberDetails extends AbstractModel implements SerialNumberDetailsInterface
{
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails');
    }

    /**
     * @param int $serialnumberId
     *
     * @return $this
     */
    public function setSerialnumberDetailsId($serialnumberId)
    {
        return $this->setData('serialnumber_details_id', $serialnumberId);
    }

    public function getSerialnumberDetailsId()
    {
        return $this->getData('serialnumber_details_id');
    }

    /**
     * @param $serialnumber
     *
     * @return $this
     */
    public function setSerialnumber($serialnumber)
    {
        return $this->setData('serialnumber', $serialnumber);
    }

    public function getSerialnumber()
    {
        return $this->getData('serialnumber');
    }

    /**
     * @param $productId
     *
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @param $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        return $this->setData('notes', $notes);
    }

    public function getNotes()
    {
        return $this->getData('notes');
    }

    /**
     * @param $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        return $this->setData('cost', $cost);
    }

    public function getCost()
    {
        return $this->getData('cost');
    }

    /**
     * @param $dateAcquired
     *
     * @return $this
     */
    public function setDateAcquired($dateAcquired)
    {
        return $this->setData('date_acquired', $dateAcquired);
    }

    public function getDateAcquired()
    {
        return $this->getData('date_acquired');
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    public function getStatus()
    {
        return $this->getData('status');
    }
}
