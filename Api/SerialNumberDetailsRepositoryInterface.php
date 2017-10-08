<?php
/**
 * THIS IS A SERVICE CONTRACT
 */
namespace SalesIgniter\Rental\Api;

use Magento\Framework\DataObject;

/**
 * @api
 * Interface CustomRepositoryInterface
 * @package Namespace\Custom\api
 */
interface SerialNumberDetailsRepositoryInterface
{
    /**
     * @param \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface $serialNumberDetails
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface $serialNumberDetails);

    /**
     * @param $serialNumberDetailsId
     *
     * @return \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($serialNumberDetailsId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\SerialNumberDetailsSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $serialNumberDetailsId
     *
     * @return bool
     */
    public function delete($serialNumberDetailsId);

    /**
     * @param DataObject $dataObject
     *
     * @return int
     */
    public function saveFromObjectData(DataObject $dataObject);

    /**
     * @param $serialNumberId
     *
     * @return array
     */
    public function getByIdAsArray($serialNumberId);

    /**
     * @param $productId
     *
     * @return array
     */
    public function getByProductIdAsArray($productId);

    /**
     * @param int    $productId
     * @param string $status
     * @param array  $serialList
     *
     * @param        $reservationId
     *
     * @return mixed
     */
    public function updateSerials($productId, $status, $serialList, $reservationId);

    /**
     * @param $productId
     *
     * @return bool
     */
    public function deleteByProductId($productId);
}
