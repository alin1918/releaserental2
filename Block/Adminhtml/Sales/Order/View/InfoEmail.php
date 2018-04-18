<?php
namespace SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View;

class InfoEmail extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'SalesIgniter_Rental::sales/order/view/rental_dates_info_email.phtml';
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

    public function getRentalDates()
    {
        $dates = $this->helperCalendar->getDatesForOrder($this->getData('hasOrder'));
        $htmlDates = '';
        
        if (isset($dates[0]['is_buyout'])
            && $dates[0]['is_buyout'] == 1
        ) {
            return $htmlDates;
        } 
        
        if (count($dates) > 0) {
            if ($dates[0]['start_date']->format('H:i') !== '00:00') {
                $htmlDates = '<p>' . __('Start Date: ') . $this->helperCalendar->formatDateTime($dates[0]['start_date']) . '</p>';
            } else {
                $htmlDates = '<p>' . __('Start Date: ') . $this->helperCalendar->formatDate($dates[0]['start_date']) . '</p>';
            }
            if ($dates[0]['end_date']->format('H:i') !== '00:00') {
                $htmlDates .= '<p>' . __('End Date: ') . $this->helperCalendar->formatDateTime($dates[0]['end_date']) . '</p>';
            } else {
                $htmlDates .= '<p>' . __('End Date: ') . $this->helperCalendar->formatDate($dates[0]['end_date']) . '</p>';
            }
        }
        return $htmlDates;
    }
}
