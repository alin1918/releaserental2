<?php

namespace SalesIgniter\Rental\Model;

use Magento\Framework\Model\AbstractModel;
use SalesIgniter\Rental\Api\Data\FixedRentalNamesInterface;

class FixedRentalNames extends AbstractModel implements FixedRentalNamesInterface
{
    protected function _construct()
    {
        $this->_init('SalesIgniter\Rental\Model\ResourceModel\FixedRentalNames');
    }

    /**
     * @param $fixedname
     *
     * @return $this
     */
    public function setNameId($fixedname)
    {
        return $this->setData('name_id', $fixedname);
    }

    public function getNameId()
    {
        return $this->getData('name_id');
    }

    /**
     * @param $fixedname
     *
     * @return $this
     */
    public function setName($fixedname)
    {
        return $this->setData('name', $fixedname);
    }

    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @param $fixedname
     *
     * @return $this
     */
    public function setCatalogRules($fixedname)
    {
        return $this->setData('catalog_rules', $fixedname);
    }

    public function getCatalogRules()
    {
        return $this->getData('catalog_rules');
    }
}
