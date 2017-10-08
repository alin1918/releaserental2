<?php
/**
 *
 */

namespace SalesIgniter\Rental\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CustomSearchResultsInterface
 *
 * @package SalesIgniter\Rental\Api\Data
 * @api
 */
interface FixedRentalDatesSearchResultsInterface extends SearchResultsInterface
{

    /**
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface[]
     */
    public function getItems();

    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
