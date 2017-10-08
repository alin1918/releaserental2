<?php
/**
 * THIS IS THE DATA CONTAINER.
 */

namespace SalesIgniter\Rental\Api\Data;

/**
 * Interface CustomInterface.
 *
 * @api
 */
interface FixedRentalNamesInterface
{
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

    public function getName();

    public function setName($fixedname);

    public function getCatalogRules();

    public function setCatalogRules($fixedname);
}
