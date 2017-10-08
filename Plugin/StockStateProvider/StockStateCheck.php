<?php
namespace SalesIgniter\Rental\Plugin\StockStateProvider;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class StockStateCheck
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental
    ) {
        $this->_helperRental = $helperRental;
    }


    /**
     * @param \Magento\CatalogInventory\Model\StockStateProvider                      $subject
     * @param \Magento\CatalogInventory\Model\Stock\Item                              $stockItem
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
    ) {
        if ($this->_helperRental->isRentalType($stockItem->getProductId())) {
            $stockItem->setQty(1);
            return [$stockItem, $qty];
        }
    }
}
