<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AvailableQty extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
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
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $attributeAction;
    /**
     * @var \SalesIgniter\Rental\Model\Stock
     */
    private $stock;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context             $context
     * @param \Magento\Catalog\Model\ProductFactory             $productModelFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface   $productRepository
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Catalog\Helper\Data                      $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar              $calendarHelper
     * @param \Magento\Catalog\Model\Product\Action             $attributeAction
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param \SalesIgniter\Rental\Model\Product\Stock          $stock
     * @param \Magento\Backend\Model\Session\Quote              $quoteSession
     * @param \Magento\Framework\Locale\ResolverInterface       $localeResolver
     * @param \Magento\Catalog\Model\Session                    $catalogSession
     * @param \SalesIgniter\Rental\Helper\Product               $productHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \Magento\Catalog\Model\Product\Action $attributeAction,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \SalesIgniter\Rental\Model\Product\Stock $stock,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Catalog\Model\Session $catalogSession,
        \SalesIgniter\Rental\Helper\Product $productHelper
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        parent::__construct($context);
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->attributeAction = $attributeAction;
        $this->stock = $stock;
        $this->quoteSession = $quoteSession;
        $this->catalogSession = $catalogSession;
        $this->stockManagement = $stockManagement;
        $this->localeResolver = $localeResolver;
        $this->productRepository = $productRepository;
    }

    /**
     * Get the total quantity and available quantity for a product
     * This is used on the manual reservation edit page and the Maintenance module edit ticket page
     *
     *  $params['skiputc'] if set to 1, skips utc time format conversion and assumes dates are already in mysql time format
     *  $params['start_date']  new start date
     *  $params['end_date']  new end date
     *  $params['product_id'] new product id
     *  $params['product_id_orig'] original product id from reservationorders table
     *  $params['start_date_orig'] original start date
     *  $params['end_date_orig'] original end date
     *  $params['quantity_already_reserved'] quantity already reserved
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.LongVariableNames)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $qty = 1;
        ///Here I pass an order_id or a reservationorder_id and based on that I select the product_id and start end dates and qty. Not sure about parent_id if I should take that too into account
        /// if is order_id I get all the reservations with that order and that product_id and exclude the qty for the dates
        if (array_key_exists('qty', $params)) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            );
            $qty = $filter->filter($params['qty']);
        }
        $product = null;
        $productId = false;
        $productIdOrig = false;
        if (array_key_exists('product_id_orig', $params) && $params['product_id_orig'] != '') {
            $productIdOrig = $params['product_id_orig'];
        }
        if (array_key_exists('product_id', $params) && $params['product_id'] > 0) {
            $product = $this->productHelper->initProduct(
                (int)$params['product_id'],
                $params
            );
            $productId = $product->getId();
        }

        $startDate = null;
        $endDate = null;

        if (array_key_exists('start_date', $params) &&
            $params['start_date'] !== '' &&
            $params['end_date'] !== ''
        ) {
            $hasTimes = false;
            if ($product !== null) {
                $hasTimes = $product->getSirentUseTimes() > 0;
            }
            if (!isset($params['skiputc'])) {
                /** @var \DateTime $startDate */
                $startDate = $this->calendarHelper->convertDateToUTC($params['start_date'], $hasTimes, $this->localeResolver->getLocale());
                /** @var \DateTime $endDate */
                $endDate = $this->calendarHelper->convertDateToUTC($params['end_date'], $hasTimes, $this->localeResolver->getLocale());
            } else {
                $startDate = new \DateTime($params['start_date']);
                $endDate = new \DateTime($params['end_date']);
            }
        }

        $fromDateInitial = '';
        $toDateInitial = '';
        if ($startDate !== null && $endDate !== null) {
            if ($hasTimes) {
                $fromDateInitial = $startDate->format('Y-m-d H:i:s');
                $toDateInitial = $endDate->format('Y-m-d H:i:s');
            } else {
                $fromDateInitial = $startDate->format('Y-m-d') . ' 00:00:00';
                $toDateInitial = $endDate->format('Y-m-d') . ' 00:00:00';
            }
        }
        $availableQuantity = 0;
        $totalQuantity = 0;
        if (null !== $product) {
            $totalQuantity = $this->stockManagement->getSirentQuantity($product);
            $excludingReservations = [];
            if (isset($params['reservation_id'])) {
                $excludingReservations[] = $params['reservation_id'];
            }
            $availableQuantity = $this->stockManagement->getAvailableQuantity($product, $fromDateInitial, $toDateInitial, $excludingReservations);
        }

        $overbookText = '';
        if ($qty > $availableQuantity) {
            $overbookText = '<span style="color:red"> ' . __('(quantity will be overbooked)') . '</span>';
        }
        $availableQuantityMessage = __('Available Qty for the dates: ') . $availableQuantity . $overbookText . __(' Total Qty for the Product: ') . $totalQuantity;
        if (null === $product) {
            $availableQuantityMessage = __('Please select a product');
        }
        $newData = false;
        if ($productIdOrig !== false) {
            $product = $this->productRepository->getById($productIdOrig);
            $productId = $product->getId();
            $newData[] = [
                'id' => $product->getId(),
                'text' => $product->getName(),
                'sku' => $product->getSku(),
                'images' => $product->getMediaGalleryEntries(),
            ];
        }
        $responseContent = [
            'success' => true,
            'newData' => $newData,
            'productId' => $productId,
            'availableQuantity' => $availableQuantityMessage,
            'error_message' => __(''),
        ];
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
