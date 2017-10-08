<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SerialReturn extends Column
{
    private $_userFactory;

    protected $_productFactory;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Ui\Component\Listing\Column\UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface                                   $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\User\Model\UserFactory                    $userFactory
     * @param \SalesIgniter\Rental\Helper\Data                   $helperRental
     * @param \Magento\Catalog\Model\ProductFactory              $productFactory
     * @param \Magento\Framework\UrlInterface                    $urlBuilder
     * @param array                                              $components
     * @param array                                              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_userFactory = $userFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helperRental = $helperRental;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $productId
     *
     * @return mixed
     */
    private function getSuggestUrl($productId, $resOrderId)
    {
        return $this->urlBuilder->getUrl(
            'salesigniter_rental/ajax/suggestserialsout',
            [
                'product_id' => $productId,
                'res_order' => $resOrderId
            ]
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        /** @var array $dataSource */
        if (isset($dataSource['data']['items'])) {
            /** @var string $fieldName */
            $fieldName = $this->getData('name');
            /** @var array $item */
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = '';
                /**
                 * First check if product uses serials, if not leave this column blank
                 */

                $useSerialNumbers = $this->helperRental->isSerialEnabledForProduct($item['product_id']);
                $item[$fieldName . '_resorderid'] = $item['reservationorder_id'];
                $item[$fieldName . '_productid'] = $item['product_id'];
                $item[$fieldName . '_source'] = $this->getSuggestUrl($item['product_id'], $item['reservationorder_id']);
                $item[$fieldName . '_qty'] = $item['qty'];
                $item[$fieldName . '_use_serials'] = '0';
                if (!$useSerialNumbers) {
                    continue;
                } else {
                    $item[$fieldName . '_use_serials'] = '1';
                }
            }

            return $dataSource;
        }
    }
}
