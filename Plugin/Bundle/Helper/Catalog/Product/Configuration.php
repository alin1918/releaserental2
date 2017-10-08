<?php

namespace SalesIgniter\Rental\Plugin\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

class Configuration
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

    //function beforeMETHOD($subject, $arg1, $arg2){}
    //function aroundMETHOD($subject, $procede, $arg1, $arg2){return $proceed($arg1, $arg2);}
    //function afterMETHOD($subject, $result){return $result;}
    /**
     * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $subject
     * @param \Closure                                             $proceed
     * @param ItemInterface                                        $item
     * @param \Magento\Catalog\Model\Product                       $selectionProduct
     *
     * @return float
     *
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \RuntimeException
     */
    public function aroundGetSelectionFinalPrice(
        \Magento\Bundle\Helper\Catalog\Product\Configuration $subject,
        \Closure $proceed,
        ItemInterface $item,
        \Magento\Catalog\Model\Product $selectionProduct
    ) {
        $product = $item->getProduct();
        $returnValue = $proceed($item, $selectionProduct);
        if ($this->helperRental->isFrontendAndBackendEdit() && $this->helperRental->isRentalType($product)) {
            $dates = $this->helperCalendar->getCurrentDatesOnFrontend($product);
            if ($dates->getStartDate()) {
                if ($this->helperRental->isPricePerProduct($product)) {
                    $hasDates = false;
                    if ($this->registry->registry('start_date')) {
                        $hasDates = true;
                    }
                    if ($hasDates === false) {
                        $this->registry->register('start_date', $dates->getStartDate());
                        $this->registry->register('end_date', $dates->getEndDate());
                    }
                    $returnValue = $selectionProduct->getFinalPrice($selectionProduct->getQty());
                    if ($hasDates === false) {
                        $this->registry->unregister('start_date');
                        $this->registry->unregister('end_date');
                    }
                } else {
                    $returnValue = $this->priceCalculations->calculatePrice($product->getId(), $dates->getStartDate(), $dates->getEndDate(), 1);
                }
            }
        }

        return $returnValue;
    }
}
