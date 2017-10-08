<?php
/**
 * THIS IS THE REPOSITORY.
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface;
use SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates as FixedRentalDatesResource;
use SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates\CollectionFactory;

/**
 * Class FixedRentalDatesRepository.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FixedRentalDatesRepository implements FixedRentalDatesRepositoryInterface
{
    /**
     * @var
     */
    private $fixedRentalDatesResource;
    /**
     * @var
     */
    private $fixedRentalDatesFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \SalesIgniter\Rental\Api\Data\FixedRentalDatesSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * FixedRentalDatesRepository constructor.
     *
     * @param \SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates                   $fixedRentalDatesResource
     * @param \SalesIgniter\Rental\Model\FixedRentalDatesFactory                          $fixedRentalDatesFactory
     * @param \SalesIgniter\Rental\Model\ResourceModel\FixedRentalDates\CollectionFactory $collectionFactory
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalDatesSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        FixedRentalDatesResource $fixedRentalDatesResource,
        FixedRentalDatesFactory $fixedRentalDatesFactory,
        CollectionFactory $collectionFactory,
        \SalesIgniter\Rental\Api\Data\FixedRentalDatesSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->fixedRentalDatesResource = $fixedRentalDatesResource;
        $this->fixedRentalDatesFactory = $fixedRentalDatesFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface $fixedRentalDates
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface $fixedRentalDates)
    {
        $this->fixedRentalDatesResource->save($fixedRentalDates);

        return $fixedRentalDates->getId();
    }

    /**
     * @param int $fixedRentalDatesId
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalDatesInterface $fixedDateItem
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($fixedRentalDatesId)
    {
        $fixedDateItem = $this->fixedRentalDatesFactory->create();
        $this->fixedRentalDatesResource->load($fixedDateItem, $fixedRentalDatesId);
        if (!$fixedDateItem->getId()) {
            throw new NoSuchEntityException('Custom does not exist');
        }

        return $fixedDateItem;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \SalesIgniter\Rental\Api\Data\FixedRentalDatesSearchResultsInterface
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

        $fixedRentalDatess = [];
        foreach ($collection as $fixedRentalDates) {
            $fixedRentalDatess[] = $fixedRentalDates;
        }
        $searchResults->setItems($fixedRentalDatess);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param int $fixedRentalDatesId
     *
     * @return bool
     */
    public function delete($fixedRentalDatesId)
    {
        $fixedRentalDates = $this->getById($fixedRentalDatesId);
        if ($this->fixedRentalDatesResource->delete($fixedRentalDates)) {
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
     * @param FixedRentalDatesResource\Collection       $collection
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
        $fixedDateItem = $this->fixedRentalDatesFactory->create();
        $fixedDateItem->setData($dataObject->getData());

        return $this->save($fixedDateItem);
    }

    /**
     * @param $data
     *
     * @return int
     */
    public function saveData($data)
    {
        $fixedNamesItem = $this->fixedRentalDatesFactory->create();
        if (is_object($data)) {
            $fixedNamesItem->setData($data->getData());
        } elseif (is_array($data)) {
            $fixedNamesItem->setData($data);
        }

        return $this->save($fixedNamesItem);
    }

    /**
     * @param $fixedDateId
     *
     * @return array
     */
    public function getByIdAsArray($fixedDateId)
    {
        $fixedItem = $this->getById($fixedDateId);

        return $fixedItem->getData();
    }

    /**
     * @param $nameId
     *
     * @return array
     */
    public function getByNameIdAsArray($nameId)
    {
        return $this->fixedRentalDatesResource->loadByNameId($nameId);
    }

    /**
     * @param int $nameId
     *
     * @return bool
     */
    public function deleteByNameId($nameId)
    {
        $fixedRentalDatesRowsAffected = $this->fixedRentalDatesResource->deleteByNameId($nameId);
        if ($fixedRentalDatesRowsAffected > 0) {
            return true;
        } else {
            return false;
        }
    }
}
