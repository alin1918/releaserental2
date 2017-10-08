<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View;

class ReturnGridPanel extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'SalesIgniter_Rental::sales/order/view/return_grid_panel.phtml';
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * Info constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \SalesIgniter\Rental\Helper\Data                 $helperRental
     * @param \Magento\Framework\View\LayoutInterface          $layout
     * @param \SalesIgniter\Rental\Helper\Calendar             $helperCalendar
     * @param array                                            $data
     *
     * @internal param \SalesIgniter\Rental\Helper\Date $helperDate
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
        $this->layout = $context->getLayout();
    }

    public function getReturnGrid()
    {

        /** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGrid $block */
        $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGrid',
            'rental_returns_listing',
            ['is_uicomponent' => 1]
        );

        return $block->toHtml();
    }
}
