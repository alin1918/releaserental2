<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Widget;

use Magento\Backend\Block\Widget\Grid\Container as CoreGridContainer;

class DataTable extends CoreGridContainer
{
    /**
     * @var \SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Grid
     */
    protected $_gridBlock;

    protected function _construct()
    {
        $this->_controller = 'adminhtml_widget_datatable';
        $this->_blockGroup = 'SalesIgniter_Rental';

        $this->removeButton('add');

        parent::_construct();

        $this->_gridBlock = $this->getLayout()
            ->createBlock('\SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Grid');

        $this->_gridBlock->setSaveParametersInSession(false);
    }

    /**
     * @param $Text
     *
     * @return $this
     */
    public function setHeaderText($Text)
    {
        $this->_headerText = __($Text);
        return $this;
    }

    /**
     * @param $Collection
     *
     * @return $this
     */
    public function setCollection($Collection)
    {
        $this->_gridBlock->setCollection($Collection);
        return $this;
    }

    /**
     * @param $columnId
     * @param $column
     *
     * @return $this
     */
    public function addColumn($columnId, $column)
    {
        $this->_gridBlock->addColumn($columnId, $column);
        return $this;
    }

    /**
     * @param $Url
     *
     * @return $this
     */
    public function setGridUrl($Url)
    {
        $this->_gridBlock->setData('grid_url', $Url);
        return $this;
    }

    /**
     * @param $Url
     *
     * @return $this
     */
    public function setRowUrl($Url)
    {
        $this->_gridBlock->setData('row_url', $Url);
        return $this;
    }

    /**
     * @return DataTable\Grid
     */
    public function getGridBlock()
    {
        return $this->_gridBlock;
    }
}
