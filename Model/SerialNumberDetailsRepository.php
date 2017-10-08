<?php
/**
 * THIS IS THE REPOSITORY
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\SerialNumberDetailsRepositoryInterface;
use SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails as SerialNumberDetailsResource;
use SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory;

/**
 * Class SerialNumberDetailsRepository
 *
 * @package SalesIgniter\Rental\Model
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SerialNumberDetailsRepository implements SerialNumberDetailsRepositoryInterface
{
    /**
     * @var $serialNumberDetailsResource
     */
    private $serialNumberDetailsResource;
    /**
     * @var $serialNumberDetailsFactory
     */
    private $serialNumberDetailsFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \SalesIgniter\Rental\Api\Data\SerialNumberDetailsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * SerialNumberDetailsRepository constructor.
     *
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails                   $serialNumberDetailsResource
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsFactory                          $serialNumberDetailsFactory
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $collectionFactory
     * @param \SalesIgniter\Rental\Api\Data\SerialNumberDetailsSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        SerialNumberDetailsResource $serialNumberDetailsResource,
        SerialNumberDetailsFactory $serialNumberDetailsFactory,
        CollectionFactory $collectionFactory,
        \SalesIgniter\Rental\Api\Data\SerialNumberDetailsSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->serialNumberDetailsResource = $serialNumberDetailsResource;
        $this->serialNumberDetailsFactory = $serialNumberDetailsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface $serialNumberDetails
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface $serialNumberDetails)
    {
        $this->serialNumberDetailsResource->save($serialNumberDetails);
        return $serialNumberDetails->getId();
    }

    /**
     * @param int $serialNumberDetailsId
     *
     * @return \SalesIgniter\Rental\Api\Data\SerialNumberDetailsInterface $serialNumberItem
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($serialNumberDetailsId)
    {
        $serialNumberItem = $this->serialNumberDetailsFactory->create();
        $this->serialNumberDetailsResource->load($serialNumberItem, $serialNumberDetailsId);
        if (!$serialNumberItem->getId()) {
            throw new NoSuchEntityException('Custom does not exist');
        }
        return $serialNumberItem;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\SerialNumberDetailsSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                $this->getDirection($sortOrder->getDirection())
            );
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $serialNumberDetailss = [];
        foreach ($collection as $serialNumberDetails) {
            $serialNumberDetailss[] = $serialNumberDetails;
        }
        $searchResults->setItems($serialNumberDetailss);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param int $serialNumberDetailsId
     *
     * @return bool
     */
    public function delete($serialNumberDetailsId)
    {
        $serialNumberDetails = $this->getById($serialNumberDetailsId);
        if ($this->serialNumberDetailsResource->delete($serialNumberDetails)) {
            return true;
        } else {
            return false;
        }
    }

    private function getDirection($direction)
    {
        return $direction === SortOrder::SORT_ASC ?: SortOrder::SORT_DESC;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $group
     * @param SerialNumberDetailsResource\Collection    $collection
     */
    private function addFilterGroupToCollection($group, $collection)
    {
        $fields = [];
        $conditions = [];

        foreach ($group->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $field = $filter->getField();
            $value = $filter->getValue();
            $fields[] = $field;
            $conditions[] = [$condition => $value];
        }
        $collection->addFieldToFilter($fields, $conditions);
    }

    /**
     * @param DataObject $dataObject
     *
     * @return int
     */
    public function saveFromObjectData(DataObject $dataObject)
    {
        $serialNumberItem = $this->serialNumberDetailsFactory->create();
        $serialNumberItem->setData($dataObject->getData());
        return $this->save($serialNumberItem);
    }

    /**
     * @param $serialNumberId
     *
     * @return array
     */
    public function getByIdAsArray($serialNumberId)
    {
        $serialItem = $this->getById($serialNumberId);
        return $serialItem->getData();
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getByProductIdAsArray($productId)
    {
        return $this->serialNumberDetailsResource->loadByProductId($productId);
    }

    /**
     * @param int    $productId
     * @param string $status
     * @param array  $serialList
     *
     * @param        $reservationId
     *
     * @return int
     */
    public function updateSerials($productId, $status, $serialList, $reservationId){
        return $this->serialNumberDetailsResource->updateSerials($productId, $status, $serialList, $reservationId);
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    public function deleteByProductId($productId)
    {
        $serialNumberDetailsRowsAffected = $this->serialNumberDetailsResource->deleteByProductId($productId);
        if ($serialNumberDetailsRowsAffected > 0) {
            return true;
        } else {
            return false;
        }
    }
}
