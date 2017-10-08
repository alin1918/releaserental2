<?php

namespace SalesIgniter\Rental\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;

class InputField extends AbstractRenderer
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Manufacturer constructor.
     * @param AttributeFactory $attributeFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        Context $context,
        array $data = array()
    )
    {
        $this->attributeFactory = $attributeFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        // Get default value:
        $value = "<input type='text' name='qty_shipped' data-action='qty-shipped' />";

        return $value;
    }
}
