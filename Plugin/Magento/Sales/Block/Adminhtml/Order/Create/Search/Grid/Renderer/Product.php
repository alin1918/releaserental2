<?php
namespace SalesIgniter\Rental\Plugin\Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

class Product
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text|Closure
     */
    private $text;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param \SalesIgniter\Rental\Helper\Data                        $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                    $helperCalendar
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface       $priceCurrency
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text $text
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations    $priceCalculations
     *
     * @internal param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text $text,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
    ) {
        $this->_helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
        $this->priceCalculations = $priceCalculations;
        $this->priceCurrency = $priceCurrency;
        $this->text = $text;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product $subject
     * @param \Closure                                                                 $proceed
     * @param \Magento\Framework\DataObject                                            $row
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundRender(
        \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $row
    ) {
        $returnValue = $proceed($row);
        $this->text->setColumn($subject->getColumn());
        $rendered = $this->text->render($row);
        $isConfigurable = $row->canConfigure();
        $style = $isConfigurable ? '' : 'disabled';
        $prodAttributes = $isConfigurable ? sprintf(
            'list_type = "product_to_add" product_id = %s',
            $row->getId()
        ) : 'disabled="disabled"';

        if ($this->_helperRental->isRentalTypeSimple($row->getId())) {
            $style .= ' is_rent_type';
            return $rendered . sprintf(
                    '<a href="javascript:void(0)" class="action-configure action-configure-notfirst %s" %s>%s</a>',
                    $style,
                    $prodAttributes,
                    __('Configure')
                );
        } elseif ($this->_helperRental->isRentalType($row->getId())) {
            $style .= ' is_rent_type';
            return $rendered . sprintf(
                    '<a href="javascript:void(0)" class="action-configure %s" %s>%s</a>',
                    $style,
                    $prodAttributes,
                    __('Configure')
                );
        } else {
            return $returnValue;
        }
    }
}
