<?php
/**
 *
 */

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Model\AbstractModel;
use SalesIgniter\Rental\Api\Data\InventoryGridInterface;

class InventoryGrid extends AbstractModel implements InventoryGridInterface
{
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\InventoryGrid');
    }

    /**
     * @param $productId
     *
     * @return $this
     *
     */
    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }
}
