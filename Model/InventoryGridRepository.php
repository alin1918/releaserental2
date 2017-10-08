<?php
/**
 * THIS IS THE REPOSITORY
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\InventoryGridRepositoryInterface;
use SalesIgniter\Rental\Model\ResourceModel\InventoryGrid as InventoryGridResource;
use SalesIgniter\Rental\Model\ResourceModel\InventoryGrid\CollectionFactory;

/**
 * Class InventoryGridRepository
 *
 * @package SalesIgniter\Rental\Model
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class InventoryGridRepository implements InventoryGridRepositoryInterface
{
    /**
     * @var $inventorygridResource
     */
    private $inventorygridResource;
    /**
     * @var $inventorygridFactory
     */
    private $inventorygridFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \SalesIgniter\Rental\Api\Data\InventoryGridSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * InventoryGridRepository constructor.
     *
     * @param \SalesIgniter\Rental\Model\ResourceModel\InventoryGrid                   $inventorygridResource
     * @param \SalesIgniter\Rental\Model\InventoryGridFactory                          $inventorygridFactory
     * @param \SalesIgniter\Rental\Model\ResourceModel\InventoryGrid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                             $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Api\Data\InventoryGridSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        InventoryGridResource $inventorygridResource,
        InventoryGridFactory $inventorygridFactory,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \SalesIgniter\Rental\Api\Data\InventoryGridSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->inventorygridResource = $inventorygridResource;
        $this->inventorygridFactory = $inventorygridFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param \SalesIgniter\Rental\Api\Data\InventoryGridInterface $inventorygrid
     *
     * @return int
     */
    public function save(\SalesIgniter\Rental\Api\Data\InventoryGridInterface $inventorygrid)
    {
        $this->inventorygridResource->save($inventorygrid);
        return $inventorygrid->getId();
    }

    /**
     * @param int $inventorygridId
     *
     * @return \SalesIgniter\Rental\Api\Data\InventoryGridInterface $serialNumberItem
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($inventorygridId)
    {
        $inventoryGridItem = $this->inventorygridFactory->create();
        $this->inventorygridResource->load($inventoryGridItem, $inventorygridId);
        if (!$inventoryGridItem->getId()) {
            throw new NoSuchEntityException('Custom does not exist');
        }
        return $inventoryGridItem;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     *
     * @return \SalesIgniter\Rental\Api\Data\InventoryGridSearchResultsInterface
     * @internal param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() === SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);

        return $searchResults;
    }

    /**
     * @param int $inventorygridId
     *
     * @return bool
     */
    public function delete($inventorygridId)
    {
        $inventorygrid = $this->getById($inventorygridId);
        if ($this->inventorygridResource->delete($inventorygrid)) {
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
     * @param InventoryGridResource\Collection          $collection
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

    public function saveFromArray($data)
    {
        $inventoryGrid = $this->inventorygridFactory->create();
        $inventoryGrid->setData($data);
        return $this->save($inventoryGrid);
    }

    public function deleteByProductId($productId)
    {
        $this->searchCriteriaBuilder->addFilter('product_id', $productId);
        $items = $this->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($items as $item) {
            $this->delete($item->getId());
        }
    }
}
