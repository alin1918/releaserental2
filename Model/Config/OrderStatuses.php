<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Config;

/**
 * AdminNotification update frequency source
 *
 * @codeCoverageIgnore
 */
class OrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory|SalesIgniter\Rental\Helper\Data|SalesIgniter\Rental\Plugin\Catalog\Template
     */
    private $orderStatusCollectionFactory;

    /**
     * OrderStatuses constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
    ) {
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->orderStatusCollectionFactory->create()->toOptionArray();
        foreach ($statuses as $index => $data) {
            if ($data['value'] == 'Completed') {
                unset($statuses[$index]);
            }
        }
        $notUsed = [
            ['value' => 'noInvoice', 'label' => __('With No Invoice (Recommended)')],
            ['value' => 'withInvoice', 'label' => __('When Invoiced (Payment Received status "Processing")')]
        ];
        return array_merge($notUsed, $statuses);
    }
}
