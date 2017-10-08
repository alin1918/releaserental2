<?php
/**
 * THIS IS A SERVICE CONTRACT.
 */

namespace SalesIgniter\Rental\Api;

use Magento\Framework\DataObject;

/**
 * @api
 * Interface CustomRepositoryInterface
 */
interface FixedRentalNamesRepositoryInterface
{
    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface $fixedRentalNames
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface $fixedRentalNames);

    /**
     * @param $fixedRentalNamesId
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($fixedRentalNamesId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalNamesSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $fixedRentalNamesId
     *
     * @return bool
     */
    public function delete($fixedRentalNamesId);

    /**
     * @param DataObject $dataObject
     *
     * @return int
     */
    public function saveFromObjectData(DataObject $dataObject);

    /**
     * @param array | DataObject $data
     *
     * @return int
     */
    public function saveData($data);

    /**
     * @param $fixedNamesId
     *
     * @return array
     */
    public function getByIdAsArray($fixedNamesId);

    /**
     * @param $productId
     *
     * @return array
     */
    public function getByProductIdAsArray($productId);

    /**
     * @param $productId
     *
     * @return bool
     */
    public function deleteByProductId($productId);
}
