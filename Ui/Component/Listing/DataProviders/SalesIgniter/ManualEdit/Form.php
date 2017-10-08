<?php

namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\ManualEdit;

class Form extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     *
     */
    private $resOrderId;

    /**
     * Grid constructor.
     *
     * @param string                                                                       $name
     * @param string                                                                       $primaryFieldName
     * @param string                                                                       $requestFieldName
     * @param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface                                      $request
     * @param array                                                                        $meta
     * @param array                                                                        $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection();
            $this->getCollection()->load();
        }

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $itemAsArray = $item->toArray([]);
            //$itemAsArray['id_field_name'] = 'reservationorder_id';
            $itemAsArray['is_shipped'] = 0;
            $itemAsArray['reservation_id'] = $itemAsArray['reservationorder_id'];
            if ($itemAsArray['qty_shipped'] > 0) {
                $itemAsArray['is_shipped'] = 1;
            }
            $arrItems[$itemAsArray['reservationorder_id']] = $itemAsArray;
        }

        return $arrItems;
    }
}
