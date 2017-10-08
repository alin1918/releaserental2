<?php
/**
 * THIS IS THE DATA CONTAINER
 *
 */

namespace SalesIgniter\Rental\Api\Data;

/**
 * Interface CustomInterface
 *
 * @package SalesIgniter\Rental\api\Data
 * @api
 */
interface FixedRentalDatesInterface
{
    /**
     * @return int
     */
    public function getDateId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDateId($id);

    /**
     * @return int
     */
    public function getNameId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setNameId($id);

    /**
     * @return int
     */
    public function getWeekMonth();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setWeekMonth($id);

    /**
     * @return int
     */
    public function getRepeatType();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setRepeatType($id);

    /**
     * @return int
     */
    public function getDateTo();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDateTo($id);

    /**
     * @return int
     */
    public function getDateFrom();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDateFrom($id);
    /**
     * @return int
     */
    public function getAllDay();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setAllDay($id);

    /**
     * @return int
     */
    public function getRepeatDays();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setRepeatDays($id);



}
