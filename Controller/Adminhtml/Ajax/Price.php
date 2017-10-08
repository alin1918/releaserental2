<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog data.
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
     * Registry.
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
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;

    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    protected $stockManagement;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     * @param \Magento\Catalog\Model\ProductFactory                $productModelFactory
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Catalog\Helper\Data                         $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar                 $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Product                  $productHelper
     * @param \SalesIgniter\Rental\Api\StockManagementInterface    $stockManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Product $productHelper,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->priceCalculations = $priceCalculations;
        $this->stockManagement = $stockManagement;
        parent::__construct($context);
    }

    /**
     * Update price data for product.
     *
     * @return \Magento\Framework\Controller\Result\Json
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $qty = 1;
        if (isset($params['qty'])) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            );
            $qty = $filter->filter($params['qty']);
        }
        if ($qty === '') {
            $qty = 1;
        }
        $product = $this->productHelper->initProduct(
            (int) $this->getRequest()->getParam('sirent_product_id'),
            $params
        );
        if (array_key_exists('super_attribute', $params) && null !== $params) {
            $productObj = $product->getTypeInstance()->getProductByAttributes($params['super_attribute'], $product);
            if (is_object($productObj)) {
                $product = $productObj;
                $this->registry->register('current_product', $product);
            }
        }
        if (!$product) {
            $responseContent = [
                'success' => false,
                'error_message' => __('Product does not exists'),
            ];
        } else {

            //tests should take into account various formats/localizations
            //browser timezone. so need to change to a timezone very different of mine

            $hasTimes = $this->calendarHelper->useTimes($product);

            /** @var \DateTime $startDate */
            $startDate = $this->calendarHelper->convertDateToUTC($params['calendar_selector']['from'], $hasTimes, $params['calendar_selector']['locale']);
            /** @var \DateTime $endDate */
            $endDate = $this->calendarHelper->convertDateToUTC($params['calendar_selector']['to'], $hasTimes, $params['calendar_selector']['locale']);
            $this->registry->register('start_date', $startDate);
            $this->registry->register('end_date', $endDate);
            //$store = $this->getCurrentStore();

            //$regularPrice = $product->getPriceInfo()->getPrice('regular_price');
            $finalPrice = $product->getFinalPrice($qty);
            list($finalPrice, $finalPriceSpecial, $finalPriceTax, $finalPriceSpecialTax) = $this->priceCalculations->getAllPricesValues($product, $finalPrice);
            $stockManagement = $this->stockManagement;
            $availableQty = $stockManagement->getAvailableQuantity($product, $startDate, $endDate);
            $sirentQty = $stockManagement->getSirentQuantity($product);
            $responseContent = [
                'success' => true,
                'finalPrice' => [
                    'amount' => $this->_registerJsPrice($finalPrice),
                    'amountSpecial' => $this->_registerJsPrice($finalPrice),
                    'amountTax' => $this->_registerJsPrice($finalPriceTax),
                    'amountSpecialTax' => $this->_registerJsPrice($finalPriceTax),
                ],
                'availableQty' => $availableQty,
                'remainingQty' => $sirentQty - $availableQty,
                'productName' => $product->getName(),
                'error_message' => false,
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);

        return $resultJson;
    }

    /**
     * Replace ',' on '.' for js.
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
