<?php
namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\ManualEdit;

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
            $this->getCollection()->filterByMain();

            $this->getCollection()->load();
        }

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $itemAsArray = $item->toArray([]);
            $arrItems['items'][] = $itemAsArray;
        }

        return $arrItems;
    }
}
