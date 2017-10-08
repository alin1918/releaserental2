<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Observer;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\ObserverInterface;
use SalesIgniter\Rental\Api\StockManagementInterface;
use SalesIgniter\Rental\Model\Product\PriceCalculations;

class UpdateBundleItemPrice implements ObserverInterface
{
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;

    /**
     * @param \SalesIgniter\Rental\Api\StockManagementInterface    $stockManagement
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     * @param \SalesIgniter\Rental\Helper\Calendar                 $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        PriceCalculations $priceCalculations,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Data $helperRental

    ) {
        $this->stockManagement = $stockManagement;
        $this->helperRental = $helperRental;
        $this->priceCalculations = $priceCalculations;
        $this->calendarHelper = $calendarHelper;
    }

    /**
     * Cleanup product reviews after product delete
     *
     * @param   \Magento\Framework\Event\Observer $observer
     *
     * @return  $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     * * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $item \Magento\Quote\Model\Quote\Item */
        $item = $observer->getEvent()->getQuoteItem();
        if ($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE && $this->helperRental->isRentalType($item->getProduct()) && !$this->helperRental->isPricePerProduct($item->getProduct())) {
            $pCount = [];
            $dates = $this->calendarHelper->getDatesFromBuyRequest(
                $item->getOptionByCode('info_buyRequest'), $item->getProduct()
            );
            if (!$dates->getIsBuyout()) {
                $bundlePrice = $this->priceCalculations->calculatePrice(
                    $item->getProduct()->getId(),
                    $dates->getStartDate(),
                    $dates->getEndDate(),
                    $item->getQty()
                );
            } else {
                $bundlePrice = $this->priceCalculations->calculateBuyoutPrice($item->getProduct()->getId());
            }
            $item->setCustomPrice($bundlePrice);
            $item->setOriginalCustomPrice($bundlePrice);
            $item->getProduct()->setIsSuperMode(true);
            $pCount[$item->getProduct()->getId()]['price'] = $bundlePrice;
            $pCount[$item->getProduct()->getId()]['init'] = 0;

            if ($dates->getStartDate() && $dates->getEndDate()) {
                foreach ($item->getChildren() as $bundleItems) {
                    /** @var $bundleItems\Magento\Quote\Model\Quote\Item */

                    $itemPrice = 0;
                    if (array_key_exists($bundleItems->getParentItem()->getProduct()->getId(), $pCount) && $pCount[$bundleItems->getParentItem()->getProduct()->getId()]['init'] === 0) {
                        $itemPrice = $pCount[$bundleItems->getParentItem()->getProduct()->getId()]['price'];
                        $pCount[$bundleItems->getParentItem()->getProduct()->getId()]['init'] = 1;
                    }
                    $bundleItems->setCustomPrice($itemPrice);
                    $bundleItems->setOriginalCustomPrice($itemPrice);
                    $bundleItems->getProduct()->setIsSuperMode(true);
                }
                $item->getProduct()->setIsSuperMode(true);
            }
        }

        return $this;
    }
}
