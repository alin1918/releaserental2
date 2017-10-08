<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal;

use Magento\Backend\Block\Template as BlockTemplate;

class Products extends BlockTemplate
{
    /** @var \SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable $_gridBlock */
    protected $_gridBlock;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_gridBlock = $this->getLayout()
            ->createBlock('SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Grid');
        //$this->_gridBlock->setCollection($this->getParentBlock()->getOrder()->getItemsCollection());
    }

    public function getGridHtml()
    {
        $this->_gridBlock->setCollection($this->getParentBlock()->getOrder()->getItemsCollection());
        $this->_gridBlock->addColumn('product_quantity', [
            'header' => __('Quantity'),
            'index' => 'qty_ordered',
            'type' => 'number'
        ]);

        $this->_gridBlock->addColumn('product_name', [
            'header' => __('Product'),
            'index' => 'name'
        ]);

        $this->_gridBlock->addColumn('product_price', [
            'header' => __('Price'),
            'index' => 'price',
            'type' => 'currency'
        ]);

        return $this->_gridBlock->toHtml();
    }
}
