<?php
namespace SalesIgniter\Rental\Block\Footer;

class Pricingppr extends \Magento\Framework\View\Element\Template
{
    protected $_template = "SalesIgniter_Rental::footer/pricingppr.phtml";

    public function getPricingUpdateUrl()
    {
        return $this->getUrl(
            'salesigniter_rental/ajax/updatelistingprices',
            [
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }
}
