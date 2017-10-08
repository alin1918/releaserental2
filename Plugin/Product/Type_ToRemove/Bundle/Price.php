<?php
namespace SalesIgniter\Rental\Model\Plugin\Product\Type\Bundle;

class Price
{
    
    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental
    ) {
        $this->_helperRental = $helperRental;
    }
    /**
     * bundle selection price get value
     *
     * @return null
     */
    public function aroundGetValue(
        \Magento\Bundle\Pricing\Price\BundleSelectionPrice $subject,
        \Closure $proceed
    ) {
        $product = $subject->getProduct();

        $proceed();
    }
}
