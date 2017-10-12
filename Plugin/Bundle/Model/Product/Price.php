<?php

namespace SalesIgniter\Rental\Plugin\Bundle\Model\Product;

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
     * @param \Magento\Bundle\Model\Product\Price $subject
     * @param \Closure                            $proceed
     * @param                                     $product
     * @param null                                $qty
     *
     * @return float|mixed
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetTotalBundleItemsPrice(
        \Magento\Bundle\Model\Product\Price $subject,
        \Closure $proceed,
        $product,
        $qty = null
    ) {
        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($product)) {
            $dates = $this->helperCalendar->getCurrentDatesOnFrontend($product);
            $startDate = null;
            if ($this->registry->registry('start_date')) {
                $startDate = $this->registry->registry('start_date');
                $endDate = $this->registry->registry('end_date');
            } elseif ($dates->getStartDate()) {
                $startDate = $dates->getStartDate();
                $endDate = $dates->getEndDate();
            }
            if ($startDate !== null && !$this->helperRental->isPricePerProduct($product)) {
                return $this->priceCalculations->calculatePrice($product->getId(), $startDate, $endDate, $qty);
            }
        }

        return $proceed($product, $qty);
    }

    /**
     * @param \Magento\Bundle\Model\Product\Price $subject
     * @param \Closure                            $proceed
     * @param                                     $bundleProduct
     * @param \Magento\Catalog\Model\Product      $selectionProduct
     * @param float                               $bundleQty
     * @param float                               $selectionQty
     * @param bool                                $multiplyQty
     * @param bool                                $takeTierPrice
     *
     * @return float
     *
     * @throws \LogicException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetSelectionFinalTotalPrice(
        \Magento\Bundle\Model\Product\Price $subject,
        \Closure $proceed,
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        $returnValue = $proceed(
            $bundleProduct,
            $selectionProduct,
            $bundleQty,
            $selectionQty,
            $multiplyQty,
            $takeTierPrice
        );

        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($bundleProduct)) {
            $dates = $this->helperCalendar->getCurrentDatesOnFrontend($bundleProduct);
            if ($dates->getStartDate()) {
                if ($this->helperRental->isPricePerProduct($bundleProduct)) {
                    $hasDates = false;
                    if ($this->registry->registry('start_date')) {
                        $hasDates = true;
                    }
                    if ($hasDates === false) {
                        $this->registry->register('start_date', $dates->getStartDate());
                        $this->registry->register('end_date', $dates->getEndDate());
                    }
                    $returnValue = $selectionQty * $selectionProduct->getFinalPrice($selectionQty);
                    if ($hasDates === false) {
                        $this->registry->unregister('start_date');
                        $this->registry->unregister('end_date');
                    }
                } else {
                    $returnValue = $this->priceCalculations->calculatePrice($bundleProduct->getId(), $dates->getStartDate(), $dates->getEndDate(), 1);
                }
            }
        }

        return $returnValue;
    }
}
