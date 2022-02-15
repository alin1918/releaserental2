<?php

namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\Send;

class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                ->filterByNoShip()
                ->filterByNotManual();
            $this->getCollection()->load();
        }

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $itemAsArray = $item->toArray([]);
            if (!isset($itemAsArray['qty_returned'])) {
                $itemAsArray['qty_returned'] = 0;
            }
            if (!isset($itemAsArray['qty_shipped'])) {
                $itemAsArray['qty_shipped'] = 0;
            }
            $itemAsArray['max_qty_to_ship'] = $itemAsArray['qty'] - $itemAsArray['qty_cancel'] - $itemAsArray['qty_shipped'];
            $arrItems['items'][] = $itemAsArray;
        }

        return $arrItems;
    }
}
