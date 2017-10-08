<?php
namespace SalesIgniter\Rental\Plugin\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class Item
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
     * @param \Magento\CatalogInventory\Model\StockStateProvider $subject
     * @param StockItemInterface                                 $stockItem
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */

    public function afterGetManageStock(
        \Magento\CatalogInventory\Model\Stock\Item $subject
    ) {
        //if ($this->_helperRental->isRentalType($subject->getProductId())) {
        //    $subject->setManageStock(false);
         //   $subject->setIsInStock(true);
       // }
        //return false;
    }
}
