<?php
/**
 * THIS IS THE REPOSITORY.
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;
use SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames as FixedRentalNamesResource;
use SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames\CollectionFactory;

/**
 * Class FixedRentalNamesRepository.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FixedRentalNamesRepository implements FixedRentalNamesRepositoryInterface
{
    /**
     * @var
     */
    private $fixedRentalNamesResource;
    /**
     * @var
     */
    private $fixedRentalNamesFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \SalesIgniter\Rental\Api\Data\FixedRentalNamesSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * FixedRentalNamesRepository constructor.
     *
     * @param \SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames                   $fixedRentalNamesResource
     * @param \SalesIgniter\Rental\Model\FixedRentalNamesFactory                          $fixedRentalNamesFactory
     * @param \SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames\CollectionFactory $collectionFactory
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalNamesSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        FixedRentalNamesResource $fixedRentalNamesResource,
        FixedRentalNamesFactory $fixedRentalNamesFactory,
        CollectionFactory $collectionFactory,
        \SalesIgniter\Rental\Api\Data\FixedRentalNamesSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->fixedRentalNamesResource = $fixedRentalNamesResource;
        $this->fixedRentalNamesFactory = $fixedRentalNamesFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface $fixedRentalNames
     *
     * @return int
     * @throws \Exception
     */
    public function save(\SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface $fixedRentalNames)
    {
        $this->fixedRentalNamesResource->save($fixedRentalNames);

        return $fixedRentalNames->getId();
    }

    /**
     * @param int $fixedRentalNamesId
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface $fixedNamesItem
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($fixedRentalNamesId)
    {
        $fixedNamesItem = $this->fixedRentalNamesFactory->create();
        $this->fixedRentalNamesResource->load($fixedNamesItem, $fixedRentalNamesId);
        if (!$fixedNamesItem->getId()) {
            throw new NoSuchEntityException('Custom does not exist');
        }

        return $fixedNamesItem;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalNamesSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var Magento\Framework\Api\SortOrder $sortOrder */
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
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

        $fixedRentalNamess = [];
        foreach ($collection as $fixedRentalNames) {
            $fixedRentalNamess[] = $fixedRentalNames;
        }
        $searchResults->setItems($fixedRentalNamess);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param int $fixedRentalNamesId
     *
     * @return bool
     */
    public function delete($fixedRentalNamesId)
    {
        $fixedRentalNames = $this->getById($fixedRentalNamesId);
        if ($this->fixedRentalNamesResource->delete($fixedRentalNames)) {
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
     * @param FixedRentalNamesResource\Collection       $collection
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
        $fixedNamesItem = $this->fixedRentalNamesFactory->create();
        $fixedNamesItem->setData($dataObject->getData());

        return $this->save($fixedNamesItem);
    }

    /**
     * @param $data
     *
     * @return int
     */
    public function saveData($data)
    {
        $fixedNamesItem = $this->fixedRentalNamesFactory->create();
        if (is_object($data)) {
            $fixedNamesItem->setData($data->getData());
        } elseif (is_array($data)) {
            $fixedNamesItem->setData($data);
        }

        return $this->save($fixedNamesItem);
    }

    /**
     * @param $fixedNamesId
     *
     * @return array
     */
    public function getByIdAsArray($fixedNamesId)
    {
        $fixednameItem = $this->getById($fixedNamesId);

        return $fixednameItem->getData();
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getByProductIdAsArray($productId)
    {
        return $this->fixedRentalNamesResource->loadByProductId($productId);
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    public function deleteByProductId($productId)
    {
        $fixedRentalNamesRowsAffected = $this->fixedRentalNamesResource->deleteByProductId($productId);
        if ($fixedRentalNamesRowsAffected > 0) {
            return true;
        } else {
            return false;
        }
    }
}
