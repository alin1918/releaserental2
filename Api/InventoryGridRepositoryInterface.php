<?php

namespace SalesIgniter\Rental\Api;

/**
 * @api
 * Interface InventoryGridRepositoryInterface
 * @package Namespace\Custom\api
 */
interface InventoryGridRepositoryInterface
{
    /**
     * @param \SalesIgniter\Rental\Api\Data\InventoryGridInterface $inventorygrid
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\InventoryGridInterface $inventorygrid);

    /**
     * @param $inventorygridId
     *
     * @return \SalesIgniter\Rental\Api\Data\InventoryGridInterface int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($inventorygridId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\InventoryGridSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $inventorygridId
     *
     * @return bool
     */
    public function delete($inventorygridId);

    /**
     * @param $data
     *
     * @return int
     */
    public function saveFromArray($data);

    public function deleteByProductId($productId);
}
