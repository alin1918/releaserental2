<?php

namespace SalesIgniter\Rental\Plugin\Pricing;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\Pricing\SaleableInterface;

class RenderPlugin
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
     * @var \Magento\Framework\Pricing\Render\Layout
     */
    private $priceLayout;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
     * @param \Magento\Framework\Pricing\Render\Layout             $priceLayout
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Layout $priceLayout,
        ProductRepositoryInterface $productRepository,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
    ) {
        $this->_helperRental = $helperRental;
        $this->helperCalendar = $helperCalendar;
        $this->priceCalculations = $priceCalculations;
        $this->priceCurrency = $priceCurrency;
        $this->priceLayout = $priceLayout;
        $this->productRepository = $productRepository;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param \Magento\Framework\Pricing\Render               $subject
     * @param \Closure                                        $proceed
     * @param AmountInterface                                 $amount
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param \Magento\Framework\Pricing\SaleableInterface    $saleableItem
     * @param array                                           $arguments
     * This is mainly in product details page
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function aroundRenderAmount(
        \Magento\Framework\Pricing\Render $subject,
        \Closure $proceed,
        AmountInterface $amount,
        PriceInterface $price,
        SaleableInterface $saleableItem = null,
        array $arguments = []
    ) {
        $returnValue = $proceed($amount, $price, $saleableItem, $arguments);
        if ($saleableItem !== null && $this->_helperRental->isRentalType($saleableItem->getId()) && ($saleableItem->getParentProductId() || strpos($returnValue, 'final_price') !== false)) {
            $productId = $saleableItem->getId();
            if ($this->_helperRental->isConfigurable($saleableItem->getId())) {
                $product = $this->productRepository->getById($saleableItem->getId());
                $usedProducts = $product->getTypeInstance()->getUsedProducts($product);

                foreach ($usedProducts as $iProduct) {
                    $productId = $iProduct->getId();
                    break;
                }
            }
            if (!$saleableItem->getParentProductId() || $this->_helperRental->isPricePerProduct($saleableItem->getParentProductId())) {
                return $this->priceCalculations->getPriceListHtml($productId, false, $returnValue);
            }
        }

        return $returnValue;
    }
}
