<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Observer;

use Magento\Framework\Event\ObserverInterface;
//use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use SalesIgniter\Rental\Api\StockManagementInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * Validator observer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 */
class QuantityValidatorObserver implements ObserverInterface
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $helperRental;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar|IntlDateFormatter
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * @var array
     */
    protected $baseInventory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $quoteSessionFrontend;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $productStock;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                  $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar              $calendarHelper
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $productStock
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Framework\App\RequestInterface           $request
     * @param \Magento\Checkout\Model\Session                   $quoteSessionFrontend
     * @param \Magento\Framework\Message\ManagerInterface       $messageManager
     * @param \Magento\Catalog\Model\Session                    $catalogSession
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        StockManagementInterface $productStock,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $quoteSessionFrontend,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->helperRental = $helperRental;
        $this->calendarHelper = $calendarHelper;
        $this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->quoteSessionFrontend = $quoteSessionFrontend;
        $this->productStock = $productStock;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
    }

    /**
     * Removes error statuses from quote and item, set by this observer.
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int                             $code
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
        $quoteItems = $quote->getItemsCollection();
        $removeErrorFromQuote = true;

        foreach ($quoteItems as $quoteItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if ($quoteItem->getItemId() === $item->getItemId()) {
                continue;
            }

            $errorInfoArray = $quoteItem->getErrorInfos();
            foreach ($errorInfoArray as $errorInfo) {
                if ($errorInfo['code'] === $code) {
                    $removeErrorFromQuote = false;
                    break;
                }
            }

            if (!$removeErrorFromQuote) {
                break;
            }
        }

        if ($removeErrorFromQuote && $quote->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $quote->removeErrorInfosByParams(null, $params);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItemObj
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     */
    private function sameDatesEnforce($quoteItemObj)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteItemObj->getQuote();
        $quoteItems = $quote->getAllItems();
        $firstQuoteItem = false;
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }
            if ($quoteItem->getParentItem()) {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItem->getParentItem());
            } else {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItem);
            }
            $dates = $this->calendarHelper->getDatesFromBuyRequest(
                $buyRequest, $quoteItem->getProduct()
            );
            if (!$firstQuoteItem && $dates->getStartDate() && $dates->getEndDate()) {
                $startDate = $dates->getStartDate();
                $endDate = $dates->getEndDate();

                $counter = 0;
                foreach ($quoteItems as $quoteItemNew) {
                    if ($quoteItemNew->getParentItem()) {
                        continue;
                    }
                    if ($quoteItemNew->getParentItem()) {
                        $buyRequestNew = $this->calendarHelper->prepareBuyRequest($quoteItemNew->getParentItem());
                    } else {
                        $buyRequestNew = $this->calendarHelper->prepareBuyRequest($quoteItemNew);
                    }
                    $datesNew = $this->calendarHelper->getDatesFromBuyRequest(
                        $buyRequestNew, $quoteItemNew->getProduct()
                    );
                    if ($datesNew->getStartDate() && $datesNew->getEndDate()) {
                        if ($counter > 0) {
                            $startDateNew = $datesNew->getStartDate();
                            $endDateNew = $datesNew->getEndDate();

                            if ($startDate != $startDateNew || $endDate != $endDateNew) {
                                $this->addErrorByType($quoteItem, Stock::SAME_DATES_ENFORCE_ERROR);

                                return false;
                            }
                        }
                        ++$counter;
                    }
                }
            }
            break;
        }

        return true;
    }

    /**
     * This function is used to generate the updated inventory for the current quote item and see if there are any intersecting dates
     * Seems here the object checkout/model/session->getQuote is not available, actually seems that the quote object might not be available all over the cart
     * check more quoteitemrepository.
     *
     * @param $quoteItem
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     */
    private function generateBaseInventory($quoteItem)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteItem->getQuote();
        $quoteItems = $quote->getAllItems();

        $updatedInventory = $this->productStock->getInventoryTable($quoteItem->getProduct());
        $sirentFrontendId = false;
        if ($this->request->getParam('sirent_quote_id_frontend')) {
            $sirentFrontendId = $this->request->getParam('sirent_quote_id_frontend');
        }
        if ($this->registry->registry('sirent_quote_id_frontend')) {
            $sirentFrontendId = $this->registry->registry('sirent_quote_id_frontend');
        }
        if ($sirentFrontendId) {
            foreach ($quoteItems as $quoteItemObj) {
                if ($quoteItemObj->getId() !== $sirentFrontendId &&
                    $quoteItemObj->getParentItemId() !== $sirentFrontendId
                ) {
                    continue;
                }
                $productObj = $quoteItemObj->getProduct();
                if ($productObj->getId() !== $quoteItemObj->getProduct()->getId()) {
                    continue;
                }
                if ($quoteItemObj->getParentItem()) {
                    $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItemObj->getParentItem());
                } else {
                    $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItemObj);
                }
                $datesConfigure = $this->calendarHelper->getDatesFromBuyRequest(
                    $buyRequest, $productObj
                );
                $updatedInventory = $this->productStock->getUpdatedInventory(
                    $quoteItemObj->getProduct()->getId(),
                    $datesConfigure->getStartDateWithTurnover(),
                    $datesConfigure->getEndDateWithTurnover(),
                    0,
                    $quoteItemObj->getQty(),
                    0,
                    $updatedInventory
                );
            }
        }
        foreach ($quoteItems as $quoteItemObj) {
            if ($quoteItemObj->getParentItemId() === $quoteItem->getId() ||
                $quoteItemObj->getId() === $quoteItem->getId()
            ) {
                continue;
            }

            $productObj = $quoteItemObj->getProduct();
            if ($productObj->getId() !== $quoteItem->getProduct()->getId()) {
                continue;
            }
            if ($quoteItemObj->getParentItem()) {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItemObj->getParentItem());
            } else {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItemObj);
            }
            $dates = $this->calendarHelper->getDatesFromBuyRequest(
                $buyRequest, $productObj
            );

            if ($dates->getStartDate() && $dates->getEndDate()) {
                $updatedInventory = $this->productStock->getUpdatedInventory(
                    $productObj->getId(),
                    $dates->getStartDateWithTurnover(),
                    $dates->getEndDateWithTurnover(),
                    $quoteItemObj->getQty(),
                    0,
                    0,
                    $updatedInventory
                );
            }
        }

        return $updatedInventory;
    }

    /**
     * Validates the qty inventory.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \LogicException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();

        if (!$quoteItem ||
            !$quoteItem->getProductId() ||
            !$quoteItem->getQuote() ||
            ($this->request->getModuleName() === 'paypal' && $this->request->getActionName() === 'review') ||
            ($this->request->getModuleName() === 'paypal' && $this->request->getActionName() === 'placeOrder')

        ) {
            return $this;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $quoteItem->getProduct();

        if ($product->getTypeId() === Sirent::TYPE_RENTAL) {
            if (!$this->helperRental->isBackendAdminOrderEdit() && $this->calendarHelper->sameDatesEnforce() && !$this->sameDatesEnforce($quoteItem)) {
                return $this;
            }
            $qty = $quoteItem->getQty();
            if ($quoteItem->getParentItem()) {
                $qty *= $quoteItem->getParentItem()->getQty();
            }
            if ($quoteItem->getParentItem()) {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItem->getParentItem());
            } else {
                $buyRequest = $this->calendarHelper->prepareBuyRequest($quoteItem);
            }
            $dates = $this->calendarHelper->getDatesFromBuyRequest(
                $buyRequest, $quoteItem->getProduct()
            );

            if (!$dates->getIsBuyout()) {
                if (!$dates->getStartDate() || !$dates->getEndDate()) {
                    $this->addErrorByType($quoteItem, Stock::SELECT_START_END_DATES_ERROR);
                } else {
                    $errorType = $this->productStock->checkIntervalValid(
                        $product->getId(), $dates, $qty, $this->generateBaseInventory($quoteItem));

                    if ($errorType !== Stock::NO_ERROR && $errorType !== Stock::END_DATE_DISABLED_ERROR && $errorType !== Stock::START_DATE_DISABLED_ERROR) {
                        $this->addErrorByType($quoteItem, $errorType);
                    } elseif ($this->calendarHelper->keepSelectedDates()) {
                        $this->catalogSession->setStartDateGlobal($dates->getStartDate()->format('Y-m-d H:i:s'));
                        $this->catalogSession->setEndDateGlobal($dates->getEndDate()->format('Y-m-d H:i:s'));
                    }
                }
            } else {
                $currentQty = $this->helperRental->getAttribute($product, 'sirent_quantity');
                $today = $this->calendarHelper->getTimeAccordingToTimeZone();
                $todayDate = new \DateTime($today->format('Y-m-d H:i:s'));
                $yearsUpFront2 = $todayDate->add(new \DateInterval('P2Y'));
                $availableQuantity = $this->productStock->getAvailableQuantity($product, $todayDate, $yearsUpFront2);
                if ($currentQty < $qty || $availableQuantity < $qty) {
                    $this->addErrorByType($quoteItem, Stock::NOT_ENOUGH_QUANTITY_ERROR_BUYOUT);
                }
            }
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param                                 $errorType
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addErrorByType($quoteItem, $errorType)
    {
        $this->_removeErrorsFromQuoteAndItem($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY);

        switch ($errorType) {
            case Stock::START_DATE_DISABLED_ERROR:
                $errorMessage = __('Start Date is disabled for the selected Dates');
                break;
            case Stock::END_DATE_DISABLED_ERROR:
                $errorMessage = __('End Date is disabled for the selected Dates');
                break;
            case Stock::START_DATE_DISABLED_ERROR_FULL:
                $errorMessage = __('Start Date is disabled for the selected Dates');
                break;
            case Stock::END_DATE_DISABLED_ERROR_FULL:
                $errorMessage = __('End Date is disabled for the selected Dates');
                break;
            case Stock::BOOKED_DATES_ERROR:
                $errorMessage = __('Not enough inventory for the selected dates');
                break;
            case Stock::NOT_ENOUGH_QUANTITY_ERROR:
                $errorMessage = __('There is not enough quantity for the selected dates');
                break;
            case Stock::NOT_ENOUGH_QUANTITY_ERROR_BUYOUT:
                $errorMessage = __('There is not enough quantity for buyout');
                break;
            case Stock::SELECT_START_END_DATES_ERROR:
                $errorMessage = __('Please select start and end date first');
                break;
            case Stock::SAME_DATES_ENFORCE_ERROR:
                $errorMessage = __('You must have same dates for all products in cart');
                break;
            case Stock::DISABLED_DATES_ERROR:
                $errorMessage = __('Selected Dates are disabled!');
                break;
            case Stock::MAXIMUM_PERIOD_ERROR:
                $errorMessage = __('Selected dates should be in the maximum period allowed!');
                break;
            case Stock::MINIMUM_PERIOD_ERROR:
                $errorMessage = __('Selected dates should be in the minimum period allowed!');
                break;
            default:
                $errorMessage = '';
                break;
        }
        if ($errorMessage !== '') {
            if (($errorType === Stock::NOT_ENOUGH_QUANTITY_ERROR ||
                    $errorType === Stock::BOOKED_DATES_ERROR) &&
                $this->helperRental->isBackendAdminOrderEdit() &&
                !$this->calendarHelper->allowOverbookingAdmin()
            ) {
                //throw new \Magento\Framework\Exception\LocalizedException(
                //  $errorMessage
                //);
                $quoteItem->addErrorInfo(
                    'sirent_inventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $errorMessage
                );

                $quoteItem->getQuote()->addErrorInfo(
                    'sirent_qty',
                    'sirent_inventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $errorMessage
                );
            }
            if ($this->helperRental->isFrontend()) {
                $quoteItem->addErrorInfo(
                    'sirent_inventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $errorMessage
                );

                $quoteItem->getQuote()->addErrorInfo(
                    'sirent_qty',
                    'sirent_inventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $errorMessage
                );
            } elseif ($this->helperRental->isBackend() && $this->calendarHelper->allowOverbookingShowWarningAdmin() && $this->calendarHelper->allowOverbookingAdmin()) {
                $quoteItem->addMessage(
                    $errorMessage
                );

                $quoteItem->getQuote()->addMessage(
                    $errorMessage
                );
            }
            if ($errorType === Stock::SELECT_START_END_DATES_ERROR) {
                //$this->messageManager->addErrorMessage($errorMessage);
                $quoteItem->addErrorInfo(
                    'sirent_inventory_1',
                    3,
                    $errorMessage
                );

                $quoteItem->getQuote()->addErrorInfo(
                    'sirent_qty',
                    'sirent_inventory_1',
                    3,
                    $errorMessage
                );
                //  throw new \Magento\Framework\Exception\LocalizedException(
                //     $errorMessage
                // );
            }
        }
    }
}
