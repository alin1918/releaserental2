<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Plugin\Magento\Model;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    /**
     * @var Type
     */
    private $type;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @param Type                                 $type
     * @param \SalesIgniter\Rental\Helper\Data     $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar $helperCalendar
     */
    public function __construct(
        Type $type,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->type = $type;
        $this->helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
    }

    /**
     * Function used to check if the product has times enabled.
     * If it doesn't it replace the option to be of type date and not datetime
     *
     * @param CatalogProduct $product
     * @param                $result
     *
     * @return CatalogProduct\Option|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetOptionById(
        CatalogProduct $product,
        $result
    ) {
        if (is_object($result) && ($result->getTitle() === 'Start Date:' || $result->getTitle() === 'End Date:') && $this->helperRental->isRentalType($product)) {
            $useTimes = $this->helperCalendar->useTimes($product);
            if (!$useTimes) {
                $result->setType('date');
            }
        }
        return $result;
    }
}
