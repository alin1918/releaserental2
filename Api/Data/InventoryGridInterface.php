<?php
/**
 * THIS IS THE DATA CONTAINER
 *
 */

namespace SalesIgniter\Rental\Api\Data;

/**
 * Interface InventoryGridInterface
 *
 * @package SalesIgniter\Rental\api\Data
 * @api
 */
interface InventoryGridInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);
}
