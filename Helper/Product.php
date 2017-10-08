<?php

namespace SalesIgniter\Rental\Helper;

/**
 * Calendar Helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Catalog data.
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory
     */
    protected $_bundleCollection;

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $_bundleOption;

    /**
     * Cache key for Options Collection.
     *
     * @var string
     */
    protected $_keyOptionsCollection = '_cache_instance_options_collection';

    /**
     * Cache key for Selections Collection.
     *
     * @var string
     */
    protected $_keySelectionsCollection = '_cache_instance_selections_collection';

    /**
     * Cache key for used Selections.
     *
     * @var string
     */
    protected $_keyUsedSelections = '_cache_instance_used_selections';

    /**
     * Cache key for used selections ids.
     *
     * @var string
     */
    protected $_keyUsedSelectionsIds = '_cache_instance_used_selections_ids';

    /**
     * Cache key for used options.
     *
     * @var string
     */
    protected $_keyUsedOptions = '_cache_instance_used_options';

    /**
     * Cache key for used options ids.
     *
     * @var string
     */
    protected $_keyUsedOptionsIds = '_cache_instance_used_options_ids';
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

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
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $rentalHelper;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                           $context           ,                          $context
     * @param \Magento\Catalog\Model\ProductRepository                        $productRepository
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Catalog\Helper\Data                                    $catalogData
     * @param \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection
     * @param \Magento\Bundle\Model\OptionFactory                             $bundleOption
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar                            $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Data                                $rentalHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Data $rentalHelper
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->_bundleOption = $bundleOption;
        $this->_bundleCollection = $bundleCollection;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->calendarHelper = $calendarHelper;
        parent::__construct($context);
        $this->rentalHelper = $rentalHelper;
    }

    /**
     * @param array $array
     *
     * @return int[]|int[][]
     */
    private function recursiveIntval(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursiveIntval($value);
            } elseif (is_numeric($value) && (int) $value != 0) {
                $array[$key] = (int) $value;
            } else {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * @param array $array
     *
     * @return int[]
     */
    private function multiToFlatArray(array $array)
    {
        $flatArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flatArray = array_merge($flatArray, $this->multiToFlatArray($value));
            } else {
                $flatArray[$key] = $value;
            }
        }

        return $flatArray;
    }

    /**
     * Retrieve bundle selections collection based on ids.
     *
     * @param array                          $selectionIds
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getSelectionsByIds($selectionIds, $product)
    {
        sort($selectionIds);

        $usedSelections = $product->getData($this->_keyUsedSelections);
        $usedSelectionsIds = $product->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || $usedSelectionsIds !== $selectionIds) {
            $storeId = $product->getStoreId();
            $usedSelections = $this->_bundleCollection
                ->create()
                ->addAttributeToSelect('*')
                ->setFlag('product_children', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds);

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)
                    ->getWebsiteId();
                $usedSelections->joinPrices($websiteId);
            }
            $product->setData($this->_keyUsedSelections, $usedSelections);
            $product->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }

        return $usedSelections;
    }

    /**
     * Sort selections method for usort function
     * Sort selections by option position, selection position and selection id.
     *
     * @param \Magento\Catalog\Model\Product $firstItem
     * @param \Magento\Catalog\Model\Product $secondItem
     *
     * @return int
     */
    public function shakeSelections($firstItem, $secondItem)
    {
        $aPosition = [
            $firstItem->getOption()
                ->getPosition(),
            $firstItem->getOptionId(),
            $firstItem->getPosition(),
            $firstItem->getSelectionId(),
        ];
        $bPosition = [
            $secondItem->getOption()
                ->getPosition(),
            $secondItem->getOptionId(),
            $secondItem->getPosition(),
            $secondItem->getSelectionId(),
        ];
        if ($aPosition == $bPosition) {
            return 0;
        } else {
            return $aPosition < $bPosition ? -1 : 1;
        }
    }

    /**
     * @param \Magento\Framework\DataObject $selection
     * @param int[]                         $qtys
     * @param int                           $selectionOptionId
     *
     * @return float
     */
    protected function getQty($selection, $qtys, $selectionOptionId)
    {
        if ($selection->getSelectionCanChangeQty() && isset($qtys[$selectionOptionId][$selection->getId()])) {
            $qty = (float) $qtys[$selectionOptionId][$selection->getId()] > 0 ? $qtys[$selectionOptionId][$selection->getId()] : 1;
        } elseif ($selection->getSelectionCanChangeQty() && isset($qtys[$selectionOptionId])) {
            $qty = (float) $qtys[$selectionOptionId] > 0 ? $qtys[$selectionOptionId] : 1;
        } else {
            $qty = (float) $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
        }
        $qty = (float) $qty;

        return $qty;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject  $selection
     *
     * @return float|int
     */
    protected function getBeforeQty($product, $selection)
    {
        $beforeQty = 0;
        $customOption = $product->getCustomOption('product_qty_'.$selection->getId());
        if ($customOption && $customOption->getProduct()->getId() == $selection->getId()) {
            $beforeQty = (float) $customOption->getValue();

            return $beforeQty;
        }

        return $beforeQty;
    }

    /**
     * @param \Magento\Catalog\Model\Product                        $product
     * @param bool                                                  $isStrictProcessMode
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection
     * @param int[]                                                 $options
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkIsAllRequiredOptions($product, $isStrictProcessMode, $optionsCollection, $options)
    {
        if (!$product->getSkipCheckRequiredOption() && $isStrictProcessMode) {
            foreach ($optionsCollection->getItems() as $option) {
                if ($option->getRequired() && !isset($options[$option->getId()])) {
                    return false; //not all required options are set
                }
            }
        }
    }

    /**
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selections
     * @param bool                                                     $skipSaleableCheck
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection    $optionsCollection
     * @param int[]                                                    $options
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkSelectionsIsSale($selections, $skipSaleableCheck, $optionsCollection, $options)
    {
        foreach ($selections->getItems() as $selection) {
            if (!$selection->isSalable() && !$skipSaleableCheck) {
                $_option = $optionsCollection->getItemById($selection->getOptionId());
                $optionId = $_option->getId();
                if (is_array($options[$optionId]) && count($options[$optionId]) > 1) {
                    $moreSelections = true;
                } else {
                    $moreSelections = false;
                }
                $isMultiSelection = $_option->isMultiSelection();
                if ($_option->getRequired() && (!$isMultiSelection || !$moreSelections)
                ) {
                    return false; //not all selected products are available for sale
                }
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product\Option[] $options
     * @param \Magento\Framework\DataObject[]         $selections
     *
     * @return \Magento\Framework\DataObject[]
     */
    protected function mergeSelectionsWithOptions($options, $selections)
    {
        foreach ($options as $option) {
            if ($option->getRequired() && count($option->getSelections()) == 1) {
                $selections = array_merge($selections, $option->getSelections());
            } else {
                $selections = [];
                break;
            }
        }

        return $selections;
    }

    /**
     * Retrieve store filter for associated products.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return int|\Magento\Store\Model\Store
     */
    public function getStoreFilter($product)
    {
        $cacheKey = '_cache_instance_store_filter';

        return $product->getData($cacheKey);
    }

    /**
     * Set store filter for associated products.
     *
     * @param                                $store   int|\Magento\Store\Model\Store
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    public function setStoreFilter($store, $product)
    {
        $cacheKey = '_cache_instance_store_filter';
        $product->setData($cacheKey, $store);

        return $this;
    }

    /**
     * Retrieve bundle option collection.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsCollection($product)
    {
        if (!$product->hasData($this->_keyOptionsCollection)) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $this->_bundleOption->create()
                ->getResourceCollection();
            $optionsCollection->setProductIdFilter($product->getEntityId());
            $this->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection->setPositionOrder();
            $storeId = $this->getStoreFilter($product);
            if ($storeId instanceof \Magento\Store\Model\Store) {
                $storeId = $storeId->getId();
            }

            $optionsCollection->joinValues($storeId);
            $product->setData($this->_keyOptionsCollection, $optionsCollection);
        }

        return $product->getData($this->_keyOptionsCollection);
    }

    /**
     * Initialize product instance from request data.
     *
     * @param      $productId
     * @param null $params
     *
     * @return false|\Magento\Catalog\Model\Product
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function initProduct($productId, $params = null)
    {
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            if (null !== $params && isset($params['options'])) {
                $optionIds = array_keys($params['options']);
                $product->addCustomOption('option_ids', implode(',', $optionIds));
                foreach ($params['options'] as $optionId => $optionValue) {
                    if (is_array($optionValue)) {
                        $product->addCustomOption('option_'.$optionId, implode(',', $optionValue));
                    } else {
                        $product->addCustomOption('option_'.$optionId, $optionValue);
                    }
                }
            }
            if (null !== $params && isset($params['bundle_option'])) {
                $options = $params['bundle_option'];
                $selections = [];
                $isStrictProcessMode = false;
                $skipSaleableCheck = true;
                $appendAllSelections = true;
                if (is_array($options)) {
                    $options = $this->recursiveIntval($options);
                    $optionIds = array_keys($options);

                    if (empty($optionIds) && $isStrictProcessMode) {
                        return false;
                    }

                    $product->getTypeInstance()
                        ->setStoreFilter($product->getStoreId(), $product);
                    $optionsCollection = $this->getOptionsCollection($product);

                    $checkAllRequired = $this->checkIsAllRequiredOptions(
                        $product,
                        $isStrictProcessMode,
                        $optionsCollection,
                        $options
                    );

                    if ($checkAllRequired) {
                        return false; //this check should be in js
                    }
                    $selectionIds = $this->multiToFlatArray($options);
                    // If product has not been configured yet then $selections array should be empty
                    if (!empty($selectionIds)) {
                        $selections = $this->getSelectionsByIds($selectionIds, $product);

                        if (count($selections->getItems()) !== count($selectionIds)) {
                            return false;
                        }

                        // Check if added selections are still on sale
                        $isSale = $this->checkSelectionsIsSale(
                            $selections,
                            $skipSaleableCheck,
                            $optionsCollection,
                            $options
                        );
                        if ($isSale) {
                            return false;
                        }
                        $optionsCollection->appendSelections($selections, false, $appendAllSelections);
                        $selections = $selections->getItems();
                    } else {
                        $selections = [];
                    }
                } else {
                    $product->setOptionsValidationFail(true);
                    $product->getTypeInstance()
                        ->setStoreFilter($product->getStoreId(), $product);

                    $optionCollection = $product->getTypeInstance()
                        ->getOptionsCollection($product);
                    $optionIds = $product->getTypeInstance()
                        ->getOptionsIds($product);
                    $selectionCollection = $product->getTypeInstance()
                        ->getSelectionsCollection($optionIds, $product);
                    $options = $optionCollection->appendSelections($selectionCollection, false, $appendAllSelections);

                    $selections = $this->mergeSelectionsWithOptions($options, $selections);
                }
                if (!$isStrictProcessMode || count($selections) > 0) {
                    $selectionIds = [];
                    $qtys = [];
                    if (isset($params['bundle_option_qty'])) {
                        $qtys = $params['bundle_option_qty'];
                    }
                    // Shuffle selection array by option position
                    usort($selections, [$this, 'shakeSelections']);

                    foreach ($selections as $selection) {
                        $selectionOptionId = $selection->getOptionId();
                        $qty = $this->getQty($selection, $qtys, $selectionOptionId);

                        $selectionId = $selection->getSelectionId();
                        $product->addCustomOption('selection_qty_'.$selectionId, $qty, $selection);
                        $beforeQty = $this->getBeforeQty($product, $selection);
                        $product->addCustomOption('product_qty_'.$selection->getId(), $qty + $beforeQty, $selection);
                        $selectionIds[] = $selectionId;
                    }

                    $product->addCustomOption('bundle_option_ids', $this->rentalHelper->serialize(array_map('intval', $optionIds)));
                    $product->addCustomOption('bundle_selection_ids', $this->rentalHelper->serialize($selectionIds));
                }
            }

            return $product;
        }

        return false;
    }

    /**
     * Retrieve array of bundle selection IDs.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getBundleSelectionIds(\Magento\Catalog\Model\Product $product)
    {
        $customOption = $product->getCustomOption('bundle_selection_ids');
        if ($customOption) {
            $selectionIds = $this->rentalHelper->unserialize($customOption->getValue());
            if (!empty($selectionIds) && is_array($selectionIds)) {
                return $selectionIds;
            }
        }

        return [];
    }

    public function getProductsSelectionsArray($product)
    {
        $productsArray = [];
        $product = $this->rentalHelper->getProductObjectFromId($product);
        $selectionIds = $this->getBundleSelectionIds($product);

        /* @var $selections */
        if (count($selectionIds) > 0) {
            $selections = $product->getTypeInstance()->getSelectionsByIds($selectionIds, $product);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_'.$selection->getSelectionId());
                    if ($selectionQty) {
                        $selectionArray = [];

                        $selectionArray['selection_product_quantity'] = $selectionQty->getValue();
                        $selectionArray['selection_product_id'] = $selection->getProductId();
                        $productsArray[$selection->getId()] = $selectionArray;
                    }
                }
            }
        }

        return $productsArray;
    }

    public function getProductsOptionsArray($product, $required = false)
    {
        $productsArray = [];
        $product = $this->rentalHelper->getProductObjectFromId($product);

        $selectionCollection = $product->getTypeInstance()->getChildrenIds($product->getId(), $required);

        /** @var array $selectionCollection */
        foreach ($selectionCollection as $proSelection) {
            $selectionArray = [];
            //$selectionArray['selection_product_name'] = $proSelection->getName();
            //$selectionArray['selection_product_price'] = $proSelection->getPrice();
            $selectionArray['selection_product_quantity'] = $proSelection->getSelectionQty();
            $selectionArray['selection_product_id'] = $proSelection->getProductId();
            $productsArray[$proSelection->getOptionId().';;'.$proSelection->getSelectionId()] = $selectionArray;
        }

        return $productsArray;
    }

    /**
     * Retrieve current store.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }
}
