<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Form\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Select;

class ProductSearchUrl extends Select
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface                 $context
     * @param \Magento\Framework\UrlInterface  $urlBuilder
     * @param array|OptionSourceInterface|null $options
     * @param array                            $components
     * @param array                            $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $options, $components, $data);
    }

    public function getUrl()
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/suggestproducts'
        );
    }

    public function getUrlQty()
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/availableqty'
        );
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['url'] = $this->getUrl();
        $config['url_qty'] = $this->getUrlQty();
        $config['input_name'] = 'product_id';
        //$this->getValue()
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
