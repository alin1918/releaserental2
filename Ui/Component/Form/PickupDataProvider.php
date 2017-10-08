<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\CollectionFactory;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PickupDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var SessionManagerInterface
     */
    protected $session;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Constructor
     *
     * @param string                                  $name
     * @param string                                  $primaryFieldName
     * @param string                                  $requestFieldName
     * @param CollectionFactory                       $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $meta
     * @param array                                   $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->request->setParam('id', 1);
        $this->registry = $registry;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $arrItems = [
            'totalRecords' => 1,
            'items' => [],
        ];

        /** @var array $dataArr */
        $dataArr = [];
        if ($this->registry->registry('current_order')) {
            $order = $this->registry->registry('current_order');
            $dataArr['order_id'] = $order->getId();
            $dataArr['date_from'] = $order->getPickupDate();
            $dataArr['date_to'] = $order->getDropoffDate();
            $dataArr['update_pickup'] = '1';
            $idField = $this->request->getParam('id');
            $arrItems[$idField] = $dataArr;
        }
        return $arrItems;
    }
}
