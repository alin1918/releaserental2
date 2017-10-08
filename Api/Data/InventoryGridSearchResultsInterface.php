<?php
/**
 *
 */

namespace SalesIgniter\Rental\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface InventoryGridSearchResultsInterface
 *
 * @package SalesIgniter\Rental\Api\Data
 * @api
 */
interface InventoryGridSearchResultsInterface extends SearchResultsInterface
{

    /**
     * @return \SalesIgniter\Rental\Api\Data\InventoryGridInterface[]
     */
    public function getItems();

    /**
     * @param \SalesIgniter\Rental\Api\Data\InventoryGridInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
