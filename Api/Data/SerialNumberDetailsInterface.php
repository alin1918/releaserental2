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
interface SerialNumberDetailsInterface
{
    /**
     * @return int
     */
    public function getSerialnumberDetailsId();

    /**
     * @param int $serialId
     *
     * @return $this
     */
    public function setSerialnumberDetailsId($serialId);

    public function getSerialnumber();

    public function setSerialnumber($serialnumber);

    public function getProductId();

    public function setProductId($productId);

    public function getNotes();

    public function setNotes($notes);

    public function getCost();

    public function setCost($cost);

    public function getDateAcquired();

    public function setDateAcquired($dateAcquired);

    public function getStatus();

    public function setStatus($status);
}
