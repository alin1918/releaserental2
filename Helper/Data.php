<?php

namespace SalesIgniter\Rental\Helper;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\BundlePriceType;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * General Helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const USE_CONFIG_DEFAULT = -200;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceProduct;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface;
     */
    protected $moduleList;

    /**
     * @param \Magento\Framework\App\Helper\Context           $context
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Catalog\Model\Session                  $catalogSession
     * @param \Magento\Catalog\Model\ResourceModel\Product    $resourceProduct
     * @param \Magento\Framework\Registry                     $coreRegistry
     * @param \Magento\Framework\App\State                    $appState
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\RequestInterface         $request
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface     $orderRepository
     * @param \Magento\Customer\Model\Session                 $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\State $appState,
        ProductRepositoryInterface $productRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\Session $customerSession,
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_resourceProduct = $resourceProduct;
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        $this->_productRepository = $productRepository;
        /****
         * \PhpConsole\Connector::setPostponeStorage(new \PhpConsole\Storage\File('/tmp/pc.data'));
         * $handlerConsole = \PhpConsole\Handler::getInstance();
         * $handlerConsole->start();
         * \PhpConsole\Helper::register();
         * /*****/
        $this->orderRepository = $orderRepository;
        $this->request = $context->getRequest();
        $this->orderItemRepository = $orderItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    public function getExtensionVersion()
    {
        $moduleCode = 'SalesIgniter_Rental';
        $moduleInfo = $this->moduleList->getOne($moduleCode);

        return $moduleInfo['setup_version'];
    }

    /**
     * Function to return id from product object.
     * This function is not good and should be removed. We must know the parameters type when sent.
     *
     * @param $product
     *
     * @return mixed
     */
    public function getProductIdFromObject($product)
    {
        if (is_numeric($product)) {
            return $product;
        }
        if (null !== $product->getProductId()) {
            return $product->getProductId();
        }
        /*if (!is_null($product->getEntityId())) {
            return $product->getEntityId();
        }*/
        return $product->getId();
    }

    /**
     * This function return the product object from product ID.
     *
     * @param      $product
     * @param null $storeId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductObjectFromId($product, $storeId = null)
    {
        if (is_object($product)) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        return $this->_productRepository->getById($productId, false, $storeId);
    }

    /**
     * Returns true if controller is a response.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isPaymentResponse()
    {
        return strpos($this->request->getFullActionName(), '_response') !== false;
    }

    /**
     * Function returns if any product type has calendar enabled.
     *
     * @param \Magento\Catalog\Model\Product|int $product
     * @param int|string|bool|array|null         $rentalType
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isRentalType($product, $rentalType = null)
    {
        if ($rentalType === null) {
            if (is_object($product) && $product->getTypeId() === Sirent::TYPE_RENTAL) {
                return true;
            }
            $typeId = $this->getAttributeRawValue($product, 'type_id');

            if ($typeId === Sirent::TYPE_RENTAL) {
                return true;
            }
            $rentalType = $this->getAttributeRawValue($product, 'sirent_rental_type');
        }
        $rentalType = (int) $rentalType;

        return !($rentalType === 0 ||
            $rentalType === \SalesIgniter\Rental\Model\Attribute\Sources\RentalType::STATUS_DISABLED ||
            $rentalType === \SalesIgniter\Rental\Model\Attribute\Sources\RentalType::STATUS_NOTSET);
    }

    /**
     * Function returns if any product type has calendar enabled.
     *
     * @param \Magento\Catalog\Model\Product|int $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isPricePerProduct($product)
    {
        $priceType = $this->getAttributeRawValue($product, 'sirent_bundle_price_type');

        return (int) $priceType === BundlePriceType::PRICING_BUNDLE_PERPRODUCT;
    }

    /**
     * Function returns if product is sirent type.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isRentalTypeSimple($product)
    {
        $typeId = $this->getAttributeRawValue($product, 'type_id');

        return $typeId === \SalesIgniter\Rental\Model\Product\Type\Sirent::TYPE_RENTAL;
    }

    /**
     * Function returns if product is Bundle.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isBundle($product)
    {
        $typeId = $this->getAttributeRawValue($product, 'type_id');

        return $typeId === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
    }

    /**
     * Function returns if product is Bundle.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isConfigurable($product)
    {
        $typeId = $this->getAttributeRawValue($product, 'type_id');

        return $typeId === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    /**
     * Returns true if an order has a product of type rental in it.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function orderContainsRentals($order)
    {
        foreach ($order->getAllItems() as $item) {
            if ($this->isRentalTypeSimple($item)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns true if a quote (shopping cart) has product of type rental in it.
     *
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function quoteContainsRentals($quote)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($this->isRentalTypeSimple($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns attribute value directly from database.
     * Is faster and better because product might not always have all the attributes loaded
     * Also is not needed to have the product object loaded.
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @param string                             $attributeName
     * @param null | int                         $store
     *
     * @return array|bool|string
     */
    public function getAttributeRawValue($product, $attributeName, $store = null)
    {
        $productId = $this->getProductIdFromObject($product);
        if ($store === null) {
            $store = $this->_storeManager->getStore();
        }
        $result = $this->_resourceProduct->getAttributeRawValue(
            $productId,
            $attributeName,
            $store
        );

        if (is_array($result) && isset($result[$attributeName])) {
            if (is_array($result[$attributeName]) && count($result[$attributeName]) === 0) {
                return '';
            }
            $result = $result[$attributeName];
        }
        if (is_array($result) && count($result) === 0) {
            return '';
        }

        return $result;
        //$product = $this->getProductObjectFromId($product);
        //return $product->getData($attributeName);
    }

    /**
     * Returns true if current scope is frontend.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFrontend()
    {
        return $this->_appState->getAreaCode() === 'frontend';
    }

    /**
     * Returns true if current scope is frontend.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFrontendConfigureCart()
    {
        return $this->_appState->getAreaCode() === 'frontend' &&
            $this->request->getFullActionName() === 'checkout_cart_configure';
    }

    /**
     * Returns true if current scope is backend.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackend()
    {
        return $this->_appState->getAreaCode() === 'adminhtml';
    }

    /**
     * Returns true if current scope is backend.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackendAdminOrderEdit()
    {
        return $this->_appState->getAreaCode() === 'adminhtml' &&
            (strpos($this->request->getFullActionName(), 'sales_order_create') !== false || strpos($this->request->getFullActionName(), 'sales_order_edit') !== false) &&
            strpos($this->request->getFullActionName(), 'reorder') === false;
    }

    /**
     * Function returns Customer Group Id depening on the frontend or backend.
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_customerSession->getCustomerGroupId();
    }

    /**
     * Function return current store id in frontend or backend.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Function return current store id in frontend or backend.
     * Because inventory should not be different between websites this should return all stores
     * An inventory per website or store is wrong and should only be supported by warehouses
     * Magento does not support either inventory per store.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreIdsForCurrentWebsite()
    {
        $storeIds = [0];
        foreach ($this->_storeManager->getStores() as $store) {
            //if ($store->getWebsiteId() === $this->_storeManager->getWebsite()->getId()) {
            $storeIds[] = $store->getId();
            //}
        }

        return $storeIds;
    }

    /**
     * @param $product
     * @param $attribute
     *
     * @return array|bool|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttribute($product, $attribute)
    {
        return $this->getAttributeRawValue($product, $attribute);
    }

    /**
     * Checks if product is buyout type
     * todo should check if buyout price is higher than zero.
     *
     * @param $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isBuyout($product)
    {
        $buyoutEnabled = $this->getAttribute($product, 'sirent_enable_buyout');
        return (bool) $buyoutEnabled;
    }

    /**
     * Checks if we are in backend order view.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackendOrderView()
    {
        return $this->_appState->getAreaCode() === 'adminhtml' &&
            $this->request->getFullActionName() === 'sales_order_view';
    }

    /**
     * Checks if we are in backend order view.
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isBackendShipment()
    {
        return $this->_appState->getAreaCode() === 'adminhtml' &&
            $this->request->getFullActionName() === 'sales_order_shipment';
    }

    /**
     * Checks if product uses serials.
     *
     * @param $product
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSerialEnabledForProduct($product)
    {
        return (bool) $this->getAttribute($product, 'sirent_serial_numbers_use');
    }

    /**
     * @param $orderItem
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSerialEnabledForOrderItem($orderItem)
    {
        $orderItemId = $orderItem;
        if (is_object($orderItem)) {
            $orderItemId = $orderItem->getId();
        }

        $orderItem = $this->orderItemRepository->get($orderItemId);

        return $this->isSerialEnabledForProduct($orderItem->getProductId());
    }

    /**
     * @param $orderItem
     *
     * @return int
     */
    public function getProductIdFromOrderItem($orderItem)
    {
        $orderItemId = $orderItem;
        if (is_object($orderItem)) {
            $orderItemId = $orderItem->getId();
        }

        $orderItem = $this->orderItemRepository->get($orderItemId);

        return $orderItem->getProductId();
    }

    /**
     * @param $orderItem
     *
     * @return int
     */
    public function getProductIdsFromOrderItem($orderItem)
    {
        $orderItemId = $orderItem;
        if (is_object($orderItem)) {
            $orderItemId = $orderItem->getId();
        }

        $orderItem = $this->orderItemRepository->get($orderItemId);
        $this->searchCriteriaBuilder->addFilter('parent_item_id', $orderItemId);

        $criteria = $this->searchCriteriaBuilder->create();
        /** @var array $returnData */
        $returnData = [];

        $returnData[] = [
            'product_id' => $orderItem->getProductId(),
            'qty' => $orderItem->getQtyOrdered() - $orderItem->getQtyShipped(), //todo check into this: qtyrefunded/qty_canceled
            'order_item_id' => $orderItem->getItemId(),
        ];
        /** @var array $items */
        $items = $this->orderItemRepository->getList($criteria)->getItems();
        foreach ($items as $item) {
            $returnData[] = [
                'product_id' => $item->getProductId(),
                'qty' => $item->getQtyToShip(), //todo check into this: qtyrefunded/qty_canceled
                'order_item_id' => $item->getItemId(),
            ];
        }

        return $returnData;
    }

    public function isFrontendAndBackendEdit()
    {
        return ($this->_appState->getAreaCode() === 'frontend') || ($this->_appState->getAreaCode() === 'webapi_rest') || ($this->_appState->getAreaCode() === 'adminhtml' &&
                strpos($this->request->getFullActionName(), 'sales_order_create') !== false) || ($this->_appState->getAreaCode() === 'adminhtml' &&
                strpos($this->request->getFullActionName(), 'salesigniter_rental_ajax_price') !== false);
    }

    public function versionIs22AndOver()
    {
        $productMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        if (version_compare($version, '2.1.9', '>')) {
            return true;
        }

        return false;
    }

    /**
     * Because in version 2.2 al the serializer and json decodes have been replace by one function.
     *
     * @param $data
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function serialize($data, $forceDecode = false)
    {
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);

            return $serializer->serialize($data);
        } else {
            if ($forceDecode || $this->versionIs22AndOver()) {
                return json_encode($data);
            } else {
                return serialize($data);
            }
        }
    }

    /**
     * Because in version 2.2 al the serializer and json decodes have been replace by one function.
     *
     * @param $data
     *
     * @throws \InvalidArgumentException
     */
    public function unserialize($data)
    {
        if (class_exists('Magento\Framework\Serialize\Serializer\Json')) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
            try {
                $ret = $serializer->unserialize($data);
            } catch (\Exception $e) {
                try {
                    $ret = unserialize($data);
                } catch (\Exception $ex) {
                    $ret = [];
                }
            }
        } else {
            try {
                $ret = unserialize($data);
            } catch (\Exception $e) {
                try {
                    $ret = json_decode($data);
                } catch (\Exception $ex) {
                    $ret = [];
                }
            }
        }

        return $ret;
    }

    /**
     * @param $price
     * @param $sValue
     *
     * @return float
     */
    public function getAmountFromStringValue($price, $sValue)
    {
        if (substr($sValue, -1) === '%') {
            $price = (float) $price * (float) substr($sValue, 0, -1) / 100;
        } else {
            $price = (float) $sValue;
        }

        return $price;
    }
}
