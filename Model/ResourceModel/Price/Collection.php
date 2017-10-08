<?php

namespace SalesIgniter\Rental\Model\ResourceModel\Price;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\Price', 'SalesIgniter\Rental\Model\ResourceModel\Price');
    }
    
    public function joinForSpecialDates(){
        $this->getSelect()->joinLeft(
            ['pricedates' => $this->getTable('sirental_pricebydate')],
            'main_table.pricesbydate_id=pricedates.pricebydate_id',
            ['description' => 'description']
        );
        return $this;
    }
}