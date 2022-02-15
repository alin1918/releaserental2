<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Plugin\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * Class AroundProductRepositorySave.
 */
class AroundProductRepositorySave
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var \SalesIgniter\Rental\Helper\Data|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $helperRental;
    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $customOptionValuesInterfaceFactory;
    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $customOptionInterfaceFactory;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param ProductAttributeRepositoryInterface                                 $productAttributeRepository
     * @param ProductFactory                                                      $productFactory
     * @param \SalesIgniter\Rental\Helper\Data                                    $helperRental
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface                $stockRegistry
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory $customOptionValuesInterfaceFactory
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory       $customOptionInterfaceFactory
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductFactory $productFactory,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory $customOptionValuesInterfaceFactory,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionInterfaceFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productFactory = $productFactory;
        $this->helperRental = $helperRental;
        $this->customOptionValuesInterfaceFactory = $customOptionValuesInterfaceFactory;
        $this->customOptionInterfaceFactory = $customOptionInterfaceFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * This function is used I think on import or in some other cases when a product is saved from code.
     * So we are making sure that our options 'Start Date' etc are not lost.
     *
     * @param ProductRepositoryInterface $subject
     * @param \Closure                   $proceed
     * @param ProductInterface           $product
     * @param bool                       $saveOptions
     *
     * @return ProductInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws CouldNotSaveException
     * @throws InputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        ProductRepositoryInterface $subject,
        \Closure $proceed,
        ProductInterface $product,
        $saveOptions = false
    ) {

        //TODO for now it allows bundle to save custom options. I need to take into account this case
        /** @var ProductInterface $result */
        $result = $proceed($product, $saveOptions);

        $hasOptions = false;
        $customOptions = $product->getOptions();

        if (is_array($customOptions)) {
            foreach ($customOptions as $option) {
                if ($option->getTitle() == 'Start Date:' || $option->getTitle() == 'End Date:' || $option->getTitle() == 'Rental Buyout:' || $option->getTitle() == 'Damage Waiver:') {
                    $hasOptions = true;
                    break;
                }
            }
        } else {
            $customOptions = [];
        }

        $options = [];

        if (!$hasOptions && $this->helperRental->isRentalType($product)) {
            $options = [
                [
                    'sort_order' => 1,
                    'is_require' => 0,
                    'price' => '',
                    'price_type' => 'fixed',
                    'sku' => '',
                    'delete' => '',
                    'max_characters' => '',
                    'file_extension' => '',
                    'image_size_x' => '',
                    'image_size_y' => '',
                    'title' => 'Start Date:',
                    'type' => 'date_time',
                ],
                [
                    'sort_order' => 2,
                    'is_require' => 0,
                    'price' => '',
                    'price_type' => 'fixed',
                    'sku' => '',
                    'delete' => '',
                    'max_characters' => '',
                    'file_extension' => '',
                    'image_size_x' => '',
                    'image_size_y' => '',
                    'title' => 'End Date:',
                    'type' => 'date_time',
                ],
                [
                    'sort_order' => 3,
                    'is_require' => 0,
                    'price' => '',
                    'price_type' => 'fixed',
                    'sku' => '',
                    'delete' => '',
                    'max_characters' => '',
                    'file_extension' => '',
                    'image_size_x' => '',
                    'image_size_y' => '',
                    'title' => 'Rental Buyout:',
                    'type' => 'field',
                ],
                [
                    'sort_order' => 4,
                    'is_require' => 0,
                    'price' => '',
                    'price_type' => 'fixed',
                    'sku' => '',
                    'delete' => '',
                    'max_characters' => '',
                    'file_extension' => '',
                    'image_size_x' => '',
                    'image_size_y' => '',
                    'title' => 'Damage Waiver:',
                    'type' => 'field',
                ],
            ];
        }
        if ($hasOptions && $this->helperRental->isRentalType($product)) {
            foreach ($customOptions as $k => $option) {
                if ($option->getTitle() == 'Start Date:' || $option->getTitle() == 'End Date:' || $option->getTitle() == 'Rental Buyout:' || $option->getTitle() == 'Damage Waiver:') {
                    unset($customOptions[$k]);
                }
            }
        }
        //$customOptions = $product->getOptions();
        foreach ($options as $option) {

            /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
            $customOption = $this->customOptionInterfaceFactory->create(['data' => $option]);
            $customOption->setProductSku($product->getSku());
            $customOption->setOptionId(null);
            if (isset($option['values'])) {
                $values = [];
                foreach ($option['values'] as $value) {
                    $value = $this->customOptionValuesInterfaceFactory->create(['data' => $value]);
                    $values[] = $value;
                }
                $customOption->setValues($values);
            }
            $customOptions[] = $customOption;
        }

        if (count($options) > 0) {
            $product->setHasOptions(true)
                ->setCanSaveCustomOptions(true)
                ->setOptions($customOptions);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $stockItem->setManageStock(false);
            $stockItem->setIsInStock(true);
            if ($this->helperRental->isRentalType($product)) {
                $product->setPriceType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
                $product->setPrice(0);
            }
            $subject->save($product);
        } elseif ($this->helperRental->isRentalType($product) && $this->helperRental->isBundle($product)) {
            $product->setPriceType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
            $product->setPrice(0);
            $subject->save($product);
        }

        return $result;
    }

    /**
     * @param array $attributeCodes
     * @param array $linkIds
     *
     * @return $this
     *
     * @throws InputException
     */
    private function validateProductLinks(array $attributeCodes, array $linkIds)
    {
        $valueMap = [];

        foreach ($linkIds as $productId) {
            $variation = $this->productFactory->create()->load($productId);
            $valueKey = '';
            foreach ($attributeCodes as $attributeCode) {
                if (!$variation->getData($attributeCode)) {
                    throw new InputException(
                        __('Product with id "%1" does not contain required attribute "%2".', $productId, $attributeCode)
                    );
                }
                $valueKey = $valueKey.$attributeCode.':'.$variation->getData($attributeCode).';';
            }
            if (isset($valueMap[$valueKey])) {
                throw new InputException(
                    __(
                        'Products "%1" and "%2" have the same set of attribute values.',
                        $productId,
                        $valueMap[$valueKey]
                    )
                );
            }
            $valueMap[$valueKey] = $productId;
        }
    }
}
