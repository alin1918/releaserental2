<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable;

use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Framework\DataObject;

class Grid extends ExtendedGrid
{
    protected function _construct()
    {
        parent::_construct();
        //$this->setId('DataTable');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getCollection()) {
            if ($this->getCollection()->isLoaded()) {
                $CollectionItems = $this->getCollection()->getItems();
            }
            parent::_prepareCollection();

            if (!$this->_isExport) {
                if (isset($CollectionItems)) {
                    foreach ($CollectionItems as $Item) {
                        if (!$this->getCollection()->getItemById($Item->getId())) {
                            $this->getCollection()->addItem($Item);
                        }
                    }
                }

                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getData('row_url');
    }
}
