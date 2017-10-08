<?php
namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\Send;

class History extends \Magento\Ui\DataProvider\AbstractDataProvider
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
                ->filterByShippedOrReturned();
            $this->getCollection()->load();
        }

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $itemAsArray = $item->toArray([]);
            if ($itemAsArray['qty_returned'] === null) {
                $itemAsArray['qty_returned'] = 0;
            }
            if ($itemAsArray['qty_shipped'] === null) {
                $itemAsArray['qty_shipped'] = 0;
            }

            $arrItems['items'][] = $itemAsArray;
        }

        return $arrItems;
    }
}
