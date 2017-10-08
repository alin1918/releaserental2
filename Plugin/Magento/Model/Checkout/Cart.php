<?php
namespace SalesIgniter\Rental\Plugin\Magento\Model\Checkout;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;

class Cart
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $rentalHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \SalesIgniter\Rental\Helper\Calendar                 $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Data                     $rentalHelper
     * @param \Magento\Framework\App\RequestInterface              $request
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Locale\ResolverInterface          $localeResolver
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Data $rentalHelper,
        RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ResolverInterface $localeResolver
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->calendarHelper = $calendarHelper;
        $this->localeDate = $localeDate;
        $this->rentalHelper = $rentalHelper;
        $this->request = $request;
    }

    /**
     * beforeAddProduct
     * This is used when adding product directly from listing.
     * It will check if there are global dates . and if the request does not contain a request from product page
     * After that it recreates the requestparams
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        if (!$this->request->getParam('calendar_selector') && $this->calendarHelper->getGlobalDates('from') && $this->rentalHelper->isRentalType($productInfo)) {
            $fromDateInitial = $this->calendarHelper->formatDateTime($this->calendarHelper->getGlobalDates('from'));
            $toDateInitial = $this->calendarHelper->formatDateTime($this->calendarHelper->getGlobalDates('to'));
            $customOptions = $productInfo->getOptions();
            $startOptionId = false;
            $endOptionId = false;
            if (is_array($customOptions)) {
                foreach ($customOptions as $option) {
                    if ($option->getTitle() == 'Start Date:') {
                        $startOptionId = $option->getId();
                    }
                    if ($option->getTitle() == 'End Date:') {
                        $endOptionId = $option->getId();
                    }
                }
            }
            if ($startOptionId !== false) {
                $requestInfo['options'][$startOptionId] = [
                    'month' => '',
                    'day' => '',
                    'year' => '',
                    'hour' => '',
                    'minute' => '',
                    'day_part' => 'am',

                ];
            }
            if ($endOptionId !== false) {
                $requestInfo['options'][$endOptionId] = [
                    'month' => '',
                    'day' => '',
                    'year' => '',
                    'hour' => '',
                    'minute' => '',
                    'day_part' => 'am',

                ];
            }

            $requestInfo['calendar_use_times'] = '1';

            $requestInfo['calendar_selector']['from'] = $fromDateInitial;
            $requestInfo['calendar_selector']['to'] = $toDateInitial;
            $requestInfo['calendar_selector']['locale'] = $this->localeResolver->getLocale();
        }

        return [$productInfo, $requestInfo];
    }
}
