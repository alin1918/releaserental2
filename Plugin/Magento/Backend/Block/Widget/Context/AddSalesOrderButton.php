<?php

namespace SalesIgniter\Rental\Plugin\Magento\Backend\Block\Widget\Context;

class AddSalesOrderButton
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    ) {
        if ($subject->getRequest()->getFullActionName() === 'sales_order_view') {
            $buttonList->add(
                'return_order',
                [
                    'label' => __('Return'),
                    'class' => 'returnItemsButton',
                ]
            );
        }

        return $buttonList;
    }
}
