<?php

namespace SalesIgniter\Rental\Plugin\Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Price
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     * @param \Magento\Framework\Registry                          $registry
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
        \Magento\Framework\Registry $registry

    ) {
        $this->helperRental = $helperRental;
        $this->registry = $registry;
        $this->helperCalendar = $helperCalendar;
        $this->priceCalculations = $priceCalculations;
    }

    /**
     * Apply options price
     *
     * @param Product $product
     * @param int     $qty
     * @param float   $finalPrice
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            $basePrice = $finalPrice;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                }
            }
        }

        return $finalPrice;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price $subject
     * @param \Closure                                                           $proceed
     * @param   float                                                            $qty
     * @param   \Magento\Catalog\Model\Product                                   $product
     *
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetFinalPrice(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price $subject,
        \Closure $proceed,
        $qty,
        $product
    ) {
        $returnValue = $proceed($qty, $product);
        return $this->getPrice($product, $qty, $returnValue);
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetPrice(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price $subject,
        \Closure $proceed,
        $product
    ) {
        $returnValue = $proceed($product);
        return $this->getPrice($product, 1, $returnValue);
    }

    /**
     * @param $product
     * @param $qty
     *
     * @param $returnValue
     *
     * @return float
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPrice($product, $qty, $returnValue)
    {
        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($product)) {
            $dates = $this->helperCalendar->getCurrentDatesOnFrontend($product);
            if ($dates->getStartDate()) {
                if (!$this->registry->registry('start_date')) {
                    $this->registry->register('start_date', $dates->getStartDate());
                    $this->registry->register('end_date', $dates->getEndDate());
                }
                if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
                    $returnValue = $this->priceCalculations->calculatePrice(
                        $product->getCustomOption('simple_product')->getProduct()->getId(),
                        $dates->getStartDate(),
                        $dates->getEndDate(),
                        $qty
                    );
                }
                if (!$this->registry->registry('start_date')) {
                    $this->registry->unregister('start_date');
                    $this->registry->unregister('end_date');
                }
                return $returnValue;
            }
            return $returnValue;
        }
        return $returnValue;
    }
}
