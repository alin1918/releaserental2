<?php
/**
 *
 */

namespace SalesIgniter\Rental\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;


/**
 * Interface SerialNumberDetailsSearchResultsInterface
 *
 * @package SalesIgniter\Rental\Api\Data
 * @api
 */
interface SerialNumberDetailsSearchResultsInterface extends SearchResultsInterface
{

    /**
     * @return \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface[]
     */
    public function getItems();

    /**
     * @param \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
