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
    
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if($filter->getField() == 'order_id') {
            $filter->setField('increment_id');
        }        
        
        if($filter->getField() == 'name') {
            $expression = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DB\Sql\ColumnValueExpressionFactory')->create([
                'expression' => "IF(at_name.value_id > 0, at_name.value, at_name_default.value) {$filter->getConditionType()} '{$filter->getValue()}'",
            ]);
            $this->getCollection()->getSelect()->where($expression);
            return $this;
        }        
        
        parent::addFilter($filter);
    }    
}
