<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Calendar;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\UrlInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeGlobalDates extends \Magento\Framework\App\Action\Action
{

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \SalesIgniter\Rental\Helper\Product
     */
    private $productHelper;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    private $catalogCategory;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Catalog\Model\ProductFactory            $productModelFactory
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Catalog\Helper\Data                     $catalogData
     * @param \Magento\Framework\UrlInterface                  $urlBuilder
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Helper\Category                 $catalogCategory
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar             $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Product              $productHelper
     * @param \Magento\Catalog\Model\Session                   $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Product $productHelper,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        parent::__construct($context);
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->catalogSession = $catalogSession;
        $this->urlBuilder = $context->getUrl();
        $this->categoryRepository = $categoryRepository;
        $this->catalogCategory = $catalogCategory;
    }

    /**
     * Get URL for ajax price call
     *
     * @param $categoryId
     *
     * @return string
     */
    public function getCategoryUrl($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        /* @var $category \Magento\Catalog\Model\Category */
        if (!$this->catalogCategory->canShow($category)) {
            return $this->_redirect->getRefererUrl();
        }
        return $this->catalogCategory->getCategoryUrl($category);
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $dates = $this->calendarHelper->getDatesFromBuyRequest(
            $params, null, /*$params['calendar_use_times']*/
            true
        );
        if ($dates->getStartDate()) {
            $this->catalogSession->setStartDateGlobal($dates->getStartDate()->format('Y-m-d H:i:s'));
            $this->catalogSession->setEndDateGlobal($dates->getEndDate()->format('Y-m-d H:i:s'));
            $this->messageManager->addSuccessMessage(__('Dates were set'));
        } else {
            /*$responseContent = [
                'success' => false,
                'message' => __('Dates were not set')
            ];*/
            $this->messageManager->addErrorMessage(__('Dates were not set'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        // go to grid
        if (array_key_exists('category_to_go', $params)) {
            return $resultRedirect->setPath($this->getCategoryUrl($params['category_to_go']));
        } else {
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     *
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }
}
