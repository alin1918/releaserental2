<?php

namespace SalesIgniter\Rental\Plugin\Product;

/*not used for the moment. Using event for now*/
use SalesIgniter\Rental\Model\Product\PriceCalculations;

class BundlePrice
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $helperRental
     */
    protected $helperRental;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \Magento\Framework\Registry|Magento\Catalog\Model\Product\Interceptor
     */
    private $registry;
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
        PriceCalculations $priceCalculations,
        \Magento\Framework\Registry $registry
    ) {
        $this->helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
        $this->registry = $registry;
        $this->priceCalculations = $priceCalculations;
    }

    /**
     * Return product base price
     *
     * @param \Magento\Bundle\Model\Product\Price $subject
     * @param \Closure                            $proceed
     * @param \Magento\Catalog\Model\Product      $product
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetPrice(
        \Magento\Bundle\Model\Product\Price $subject,
        \Closure $proceed,
        $product
    ) {
        $returnValue = $proceed($product);
        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($product) && $this->helperRental->isPricePerProduct($product)) {
            $returnValue = 0;
        }
        return $returnValue;
    }

    /**
     * @param \Magento\Bundle\Model\Product\Price $subject
     * @param \Closure                            $proceed
     * @param   float                             $qty
     * @param   \Magento\Catalog\Model\Product    $product
     *
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetFinalPrice(
        \Magento\Bundle\Model\Product\Price $subject,
        \Closure $proceed,
        $qty,
        $product
    ) {
        $returnValue = $proceed($qty, $product);
        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($product) && !$this->helperRental->isPricePerProduct($product)) {
            $startDate = null;
            $dates = $this->helperCalendar->getCurrentDatesOnFrontend($product);
            if ($this->registry->registry('start_date')) {
                $startDate = $this->registry->registry('start_date');
                $endDate = $this->registry->registry('end_date');
            } elseif ($dates->getStartDate()) {
                $startDate = $dates->getStartDate();
                $endDate = $dates->getEndDate();
            }
            if ($startDate !== null) {
                $returnValue = $this->priceCalculations->calculatePrice(
                    $product->getId(),
                    $startDate,
                    $endDate,
                    $qty
                );
            }
        }

        return $returnValue;
    }
}
