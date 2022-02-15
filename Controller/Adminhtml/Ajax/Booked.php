<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\ResultFactory;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Booked extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \SalesIgniter\Rental\Helper\Product
     */
    private $productHelper;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $attributeAction;
    /**
     * @var \SalesIgniter\Rental\Model\Stock
     */
    private $stock;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context             $context
     * @param \Magento\Catalog\Model\ProductFactory             $productModelFactory
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Catalog\Helper\Data                      $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar              $calendarHelper
     * @param \Magento\Catalog\Model\Product\Action             $attributeAction
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param \SalesIgniter\Rental\Model\Product\Stock          $stock
     * @param \Magento\Backend\Model\Session\Quote              $quoteSession
     * @param \Magento\Catalog\Model\Session                    $catalogSession
     * @param \SalesIgniter\Rental\Helper\Product               $productHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \Magento\Catalog\Model\Product\Action $attributeAction,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \SalesIgniter\Rental\Model\Product\Stock $stock,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \SalesIgniter\Rental\Helper\Product $productHelper
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        parent::__construct($context);
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->attributeAction = $attributeAction;
        $this->stock = $stock;
        $this->quoteSession = $quoteSession;
        $this->catalogSession = $catalogSession;
        $this->stockManagement = $stockManagement;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.LongVariableNames)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (array_key_exists('qty', $params)) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            );
            $params['qty'] = $filter->filter($params['qty']);
        }
        $product = null;
        if ($params['sirent_product_id'] > 0) {
            $product = $this->productHelper->initProduct(
                (int)$params['sirent_product_id'],
                $params
            );
        }

        //these are product specific when using configurable also per shipping
        $disabledDaysWeekStart = $this->calendarHelper->getDisabledDaysWeekStart($product);
        $disabledDaysWeekEnd = $this->calendarHelper->getDisabledDaysWeekEnd($product);
        $disabledDaysWeekTurnover = $this->calendarHelper->getDisabledDaysWeek(ExcludedDaysWeekFrom::TURNOVER, $product);
        $disabledDates = $this->calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::CALENDAR, $product);
        $disabledDatesFull = $this->calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::FULL_CALENDAR, $product);
        $disabledDatesTurnover = $this->calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::TURNOVER, $product);
        $disabledDatesFullTurnover = $this->calendarHelper->getExcludedDates(ExcludedDaysWeekFrom::FULL_TURNOVER, $product);
        $turnoverBefore = $this->calendarHelper->stringPeriodToMinutes($this->calendarHelper->getTurnoverBefore($product));
        $turnoverAfter = $this->calendarHelper->stringPeriodToMinutes($this->calendarHelper->getTurnoverAfter($product));
        $minimumPeriod = $this->calendarHelper->stringPeriodToMinutes($this->calendarHelper->getMinimumPeriod($product));
        $maximumPeriod = $this->calendarHelper->stringPeriodToMinutes($this->calendarHelper->getMaximumPeriod($product));

        $fromDateInitial = '';
        $toDateInitial = '';
        $isQuoteItem = false;
        if ($this->getRequest()->getParam('sirent_quote_id')) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $this->quoteSession->getQuote()->getItemById($this->getRequest()->getParam('sirent_quote_id'));
            if ($quoteItem && is_object($quoteItem)) {
                $isQuoteItem = true;
            }
        }
        $startDate = null;
        $endDate = null;
        if (array_key_exists('calendar_selector', $params) &&
            array_key_exists('from', $params['calendar_selector']) &&
            $params['calendar_selector']['from'] !== '' &&
            $params['calendar_selector']['to'] !== ''
        ) {
            $hasTimes = false;
            if ($product !== null) {
                $hasTimes = $product->getSirentUseTimes() > 0;
            }
            /** @var \DateTime $startDate */
            $startDate = $this->calendarHelper->convertDateToUTC($params['calendar_selector']['from'], $hasTimes, $params['calendar_selector']['locale']);
            /** @var \DateTime $endDate */
            $endDate = $this->calendarHelper->convertDateToUTC($params['calendar_selector']['to'], $hasTimes, $params['calendar_selector']['locale']);
        }
        if ($startDate !== null && $endDate !== null) {
            $fromDateInitial = $startDate->format('Y-m-d H:i:s');
            $toDateInitial = $endDate->format('Y-m-d H:i:s');
        } elseif ($isQuoteItem) {
            $dates = $this->calendarHelper->getDatesFromBuyRequest(
                $quoteItem->getOptionByCode('info_buyRequest'), $quoteItem->getProduct()
            );
            $fromDateInitial = $dates->getStartDate()->format('Y-m-d H:i:s');
            $toDateInitial = $dates->getEndDate()->format('Y-m-d H:i:s');
            /**
             * in admin update global dates all over.
             */
            $this->catalogSession->setStartDateGlobal($dates->getStartDate()->format('Y-m-d H:i:s'));
            $this->catalogSession->setEndDateGlobal($dates->getEndDate()->format('Y-m-d H:i:s'));
        } elseif ($this->calendarHelper->getGlobalDates('from')) {
            $fromDateInitial = $this->calendarHelper->getGlobalDates('from')->format('Y-m-d H:i:s');
            $toDateInitial = $this->calendarHelper->getGlobalDates('to')->format('Y-m-d H:i:s');
        }

        if (null !== $product) {
            $availableQuantity = $this->stockManagement->getSirentQuantity($product);
        } else {
            $availableQuantity = \SalesIgniter\Rental\Model\Product\Stock::OVERBOOK_QTY;
        }
        $firstDateAvailable = 0;
        $firstTimeAvailable = 0;
        if ($availableQuantity > 0) {
            $firstDateAvailable = $this->stockManagement->getFirstDateAvailable($product);
            $firstTimeAvailable = $this->stockManagement->getFirstTimeAvailable($product);
        }

        $inventory = $this->stockManagement->getInventoryTable($product);
        $inventoryFull = $this->stock->updateFullDatesBooking($inventory);
        //var_export($inventory);
        //var_export($inventoryFull);

        $responseContent = [
            'success' => true,
            'bookedDates' => $inventory,
            'bookedDatesFull' => $inventoryFull,
            'disabledDaysWeekStart' => $disabledDaysWeekStart,
            'disabledDaysWeekEnd' => $disabledDaysWeekEnd,
            'disabledDaysWeekTurnover' => $disabledDaysWeekTurnover,
            'disabledDates' => $disabledDates,
            'disabledDatesTurnover' => $disabledDatesTurnover,
            'disabledDatesFull' => $disabledDatesFull,
            'disabledDatesFullTurnover' => $disabledDatesFullTurnover,
            'turnoverBefore' => $turnoverBefore,
            'turnoverAfter' => $turnoverAfter,
            'minimumPeriod' => $minimumPeriod,
            'maximumPeriod' => $maximumPeriod,
            'firstDateAvailable' => $firstDateAvailable,
            'firstTimeAvailable' => $firstTimeAvailable,
            'fromDateInitial' => $fromDateInitial,
            'toDateInitial' => $toDateInitial,

            'availableQuantity' => $availableQuantity,
            'error_message' => __('Product does not exists'),
        ];
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
