<?php
namespace SalesIgniter\Rental\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \SalesIgniter\Rental\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $extensionVersion = $this->_helper->getExtensionVersion();
        $element->setValue($extensionVersion);
        return $element->getValue();
    }
}