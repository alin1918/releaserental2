<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal;

use Magento\Backend\Block\Template as BlockTemplate;

class Orders extends BlockTemplate
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
    }

    public function getGridHtml()
    {
        $this->_gridBlock->setCollection($this->getParentBlock()->getReservationOrders());
        $this->_gridBlock->addColumn('increment_id', [
            'header' => __('Order Number'),
            'index' => 'increment_id',
            'renderer' => 'SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer\Buildhtml',
            'renderConfig' => [
                'template' => '<a data-order_id="{{reservationorder_id}}" href="' . $this->getUrl('sales/order/view', ['order_id' => '{{order_id}}']) . '" target="_blank">#{{increment_id}}</a>'
            ]
        ]);

        $this->_gridBlock->addColumn('customer_name', [
            'header' => __('Customer'),
            'index' => 'customer_name',
            'renderer' => 'SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer\Buildhtml',
            'renderConfig' => [
                'template' => '<a href="' . $this->getUrl('customer/index/edit', ['id' => '{{customer_id}}']) . '" target="_blank">{{customer_name}}</a>'
            ]
        ]);

        $this->_gridBlock->addColumn('start_date_with_turnover', [
            'header' => __('Start Date w/Turnover'),
            'index' => 'start_date_with_turnover',
            'type' => 'datetime'
        ]);

        $this->_gridBlock->addColumn('start_date', [
            'header' => __('Start Date'),
            'index' => 'start_date',
            'type' => 'datetime'
        ]);

        $this->_gridBlock->addColumn('end_date', [
            'header' => __('End Date'),
            'index' => 'end_date',
            'type' => 'datetime'
        ]);

        $this->_gridBlock->addColumn('end_date_with_turnover', [
            'header' => __('End Date w/Turnover'),
            'index' => 'end_date_with_turnover',
            'type' => 'datetime'
        ]);

        return $this->_gridBlock->toHtml();
    }
}
