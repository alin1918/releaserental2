<?php
namespace SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ReturnGrid extends \Magento\Framework\View\Element\Template
{
    //protected $_template = "SalesIgniter_Rental::sales/order/view/pickup_form.phtml";
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $context->getUrlBuilder();
        $this->jsonEncoder = $jsonEncoder;
        $this->registry = $registry;
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getOrder()
    {
        if (!$this->hasData('order')) {
            $this->setData('order', $this->registry->registry('current_order'));
        }
        return $this->getData('order');
    }
}
