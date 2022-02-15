<?php
namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\Returns;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     *
     */
    private $orderId;

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
        RequestInterface $request,
        Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        if ($this->request->getParam('order_id')) {
            $this->data['config']['update_url'] = sprintf(
                '%s%s/%s',
                $this->data['config']['update_url'],
                'order_id',
                $this->request->getParam('order_id')
            );
            $this->orderId = $this->request->getParam('order_id');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                ->filterByToReturn();
            if ($this->orderId !== null) {
                $this->getCollection()->filterByOrderId($this->orderId);
            }
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
            $itemAsArray['max_qty_to_return'] = $itemAsArray['qty_shipped'] - $itemAsArray['qty_returned'];

            $arrItems['items'][] = $itemAsArray;
        }

        return $arrItems;
    }
}
