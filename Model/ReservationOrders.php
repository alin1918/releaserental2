<?php

namespace SalesIgniter\Rental\Model;

class ReservationOrders extends \Magento\Framework\Model\AbstractModel implements ReservationOrdersInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sirental_reservationorders';

    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\ReservationOrders');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
