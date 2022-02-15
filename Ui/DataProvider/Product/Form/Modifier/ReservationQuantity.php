<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * Disable Quantity field by default
 */
class ReservationQuantity extends AbstractModifier
{
    const CODE_QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';
    const CODE_QUANTITY = 'qty';
    const CODE_QTY_CONTAINER = 'quantity_and_stock_status_qty';
    const CODE_ADVANCED_INVENTORY_BUTTON = 'advanced_inventory_button';
    /**
     * @var \SalesIgniter\Rental\Helper\Data|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier\LocatorInterface|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $locator;


    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \SalesIgniter\Rental\Helper\Data                $helperRental
     */
    public function __construct(
        LocatorInterface $locator,
        \SalesIgniter\Rental\Helper\Data $helperRental
    ) {
        $this->helperRental = $helperRental;
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $product = $this->locator->getProduct();
        if ($this->helperRental->isRentalType($product)) {
            if ($groupCode = $this->getGroupCodeByField($meta, self::CODE_QTY_CONTAINER)) {
                $parentChildren = &$meta[$groupCode]['children'];
                if (!empty($parentChildren[self::CODE_QTY_CONTAINER])) {
                    $parentChildren[self::CODE_QTY_CONTAINER] = array_replace_recursive(
                        $parentChildren[self::CODE_QTY_CONTAINER],
                        [
                            'children' => [
                                self::CODE_QUANTITY => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => ['visible' => false],
                                        ],
                                    ],
                                ],
                                self::CODE_ADVANCED_INVENTORY_BUTTON => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => ['visible' => false],
                                        ],
                                    ],
                                ],

                            ],
                        ]
                    );
                }
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
