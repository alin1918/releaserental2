<?php

namespace SalesIgniter\Rental\Plugin\AdditionalFields;

use Magento\Framework\App\ResourceConnection;

class Provider
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * CollectionFactory constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource

    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Closure                                                                   $proceed
     * @param                                                                            $requestName
     *
     * @return mixed
     */
    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        //todo seems better to just record start end dates into grid table
        //this solution is good for more complicated cases
        $result = $proceed($requestName);
        if ($requestName === 'sales_order_grid_data_source' &&
            $result instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
        ) {
            $result->getSelect()->joinLeft(
                ['reservationorders_table' => $this->resource->getTableName('sirental_reservationorders')],
                'main_table.entity_id = reservationorders_table.order_id'
            );

            $result->getSelect()->group('main_table.entity_id');
        }

        if ($requestName === 'sales_order_invoice_grid_data_source' &&
            $result instanceof \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection
        ) {
            $result->getSelect()->joinLeft(
                ['reservationorders_table' => $this->resource->getTableName('sirental_reservationorders')],
                'main_table.order_id = reservationorders_table.order_id'
            );
            $result->getSelect()->group('main_table.entity_id');
        }

        if ($requestName === 'sales_order_creditmemo_grid_data_source' &&
            $result instanceof \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
        ) {
            $result->getSelect()->joinLeft(
                ['reservationorders_table' => $this->resource->getTableName('sirental_reservationorders')],
                'main_table.order_id = reservationorders_table.order_id'
            );
            $result->getSelect()->group('main_table.entity_id');
        }

        return $result;
    }
}
