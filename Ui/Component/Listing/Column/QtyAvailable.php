<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class QtyAvailable extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.qty_available';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \SalesIgniter\Rental\Model\Product\Stock
     */
    private $helperStock;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * @param ContextInterface                                  $context
     * @param UiComponentFactory                                $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface       $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \SalesIgniter\Rental\Model\Product\Stock          $helperStock
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param \SalesIgniter\Rental\Helper\Data                  $helperRental
     * @param array                                             $components
     * @param array                                             $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Model\Product\Stock $helperStock,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->helperStock = $helperStock;
        $this->helperRental = $helperRental;
        $this->stockManagement = $stockManagement;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        $startDateArr = $this->context->getFilterParam('start_date');
        $startDate = $startDateArr['from'];
        $endDate = $startDateArr['to'];

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->helperRental->isRentalType($item['entity_id'])) {
                    $item[$fieldName] = $this->stockManagement->getAvailableQuantity($item['entity_id'], $startDate, $endDate);
                }
            }
        }

        return $dataSource;
    }
}
