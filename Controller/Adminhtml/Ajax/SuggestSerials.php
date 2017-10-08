<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use SalesIgniter\Rental\Model\SerialNumberDetailsRepository;

/**
 * Class SuggestSerials
 * @SuppressWarnings(PHPMD.LongVariableNames)
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Ajax
 */
class SuggestSerials extends \Magento\Backend\App\Action
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
     * @var \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetailsCollectionFactory
     */
    private $serialDetailsCollectionFactory;
    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $resourceHelper;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
     */
    private $serialNumberDetailsRepository;

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param \Magento\Backend\App\Action\Context                                            $context
     * @param \Magento\Framework\DB\Helper                                                   $resourceHelper
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository                       $serialNumberDetailsRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                   $searchCriteriaBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory                               $resultJsonFactory
     *
     * @internal param \Magento\Framework\Api\SearchCriteriaInterfaceFactory $searchCriteriaInterfaceFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory,
        SerialNumberDetailsRepository $serialNumberDetailsRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serialDetailsCollectionFactory = $serialDetailsCollectionFactory;
        $this->resourceHelper = $resourceHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
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
            $this->getSuggestedSerials($this->getRequest()->getParam('product_id'), $this->getRequest()->getParam('search'))
        );
        return $resultJson;
    }

    /**
     * Suggest serials for the send rentals page
     *
     * @param int    $productId
     * @param string $serial this is a partial serial that the user types into the input field
     *
     * @return array
     */
    private function getSuggestedSerialsNoApi($productId, $serial)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($serial, ['position' => 'any']);
        /** @var dd $collection */
        $collection = $this->serialDetailsCollectionFactory->create();
        $collection->addFieldToFilter(
            'product_id',
            ['eq' => $productId]
        )->addFieldToFilter(
            'status',
            ['eq' => 'available']
        )->addFieldToFilter(
            'serialnumber',
            ['like' => $labelPart]
        )->addFieldToSelect(
            'serialnumber_details_id',
            'id'
        )->addFieldToSelect(
            'serialnumber',
            'label'
        )->setOrder(
            'serialnumber_details_id',
            'ASC'
        );
        return $collection->getData();
    }

    /**
     * @param $productId
     * @param $serial
     *
     * @return array
     */
    private function getSuggestedSerials($productId, $serial)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($serial, ['position' => 'any']);

        $this->searchCriteriaBuilder->addFilter('product_id', $productId);
        $this->searchCriteriaBuilder->addFilter('status', 'available');
        $this->searchCriteriaBuilder->addFilter('serialnumber', $labelPart, 'like');

        $criteria = $this->searchCriteriaBuilder->create();
        $returnData = [];
        $items = $this->serialNumberDetailsRepository->getList($criteria)->getItems();
        foreach ($items as $item) {
            $returnData[] = [
                'value' => $item->getSerialnumber(),
                'text' => $item->getSerialnumber(),
            ];
        }
        return $returnData;
    }
}
