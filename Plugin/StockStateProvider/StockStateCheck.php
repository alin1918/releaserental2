<?php

namespace SalesIgniter\Rental\Plugin\StockStateProvider;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use SalesIgniter\Rental\Model\Product\Stock;

class StockStateCheck
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
    )
    {
        $this->_helperRental = $helperRental;
        $this->stockManagement = $stockManagement;
    }


    /**
     * @param \Magento\CatalogInventory\Model\StockStateProvider $subject
     * @param \Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @param                                                                         $qty
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedParameters)
     */
    public function beforeCheckQty(
        \Magento\CatalogInventory\Model\StockStateProvider $subject,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        $qty
    )
    {
        if ($this->_helperRental->isRentalType($stockItem->getProductId())) {
            $stockItem->setQty(Stock::OVERBOOK_QTY);//$this->stockManagement->getSirentQuantity($stockItem->getProductId()));
            return [$stockItem, $qty];
        }
    }
}
