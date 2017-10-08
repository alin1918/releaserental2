<?php

namespace SalesIgniter\Rental\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Sirent Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 */
class Sirent extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_RENTAL = 'sirent';

    protected $_customOption;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Option                    $catalogProductOption
     * @param \Magento\Eav\Model\Config                                $eavConfig
     * @param \Magento\Catalog\Model\Product\Type                      $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface                $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database       $fileStorageDb
     * @param \Magento\Framework\Filesystem                            $filesystem
     * @param \Magento\Framework\Registry                              $coreRegistry
     * @param \Psr\Log\LoggerInterface                                 $logger
     * @param ProductRepositoryInterface                               $productRepository
     * @param \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOption
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface     $stockRegistry
     * @param \SalesIgniter\Rental\Helper\Data                         $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                     $helperCalendar
     * @param \Magento\CatalogInventory\Api\StockStateInterface        $stockState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOption,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    )
    {
        $this->_customOption = $customOption;
        $this->_stockState = $stockState;
        $this->_stockRegistry = $stockRegistry;

        parent::__construct($catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
        $this->helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
    }

    /**
     * Check whether the product is available for sale
     * is alias to isSalable for compatibility
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSalable($product)
    {
        return $this->isSalable($product);
    }

    /**
     * Whether product available in stock
     *
     * @return bool
     */
    public function isInStock()
    {
        return true;
    }

    public function canConfigure($product)
    {
        return true;
    }

    public function isSalable($product)
    {
        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        /**
         * abstract method
         */
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isVirtual($product)
    {
        //check disable shipping property. on reserve order needs to make it non virtual
        //for configurable and bundle needs plugins
        $disableShipping = $this->helperCalendar->getDisabledShipping($product);
        if ($disableShipping) {
            return true;
        }
        return parent::isVirtual($product);
    }
}
