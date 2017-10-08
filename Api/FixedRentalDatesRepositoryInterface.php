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
interface FixedRentalDatesRepositoryInterface
{
    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface $fixedRentalDates
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface $fixedRentalDates);

    /**
     * @param $fixedRentalDatesId
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($fixedRentalDatesId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalDatesSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $fixedRentalDatesId
     *
     * @return bool
     */
    public function delete($fixedRentalDatesId);

    /**
     * @param DataObject $dataObject
     *
     * @return int
     */
    public function saveFromObjectData(DataObject $dataObject);

    /**
     * @param array|DataObject $data
     *
     * @return int
     */
    public function saveData($data);

    /**
     * @param $fixedDateId
     *
     * @return array
     */
    public function getByIdAsArray($fixedDateId);

    /**
     * @param $nameId
     *
     * @return array
     */
    public function getByNameIdAsArray($nameId);

    /**
     * @param $nameId
     *
     * @return bool
     */
    public function deleteByNameId($nameId);
}
