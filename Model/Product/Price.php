<?php

namespace SalesIgniter\Rental\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Price.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    const NO_DATES_PRICE = 0;
    /**
     * Catalog data.
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData;

    /**
     * Registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * Price constructor.
     *
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory       $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface       $localeDate
     * @param \Magento\Customer\Model\Session                            $customerSession
     * @param \Magento\Framework\Event\ManagerInterface                  $eventManager
     * @param PriceCurrencyInterface                                     $priceCurrency
     * @param GroupManagementInterface                                   $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface         $config
     * @param \Magento\Catalog\Helper\Data                               $catalogData
     * @param \Magento\Framework\Registry                                $registry
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations       $priceCalculations
     * @param \SalesIgniter\Rental\Helper\Calendar                       $helperCalendar
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->catalogData = $catalogData;
        $this->registry = $registry;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
        $this->priceCalculations = $priceCalculations;
        $this->helperCalendar = $helperCalendar;
    }

    /**
     * @param float|null                     $qty
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFinalPrice($qty, $product)
    {
        if ($this->registry->registry('start_date') && $this->registry->registry('end_date')) {
            $startDate = $this->registry->registry('start_date');
            $endDate = $this->registry->registry('end_date');
        } elseif ($product->hasCustomOptions()) {
            $buyRequest = $this->helperCalendar->prepareBuyRequest($product);
            $dates = $this->helperCalendar->getDatesFromBuyRequest(
                $buyRequest, $product
            );
            if ($dates->getIsBuyout()) {
                $isBuyout = true;
            } else {
                $startDate = $dates->getStartDate();
                $endDate = $dates->getEndDate();
            }
        } elseif ($this->helperCalendar->getGlobalDates('from')) {
            $startDate = $this->helperCalendar->getGlobalDates('from');
            $endDate = $this->helperCalendar->getGlobalDates('to');
        }

        $finalPrice = isset($startDate) && isset($endDate) ? $this->priceCalculations->calculatePrice($product->getId(), $startDate, $endDate, $qty) : self::NO_DATES_PRICE;
        if (isset($isBuyout)) {
            $finalPrice = $this->priceCalculations->calculateBuyoutPrice($product->getId());
        }
        $product->setFinalPrice($finalPrice);

        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

    /**
     * Apply options price.
     *
     * @param Product $product
     * @param int     $qty
     * @param float   $finalPrice
     *
     * @return float
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            $basePrice = $finalPrice;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_'.$option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                }
            }
        }

        return $finalPrice;
    }
}
