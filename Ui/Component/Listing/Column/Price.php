<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \SalesIgniter\Rental\Model\ResourceModel\Price
     */
    private $productRentalPrice;
    /**
     * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
     */
    private $priceCalculations;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;

    /**
     * @param ContextInterface                                     $context
     * @param UiComponentFactory                                   $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface          $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \SalesIgniter\Rental\Model\ResourceModel\Price       $productRentalPrice
     * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
     * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
     * @param array                                                $components
     * @param array                                                $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Model\ResourceModel\Price $productRentalPrice,
        \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->productRentalPrice = $productRentalPrice;
        $this->priceCalculations = $priceCalculations;
        $this->helperRental = $helperRental;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     * @throws \Zend_Currency_Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     * @throws \Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );

            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->helperRental->isBundle($item['entity_id']) && !$this->helperRental->isPricePerProduct($item['entity_id'])) {
                    $priceHtml = $this->priceCalculations->getPriceListHtml($item['entity_id'], true);
                    $item[$fieldName] = $priceHtml;
                } elseif ($this->helperRental->isRentalType($item['entity_id'])) {
                    $priceHtml = $this->priceCalculations->getPriceListHtml($item['entity_id'], true);
                    $item[$fieldName] = $priceHtml;
                } elseif (array_key_exists($fieldName, $item)) {
                    $item[$fieldName] = $currency->toCurrency(sprintf('%f', $item[$fieldName]));
                }
            }
        }

        return $dataSource;
    }
}
