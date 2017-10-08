<?php

namespace SalesIgniter\Rental\Plugin\Magento\Sales\Block\Adminhtml\Order\Create\Items;

class Grid
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
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
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid $subject
     * @param \Closure                                               $proceed
     * @param \Magento\Quote\Model\Quote\Item                        $item
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetConfigureButtonHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid $subject,
        \Closure $proceed,
        $item
    ) {
        $returnValueButton = $proceed($item);
        if ($this->_helperRental->isRentalType($item->getProduct())) {
            $dates = $this->helperCalendar->getDatesFromBuyRequest(
                $item->getOptionByCode('info_buyRequest'), $item->getProduct()
            );

            $returnValue = '';
            if ($dates->getStartDate() && $dates->getEndDate()) {
                $returnValue = __('Start Date: ').$this->helperCalendar->formatDateTime($dates->getStartDate());
                $returnValue .= '<br/>';
                $returnValue .= __('End Date: ').$this->helperCalendar->formatDateTime($dates->getEndDate());
            }

            return $returnValue.$returnValueButton;
        }

        return $returnValueButton;
    }
}
