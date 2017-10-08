<?php

/** Deprecated, used table join on grid class instead */

namespace SalesIgniter\Rental\Block\Adminhtml\Renderer;

class ProductName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $productFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->productFactory = $productFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->productFactory->create()->load($row->getId());

        if ($product && $product->getId()) {
            return $product->getName();
        }

        return '';
    }
}
