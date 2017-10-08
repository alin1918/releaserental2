<?php
/**
 *
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Model\AbstractModel;
use SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface;

class FixedRentalDates extends AbstractModel implements FixedRentalDatesInterface
{
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates');
    }

    /**
     * @param $fixedname
     *
     * @return $this
     */
    public function setDateId($fixedname)
    {
        return $this->setData('date_id', $fixedname);
    }

    public function getDateId()
    {
        return $this->getData('date_id');
    }

    /**
     * @param $fixedname
     *
     * @return $this
     */
    public function setNameId($fixedname)
    {
        return $this->setData('name_id', $fixedname);
    }

    public function getNameId()
    {
        return $this->getData('name_id');
    }

    /**
     * @return int
     */
    public function getWeekMonth()
    {
        return $this->getData('week_month');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setWeekMonth($id)
    {
        return $this->setData('week_month', $id);
    }

    /**
     * @return int
     */
    public function getRepeatType()
    {
        return $this->getData('repeat_type');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setRepeatType($id)
    {
        return $this->setData('repeat_type', $id);
    }

    /**
     * @return int
     */
    public function getDateTo()
    {
        return $this->getData('date_to');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDateTo($id)
    {
        return $this->setData('date_to', $id);
    }

    /**
     * @return int
     */
    public function getDateFrom()
    {
        return $this->getData('date_from');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDateFrom($id)
    {
        return $this->setData('date_from', $id);
    }

    /**
     * @return int
     */
    public function getAllDay()
    {
        return $this->getData('all_day');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setAllDay($id)
    {
        return $this->setData('all_day', $id);
    }

    /**
     * @return int
     */
    public function getRepeatDays()
    {
        return $this->getData('repeat_days');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setRepeatDays($id)
    {
        return $this->setData('repeat_days', $id);
    }
}
