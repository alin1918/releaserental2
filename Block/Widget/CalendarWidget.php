<?php

namespace SalesIgniter\Rental\Block\Widget;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;

/**
 * Calendar Widget.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CalendarWidget extends \Magento\Framework\View\Element\Template implements
    \Magento\Widget\Block\BlockInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Product cache tag.
     */
    const CACHE_TAG = 'calendar_widget';
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    protected $_calendarHelper;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $_helperRental;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * Current product instance (override registry one).
     *
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $_product = null;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $stock;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteSessionFrontend;
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $quoteItemRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \SalesIgniter\Rental\Helper\Calendar              $calendarHelper
     * @param \SalesIgniter\Rental\Model\Product\Stock          $stock
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param \SalesIgniter\Rental\Helper\Data                  $helperRental
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface          $jsonEncoder
     * @param \Magento\Framework\Math\Random                    $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface       $localeResolver
     * @param DateTimeFormatterInterface                        $dateTimeFormatter
     * @param \Magento\Catalog\Model\Session                    $catalogSession
     * @param \Magento\Framework\View\LayoutInterface           $layout
     * @param \Magento\Framework\UrlInterface                   $urlBuilder
     * @param \Magento\Backend\Model\Session\Quote              $quoteSession
     * @param \Magento\Quote\Api\CartItemRepositoryInterface    $quoteItemRepository
     * @param CartRepositoryInterface                           $quoteSessionFrontend
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Framework\App\Http\Context               $httpContext
     * @param \Magento\Framework\Api\SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param array                                             $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Model\Product\Stock $stock,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        QuoteItemRepository $quoteItemRepository,
        CartRepositoryInterface $quoteSessionFrontend,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->_calendarHelper = $calendarHelper;
        $this->_helperRental = $helperRental;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonEncoder = $jsonEncoder;
        $this->mathRandom = $mathRandom;
        $this->localeResolver = $localeResolver;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->catalogSession = $catalogSession;
        $this->httpContext = $httpContext;
        //$this->_isScopePrivate = true;/*non-cacheable block*/
        $this->layout = $context->getLayout();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->quoteSession = $quoteSession;
        $this->stock = $stock;
        $this->quoteSessionFrontend = $quoteSessionFrontend;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->stockManagement = $stockManagement;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get Key pieces for caching block content.
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'SIRENTAL_CALENDAR_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'base_url' => $this->getBaseUrl(),
            'template' => $this->getTemplate(),
            $this->getTemplateFile(),
            serialize($this->getRequest()->getParams()),
            $this->catalogSession->getStartDateGlobal(),
            $this->catalogSession->getEndDateGlobal(),

        ];
    }

    /**
     * Return current product, if on product page.
     *
     * @return mixed|null
     */
    public function getProductObj()
    {
        if ($this->_product !== null) {
            return $this->_product;
        }
        if (!is_object($this->getProduct())) {
            $this->_product = $this->getProduct();
        }
        $this->_product = $this->_coreRegistry->registry('current_product');
        if (!$this->_product) {
            return null;
        }

        return $this->_product;
    }

    /**
     * Get URL for ajax price call.
     *
     * @return string
     */
    public function getCalculatePriceUrl()
    {
        $prodObj = $this->getProductObj();
        if ($prodObj === null) {
            return '';
        }

        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/price',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => $this->getProductObj()->getId(),
            ]
        );
    }

    /**
     * Get URL for ajax price call.
     *
     * @return string
     */
    public function getChangeGlobalsUrl()
    {
        if ($this->_helperRental->isFrontend()) {
            return $this->urlBuilder->getUrl(
                'salesigniter_rental/calendar/changeglobaldates',
                [
                    '_secure' => $this->getRequest()->isSecure(),
                ]
            );
        } else {
            return $this->urlBuilder->getUrl(
                'salesigniter_rental/ajax/changeglobaldates',
                [
                    '_secure' => $this->getRequest()->isSecure(),
                ]
            );
        }
    }

    /**
     * Get change dates on products url.
     *
     * @return string
     */
    public function getChangeDatesOnProductsUrl()
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/changedatesonproducts',
            [
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }

    /**
     * Get URL for ajax price call.
     *
     * @return string
     */
    public function getUpdateBookedUrl()
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/booked',
            [
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }

    /**
     * Retrieve html id of picker.
     *
     * @return string
     */
    protected function _getHtmlId()
    {
        return $this->escapeHtml('calendar_selector');
    }

    /*
     *
     * Retrieve html name of picker
     * @return string
     */

    protected function _getHtmlName()
    {
        return 'calendar_selector';
    }

    public function hasLegend()
    {
        return $this->_calendarHelper->showTurnovers();
    }

    /**
     * Load the localization block always.
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLocalizationCalendar()
    {
        /* @var \Magento\Framework\View\Element\Html\Calendar $block */
        $blockCalendar = $this->layout->createBlock('\Magento\Framework\View\Element\Html\Calendar');
        if ($this->_helperRental->isBackend()) {
            $blockCalendar->setTemplate('SalesIgniter_Rental::calendar/localization/calendar.phtml');
        } else {
            $blockCalendar->setTemplate('SalesIgniter_Rental::widgets/localization/calendar.phtml');
        }

        return $blockCalendar->toHtml();
    }

    /**
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlCalendar()
    {
        $htmlId = $this->mathRandom->getUniqueHash($this->_getHtmlId());
        $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $prodObj = $this->getProductObj();
        /*
         * here I get the settings for calendar
         */
        $storeHours = $this->_calendarHelper->storeHours();

        $timeFormat = $this->_calendarHelper->getCalendarTimeFormat();
        if (null !== $prodObj) {
            $useTimes = $this->_calendarHelper->useTimes($prodObj);
            $useTimesGrid = $this->_calendarHelper->useTimes($prodObj, true);
        } else {
            // avoid undefined noticed on homepage
            $useTimes = null;
            $useTimesGrid = null;
        }

        if ($this->getData('calendar_use_times')) {
            $useTimes = $this->getData('calendar_use_times') === '1';
        }
        $disabledDaysWeekStart = $this->_calendarHelper->getDisabledDaysWeekStart($prodObj);
        $disabledDaysWeekEnd = $this->_calendarHelper->getDisabledDaysWeekEnd($prodObj);
        $disabledDaysWeekTurnover = $this->_calendarHelper->getDisabledDaysWeek(ExcludedDaysWeekFrom::TURNOVER, $prodObj);
        $disabledDaysWeekPricing = $this->_calendarHelper->getDisabledDaysWeek(ExcludedDaysWeekFrom::PRICE, $prodObj);
        $disabledDates = $this->_calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::CALENDAR, $prodObj);
        $disabledDatesTurnover = $this->_calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::TURNOVER, $prodObj);
        $disabledDatesPricing = $this->_calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::PRICE, $prodObj);
        $numberOfMonths = $this->_calendarHelper->getNumberOfMonths();
        $alwaysShow = $this->_calendarHelper->getAlwaysShow($prodObj);

        $minimumPeriod = $this->_calendarHelper->stringPeriodToMinutes($this->_calendarHelper->getMinimumPeriod($prodObj));
        $maximumPeriod = $this->_calendarHelper->stringPeriodToMinutes($this->_calendarHelper->getMaximumPeriod($prodObj));
        $turnoverBefore = $this->_calendarHelper->stringPeriodToMinutes($this->_calendarHelper->getTurnoverBefore($prodObj));
        $turnoverAfter = $this->_calendarHelper->stringPeriodToMinutes($this->_calendarHelper->getTurnoverAfter($prodObj));
        $futureLimit = $this->_calendarHelper->stringPeriodToMinutes($this->_calendarHelper->getFutureLimit($prodObj));
        $allowZeroPrice = $this->_calendarHelper->getAllowZeroPrice();

        //$firstDateAvailable = $this->stockManagement->getFirstDateAvailable($prodObj);
        //$firstTimeAvailable = $this->stockManagement->getFirstTimeAvailable($prodObj);
        if (null !== $prodObj) {
            $availableQuantity = $this->stockManagement->getSirentQuantity($prodObj);
        } else {
            $availableQuantity = \SalesIgniter\Rental\Model\Product\Stock::OVERBOOK_QTY;
        }
        $firstDateAvailable = 0;
        $firstTimeAvailable = 0;
        if ($availableQuantity > 0) {
            $firstDateAvailable = $this->stockManagement->getFirstDateAvailable($prodObj);
            $firstTimeAvailable = $this->stockManagement->getFirstTimeAvailable($prodObj);
        }
        $isQuoteItem = false;

        if ($this->_helperRental->isBackend() && $this->getRequest()->getParam('id')) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $this->quoteSession->getQuote()->getItemById($this->getRequest()->getParam('id'));
            if ($quoteItem && is_object($quoteItem)) {
                $isQuoteItem = true;
            }
        }

        $fromDateInitial = '';
        $toDateInitial = '';
        if ($isQuoteItem) {
            $dates = $this->_calendarHelper->getDatesFromBuyRequest(
                $quoteItem->getOptionByCode('info_buyRequest'), $quoteItem->getProduct()
            );
            $fromDateInitial = $dates->getStartDate()->format('Y-m-d H:i:s');
            $toDateInitial = $dates->getEndDate()->format('Y-m-d H:i:s');
            if (!$useTimes) {
                $fromDateInitial = $dates->getStartDate()->format('Y-m-d').' 00:00:00';
                $toDateInitial = $dates->getEndDate()->format('Y-m-d').' 00:00:00';
            }
        } elseif ($this->_calendarHelper->getGlobalDates('from')) {
            $fromDateInitial = $this->_calendarHelper->getGlobalDates('from')->format('Y-m-d H:i:s');
            $toDateInitial = $this->_calendarHelper->getGlobalDates('to')->format('Y-m-d H:i:s');
            if (!$useTimes) {
                $fromDateInitial = $this->_calendarHelper->getGlobalDates('from')->format('Y-m-d').' 00:00:00';
                $toDateInitial = $this->_calendarHelper->getGlobalDates('to')->format('Y-m-d').' 00:00:00';
            }
        }

        $html = '<div class="range sirent_calendar" id="'.$htmlId.'_range">';
        $newClass = '';
        $fixedOptions = false;
        if (null !== $prodObj && !$this->_helperRental->isBackend()) {
            $fixedOptions = $this->_calendarHelper->getFixedOptions($prodObj);
        }
        if ($this->_helperRental->isBackend()) {
            $fixedOptions = false;
            // $useTimes = true;
            $useTimesGrid = false;
            $alwaysShow = false;
            $numberOfMonths = 1;
        }
        $fixedRentalLength = 0;
        if (null !== $prodObj && ($this->_helperRental->isConfigurable($prodObj) || $this->_helperRental->isBundle($prodObj))) {
            $html .= '<div class="configurable_textred" style="color:#ff0000">'.__('To select dates first choose options above').'</div>';
        }
        if ($fixedOptions !== false) {
            $newClass = 'hiddenDates';
            $html .= $this->_calendarHelper->getFixedTemplate($prodObj);
            $fixedRentalLength = $this->_calendarHelper->stringPeriodToMinutes($fixedOptions['length'][0]);
        }
        $html .= '<div class="range-line date">'.
            '<input readonly="readonly" 
                    type="text" 
                    name="' .$this->_getHtmlName().'[from]" 
                    id="' .$htmlId.'_from'.($alwaysShow ? '_alt' : '').'"'.
            ' value="'.''.'" 
                    class="input-text no-changes ' .($alwaysShow ? 'hiddenDates' : '').'" 
                    placeholder="' .__('From').'" '.
            $this->getUiId('filter', $this->_getHtmlName(), 'from').'/>';
        if ($alwaysShow) {
            $html .= '<div id="'.$htmlId.'_from'.'">'.'</div>';
        }
        $html .= '</div>';

        $html .= '<div class="range-line date">'.
            '<input readonly="readonly" 
                        type="text" name="' .$this->_getHtmlName().'[to]" 
                        id="' .$htmlId.'_to'.($alwaysShow ? '_alt' : '').'"'.' 
                        value="' .''.'" 
                        class="input-text no-changes ' .$newClass.' '.($alwaysShow ? 'hiddenDates' : '').'" 
                        placeholder="' .__('To').'" '.
            $this->getUiId('filter', $this->_getHtmlName(), 'to').'/>';
        if ($alwaysShow) {
            $html .= '<div id="'.$htmlId.'_to'.'">'.'</div>';
        }
        $html .= '</div>';

        $html .= '<input type="hidden" 
                        name="' .$this->_getHtmlName().'[locale]"'.' 
                        value="' .$this->localeResolver->getLocale().'"/>';
        $html .= '<input type="hidden" 
                        name="sirent_product_id"' .' 
                        value="' .($prodObj === null ? 0 : $prodObj->getId()).'"/>';
        if ($isQuoteItem) {
            $html .= '<input type="hidden" 
                        name="sirent_quote_id"' .' 
                        value="' .$this->getRequest()->getParam('id').'"/>';
        }
        if ($this->_helperRental->isFrontendConfigureCart() && $this->getRequest()->getParam('id')) {
            $html .= '<input type="hidden" 
                        name="sirent_quote_id_frontend"' .' 
                        value="' .$this->getRequest()->getParam('id').'"/>';
        }
        if ($this->getData('calendar_use_times')) {
            $html .= '<input type="hidden" 
                        name="calendar_use_times"' .' 
                        value="' .$this->getData('calendar_use_times').'"/>';
        }
        if ($this->getData('category_to_go')) {
            $html .= '<input type="hidden" 
                        name="category_to_go"' .' 
                        value="' .$this->getData('category_to_go').'"/>';
        }
        $html .= '</div>';

        $html .= $this->getLocalizationCalendar();

        $html .= '<script>
            require(["jquery", "pprdatepicker"], function($){
                $("#' .$htmlId.'_range").pprdatepicker({
                    dateFormat: "' .$format.'",
                    altFormat: "' .$format.'",
                    buttonText: "' .$this->escapeHtml('').'",  
                    priceUpdateUrl: "' .$this->getCalculatePriceUrl().'",
                    updateBookedUrl: "' .$this->getUpdateBookedUrl().'",
                    changeGlobalsUrl: "' .$this->getChangeGlobalsUrl().'",
                    changeDatesOnProductsUrl: "' .$this->getChangeDatesOnProductsUrl().'",
                    sirentProductId: "' .($prodObj === null ? 0 : $prodObj->getId()).'",                                         
                    showWeek: false,
                    storeHours: ' .$this->_jsonEncoder->encode($storeHours).',
                    fromDateInitial: "' .$fromDateInitial.'",
                    toDateInitial: "' .$toDateInitial.'",
                    firstDateAvailable: "' .$firstDateAvailable.'",
                    firstTimeAvailable: "' .$firstTimeAvailable.'",
                    disabledDaysWeekStart: ' .$this->_jsonEncoder->encode($disabledDaysWeekStart).',
                    disabledDaysWeekEnd: ' .$this->_jsonEncoder->encode($disabledDaysWeekEnd).',
                    disabledDaysWeekTurnover: ' .$this->_jsonEncoder->encode($disabledDaysWeekTurnover).',
                    disabledDaysWeekPricing: ' .$this->_jsonEncoder->encode($disabledDaysWeekPricing).',
                    disabledDates: ' .$this->_jsonEncoder->encode($disabledDates).',
                    bookedDates: "",
                    disabledDatesTurnover: ' .$this->_jsonEncoder->encode($disabledDatesTurnover).',
                    disabledDatesPricing: ' .$this->_jsonEncoder->encode($disabledDatesPricing).',
                    numberOfMonths: ' .$numberOfMonths.',
                    alwaysShow: ' .($alwaysShow ? 'true' : 'false').',
                    allowZero: ' .($allowZeroPrice ? 'true' : 'false').',
                    minimumPeriod: ' .$minimumPeriod.',
                    maximumPeriod: ' .$maximumPeriod.',
                    turnoverBefore: ' .$turnoverBefore.',
                    turnoverAfter: ' .$turnoverAfter.',
                    futureLimit: ' .$futureLimit.',
                    showOn: "both",                    
                    showsTime: ' .($useTimes ? 'true' : 'false').',
                    timeNoGrid: ' .($useTimesGrid ? 'true' : 'false').',
                    fixedRentalLength: ' .$fixedRentalLength.',
                    showAnim:"slideDown",                                 
                    showHour: true,
                    showMinute: true,             
                    showOtherMonths: false,
                    selectOtherMonths:false,       
                    stepMinute: ' .(int) $this->_calendarHelper->timeIncrement().',                    
                    controlType: "select",
                    timeFormat: "' .$timeFormat.'",
                    from: {
                        id: "' .$htmlId.'_from"
                    },
                    to: {
                        id: "' .$htmlId.'_to"
                    }
                })
            });
        </script>';

        return $html;
    }

    /**
     * @return \SalesIgniter\Rental\Helper\Calendar
     */
    public function getCalendarHelper()
    {
        return $this->_calendarHelper;
    }

    /**
     * Return identifiers for produced content.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
