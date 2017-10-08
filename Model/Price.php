<?php

namespace SalesIgniter\Rental\Model;

class Price extends \Magento\Framework\Model\AbstractModel implements PriceInterface, \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'sirental_price';


    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\Price');
    }


}
