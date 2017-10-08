<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Form\Source;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Input;

class ShippedData extends Input
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface                $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array                           $components
     * @param array                           $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $components, $data);
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
        $config['url_qty'] = $this->getUrlQty();
        $config['inputName'] = 'is_shipped';
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
