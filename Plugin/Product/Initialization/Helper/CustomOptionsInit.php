<?php

namespace SalesIgniter\Rental\Plugin\Product\Initialization\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\RequestInterface;

class CustomOptionsInit
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Plugin\Product\Initialization\Helper\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                                                                                                      $helperRental
     * @param \Magento\Framework\App\RequestInterface                                                                                               $request
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface|\SalesIgniter\Rental\Plugin\Product\Initialization\Helper\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        RequestInterface $request,
        StockRegistryInterface $stockRegistry
    ) {
        $this->_helperRental = $helperRental;
        $this->stockRegistry = $stockRegistry;
        $this->request = $request;
    }

    /**
     * Adds start and end date custom options to product form.
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product                                      $product
     * @param array                                                               $productData
     *
     * @return \Magento\Catalog\Model\Product
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeInitializeFromData(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product,
        array $productData
    ) {
        $hasStartDate = -1;
        $hasEndDate = -1;
        $hasRentalBuyout = -1;
        $hasDamageWaiver = -1;
        if (isset($productData['options'])) {
            foreach ($productData['options'] as $k => $option) {
                if ($option['title'] == 'Start Date:') {
                    $hasStartDate = $k;
                }
                if ($option['title'] == 'End Date:') {
                    $hasEndDate = $k;
                }
                if ($option['title'] == 'Rental Buyout:') {
                    $hasRentalBuyout = $k;
                }
                if ($option['title'] == 'Damage Waiver:') {
                    $hasDamageWaiver = $k;
                }
            }
        }
        if (isset($productData['sirent_rental_type']) && $this->_helperRental->isRentalType($product, $productData['sirent_rental_type'])) {
            if ($hasEndDate === -1) {
                $productData = $this->addOption($productData, 'End Date:', 'date_time');
            }
            if ($hasStartDate === -1) {
                $productData = $this->addOption($productData, 'Start Date:', 'date_time');
            }
            if ($hasRentalBuyout === -1) {
                $productData = $this->addOption($productData, 'Rental Buyout:', 'field');
            }
            if ($hasDamageWaiver === -1) {
                $productData = $this->addOption($productData, 'Damage Waiver:', 'field');
            }
        } else {
            if (isset($productData['options'])) {
                unset($productData['options'][$hasStartDate], $productData['options'][$hasEndDate]);
                $product->setOptions($productData['options']);
            }
        }

        if (isset($productData['sirent_rental_type']) && $this->_helperRental->isRentalType($product, $productData['sirent_rental_type'])) {
            $productData['price_type'] = \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED;
            $productData['price'] = '0';
            $productData['quantity_and_stock_status']['is_in_stock'] = 1;
            $productData['quantity_and_stock_status']['manage_stock'] = 0;
        }

        return [$product, $productData];
    }

    /**
     * Do not remove custom options for bundles with dynamic pricing (we make a backup).
     *
     * To be called before bundle initialization
     *
     * @see \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle::afterInitialize
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product                                      $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $product->setProductOptionsBackup($product->getProductOptions());
        }

        return $product;
    }

    /**
     * @param array $productData
     * @param       $optionTitle
     * @param       $optionType
     *
     * @return array
     */
    protected function addOption(array $productData, $optionTitle, $optionType)
    {
        $sortOrder = 1;
        if (strpos($optionTitle, 'Start') !== false) {
            $sortOrder = 1;
        }
        if (strpos($optionTitle, 'End') !== false) {
            $sortOrder = 2;
        }
        if (strpos($optionTitle, 'Buyout') !== false) {
            $sortOrder = 3;
        }
        if (strpos($optionTitle, 'Waiver') !== false) {
            $sortOrder = 4;
        }
        $productData['options'][] = [
            'sort_order' => $sortOrder,
            'option_id' => '',
            'is_require' => 0,
            'price' => '',
            'price_type' => 'fixed',
            'sku' => '',
            'max_characters' => '',
            'file_extension' => '',
            'image_size_x' => '',
            'image_size_y' => '',
            'title' => $optionTitle,
            'type' => $optionType,
        ];

        return $productData;
    }
}
