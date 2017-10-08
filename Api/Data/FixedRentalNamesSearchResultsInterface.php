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
interface FixedRentalNamesSearchResultsInterface extends SearchResultsInterface
{

    /**
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface[]
     */
    public function getItems();

    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
