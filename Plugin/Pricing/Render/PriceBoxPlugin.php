<?php
namespace SalesIgniter\Rental\Plugin\Pricing\Render;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class PriceBoxPlugin
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $dateTime;
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\Registry|Magento\GoogleOptimizer\Block\Code\Category\Interceptor|SalesIgniter\Rental\Helper\Calendar|SalesIgniter\Rental\Observer\LayoutProcess|SalesIgniter\Rental\Plugin\Pricing\Render\PriceBoxPlugin
     */
    private $coreRegistry;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
     * @param \Magento\Framework\Registry                          $coreRegistry
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\App\ScopeResolverInterface        $scopeResolver
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->_helperRental = $helperRental;
        $this->priceCalculations = $priceCalculations;
        $this->priceCurrency = $priceCurrency;
        $this->helperCalendar = $helperCalendar;
        $this->dateTime = $dateTime;
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
    }

    /**
     * @param PriceBox        $subject
     * @param \Closure        $proceed
     * @param AmountInterface $amount
     * @param array           $arguments
     *
     * This is for listings pages
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRenderAmount(
        PriceBox $subject,
        \Closure $proceed,
        AmountInterface $amount,
        array $arguments = []
    ) {
        $returnValue = $proceed($amount, $arguments);
        if ($subject->getSaleableItem()->getId() !== null && $this->_helperRental->isRentalType($subject->getSaleableItem()->getId())) {
            $productId = $subject->getSaleableItem()->getId();
            if ($this->_helperRental->isConfigurable($subject->getSaleableItem()->getId())) {
                $product = $this->productRepository->getById($subject->getSaleableItem()->getId());
                $usedProducts = $product->getTypeInstance()->getUsedProducts($product);

                foreach ($usedProducts as $iProduct) {
                    $productId = $iProduct->getId();
                    break;
                }
            }
            return $this->priceCalculations->getPriceListHtml($productId, false, $returnValue);
        } else {
            return $returnValue;
        }
    }

    /**
     * We modify the priceview for bundle products so if even price range is used
     * to not take that into consideration and still use as low as
     *
     * @param PriceBox $subject
     * @param \Closure $proceed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetSaleableItem(
        PriceBox $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        if ($result->getId() !== null && $this->_helperRental->isRentalType($result->getId())) {
            $result->setPriceView(1);
        }
        return $result;
    }

    /**
     * We don't want price range for bundle products
     *
     * @param PriceBox $subject
     * @param \Closure $proceed
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundShowRangePrice(
        PriceBox $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        if ($this->_helperRental->isRentalType($subject->getSaleableItem()->getId())) {
            return false;
        }
        return $result;
    }

    /**
     * We override this function because the cache needs to be redone for every changed dates
     * Very simple mechanism but without depersonalizeplugin it won't work. the session data is lost
     *
     * @param PriceBox $subject
     * @param string   $result
     *
     * @return string
     */
    public function afterGetCacheKey(PriceBox $subject, $result)
    {
        $datesValues = '';
        if ($this->helperCalendar->getGlobalDates('from')) {
            $datesValues .= $this->helperCalendar->getGlobalDates('from')->format('Y-m-d H:i:s');
            $datesValues .= '_' . $this->helperCalendar->getGlobalDates('to')->format('Y-m-d H:i:s');
        }
        //if ($datesValues !== '') {
        return implode(
            '-',
            [
                $result,
                $this->priceCurrency->getCurrencySymbol(),
                $this->dateTime->scopeDate($this->scopeResolver->getScope()->getId())->format('Ymd'),
                $this->scopeResolver->getScope()->getId(),
                $this->customerSession->getCustomerGroupId(),
                $subject->getSaleableItem()->getId(),
                $datesValues,
                ($this->coreRegistry->registry('current_product') ? 'is_product' : 'details') . $subject->getSaleableItem()->getId(),
            ]
        );
        //}
    }

    /**
     * Might need to implement aroundGetIdentities
     */
}
