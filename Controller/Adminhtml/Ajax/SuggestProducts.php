<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * Class SuggestProducts
 * @SuppressWarnings(PHPMD.LongVariableNames)
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Ajax
 */
class SuggestProducts extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SalesIgniter_Rental::send';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\SuggestedSet
     */
    protected $suggestedSet;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $resourceHelper;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $helperImage;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\DB\Helper                     $resourceHelper
     * @param \Magento\Framework\Api\SearchCriteriaBuilder     $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param \Magento\Framework\Api\FilterBuilder             $filterBuilder
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Catalog\Helper\Image                    $helperImage
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        FilterBuilder $filterBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FilterGroupBuilder $filterGroupBuilder,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceHelper = $resourceHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->helperImage = $helperImage;
        $this->storeManager = $storeManager;
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Action for attribute set selector
     *
     * @return \Magento\Framework\Controller\Result\Json
     */

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            $this->getSuggestedProducts($this->getRequest()->getParam('q'))
        );
        return $resultJson;
    }

    /**
     * @param $productSearch
     *
     * @return array
     *
     */
    private function getSuggestedProducts($productSearch)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($productSearch, ['position' => 'any']);
        $filter[] = $this->filterBuilder
            ->setField('name')
            ->setConditionType('like')
            ->setValue($labelPart)
            ->create();

        $filter[] = $this->filterBuilder
            ->setField('sku')
            ->setConditionType('like')
            ->setValue($labelPart)
            ->create();

        /*$filterType = $this->filterBuilder
            ->setField('type_id')
            ->setConditionType('eq')
            ->setValue(Sirent::TYPE_RENTAL)
            ->create();

        $filterGroupType = $this->filterGroupBuilder
            ->addFilter($filterType)
            ->create();
        */
        /*$this->searchCriteriaBuilder->setFilterGroups([$filterGroup, $filterGroupSku]);*/
        $this->searchCriteriaBuilder
            ->addFilters($filter)
            ->addFilter('type_id', Sirent::TYPE_RENTAL);

        $criteria = $this->searchCriteriaBuilder->create();
        $returnData = [];
        $items = $this->productRepository->getList($criteria)->getItems();

        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($items as $item) {
            //$store = $this->storeManager->getStore();
            //$imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $item->getImage();

            $imageUrl = $this->helperImage->init($item, 'small_image', ['type' => 'small_image'])->keepAspectRatio(true)->resize('35', '35')->getUrl();

            $returnData['items'][] = [
                'id' => $item->getId(),
                'text' => $item->getName(),
                'sku' => $item->getSku(),
                'image' => $imageUrl,
            ];
        }
        return $returnData;
    }
}
