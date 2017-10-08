<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Catalog;

use Magento\Backend\App\Action as BackendAction;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

class Convertproducttype extends BackendAction
{
    protected $productFactory;

    protected $stockItem;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Backend\App\Action\Context|\SalesIgniter\Rental\Controller\Adminhtml\Catalog\Context            $context
     * @param \Magento\Catalog\Model\ProductFactory                                                                    $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                                                          $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface                                                     $stockItem
     * @param \SalesIgniter\Rental\Api\StockManagementInterface                                                        $stockManagement
     * @param \Magento\Framework\View\Result\PageFactory|\SalesIgniter\Rental\Controller\Adminhtml\Catalog\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter                                                                  $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory                                           $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItem,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->stockItem = $stockItem;
        $this->productFactory = $productFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->stockManagement = $stockManagement;
        $this->productRepository = $productRepository;
    }

    /**
     * Converts product types from simple to reservation or reservation to simple
     * from the catalog product massaction grid
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute()
    {
        $convertType = $this->getRequest()->getParam('convertoption');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productArray = $collection->getAllIds();

        /** Convert simple to reservation */
        if ($convertType == 'simpletoreservation') {
            foreach ($productArray as $productId) {
                $product = $this->productRepository->getById($productId);
                $productType = $product->getTypeId();
                $type = 'simple';
                if ($productType !== $type) {
                    $errorMsg = __('All products must be simple product type');
                    $this->messageManager->addError($errorMsg);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('catalog/product/index');
                    return $resultRedirect;
                }
                if (!$product->getSize()) {
                    $product->setSize(1);
                }
                $product->setTypeId(Sirent::TYPE_RENTAL);
                $this->productRepository->save($product);
                /** Set rental inventory to what the simple product inventory was */
                $inventory = $this->stockItem->getStockItem($productId);
                $this->stockManagement->updateSirentQuantity($productId, $inventory->getQty());
            }
            $Msg = __('Products have been converted from simple to reservation');
            $this->messageManager->addSuccess($Msg);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('catalog/product/index');
            return $resultRedirect;
        }

        /** Convert reservation to simple */
        if ($convertType == 'reservationtosimple') {
            foreach ($productArray as $productId) {
                $product = $this->productFactory->create()->load($productId);
                $productType = $product->getTypeId();
                $type = Sirent::TYPE_RENTAL;
                if ($productType !== $type) {
                    $errorMsg = __('All products must be reservation product type');
                    $this->messageManager->addError($errorMsg);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('catalog/product/index');
                    return $resultRedirect;
                }

                $product->setTypeId('simple');

                /** Set simple product inventory to same as the reservation product inventory */
                $rentalInventory = $product->getSirentQuantity();
                $product->setStockData(['qty' => $rentalInventory, 'is_in_stock' => $rentalInventory > 0 ? 1 : 0]);
                $product->setQuantityAndStockStatus(['qty' => $rentalInventory, 'is_in_stock' => $rentalInventory > 0 ? 1 : 0]);
                $this->productRepository->save($product);
            }
            $Msg = __('Products have been converted from reservation to simple');
            $this->messageManager->addSuccess($Msg);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('catalog/product/index');
            return $resultRedirect;
        }
    }
}
