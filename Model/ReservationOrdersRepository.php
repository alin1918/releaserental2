<?php

namespace SalesIgniter\Rental\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\InventoryGridRepositoryInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory;

/**
 * Class ReservationOrdersRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @package SalesIgniter\Rental\Model
 */
class ReservationOrdersRepository implements \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
{
    /**
     * @var \SalesIgniter\Rental\Model\ReservationOrdersFactory
     */
    protected $objectFactory;
    /**
     * @var \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders
     */
    protected $reservationOrderResource;
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    protected $stock;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    protected $calendarHelper;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    protected $sortOrderBuilder;
    /**
     * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
     */
    protected $serialNumberDetailsRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $attributeAction;
    /**
     * @var \SalesIgniter\Rental\Api\InventoryGridRepositoryInterface
     */
    protected $inventoryGridRepository;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * ReservationOrdersRepository constructor.
     *
     * @param \SalesIgniter\Rental\Model\ReservationOrdersFactory                          $objectFactory
     * @param \SalesIgniter\Rental\Helper\Calendar                                         $calendarHelper
     * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory
     * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders                   $reservationOrderResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                                  $datetime
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository                     $serialNumberDetailsRepository
     * @param Stock                                                                        $stock
     * @param \Magento\Framework\Api\SortOrderBuilder                                      $sortOrderBuilder
     * @param \SalesIgniter\Rental\Api\InventoryGridRepositoryInterface                    $inventoryGridRepository
     * @param \Magento\Catalog\Model\ProductRepository                                     $productRepository
     * @param \Magento\Catalog\Model\Product\Action                                        $attributeAction
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                 $searchCriteriaBuilder
     * @param \Magento\Framework\DB\Transaction                                            $transaction
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory                         $searchResultsFactory
     *
     * @internal param \SalesIgniter\Rental\Helper\Date $dateHelper
     */
    public function __construct(
        \SalesIgniter\Rental\Model\ReservationOrdersFactory $objectFactory,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory,
        \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders $reservationOrderResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        SerialNumberDetailsRepository $serialNumberDetailsRepository,
        Stock $stock,
        SortOrderBuilder $sortOrderBuilder,
        InventoryGridRepositoryInterface $inventoryGridRepository,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Action $attributeAction,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->reservationOrderResource = $reservationOrderResource;
        $this->stock = $stock;
        $this->calendarHelper = $calendarHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
        $this->productRepository = $productRepository;
        $this->attributeAction = $attributeAction;
        $this->inventoryGridRepository = $inventoryGridRepository;
        $this->datetime = $datetime;
        $this->transaction = $transaction;
    }

    public function getById($idRes)
    {
        $object = $this->objectFactory->create();
        $object->load($idRes);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $idRes));
        }

        return $object;
    }

    public function getByOrderItemId($orderItemId)
    {
        $resOrderArray = $this->reservationOrderResource->loadByOrderItemId($orderItemId);
        if (count($resOrderArray) > 0) {
            return $this->getById($resOrderArray[0]['reservationorder_id']);
        }

        return null;
    }

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
}
