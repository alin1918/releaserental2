<?php
namespace SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment;

use Magento\Framework\UrlInterface;

class Serials extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'SalesIgniter_Rental::sales/order/shipment/serial.phtml';
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
     * @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment\UrlInterface|Wa72\HtmlPageDom\HtmlPageCrawler
     */
    private $urlBuilder;

    /**
     * Info constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \SalesIgniter\Rental\Helper\Data                 $helperRental
     * @param \Magento\Framework\View\LayoutInterface          $layout
     * @param \Magento\Framework\UrlInterface                  $urlBuilder
     * @param \SalesIgniter\Rental\Helper\Calendar             $helperCalendar
     * @param array                                            $data
     *
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
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return string
     */
    public function getSuggestSerialsUrl()
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/suggestserials',
            [
                'product_id' => $this->getProductId(),
            ]
        );
    }
}
