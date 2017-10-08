<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Report\Inventory;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Helper\Report as ReportHelper;

class GetDateReportData extends BackendAction
{

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var DataCollection
     */
    protected $_collection;

    /**
     * @var DataObjectFactory
     */
    protected $_dataObjectFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var ReportHelper
     */
    protected $_reportHelper;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;
    /**
     * @var \SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable
     */
    private $gridBlock;

    /**
     * GetProductDateReportData constructor.
     *
     * @param Context                                               $context
     * @param JsonFactory                                           $resultJsonFactory
     * @param DataObjectFactory                                     $ObjectFactory
     * @param DataCollection                                        $Collection
     * @param ResourceConnection                                    $ResourceConnection
     * @param ProductFactory                                        $ProductFactory
     * @param \Magento\Sales\Model\OrderFactory                     $OrderFactory
     * @param \SalesIgniter\Rental\Helper\Report                    $ReportHelper
     * @param \SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable $gridBlock
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        DataObjectFactory $ObjectFactory,
        DataCollection $Collection,
        ResourceConnection $ResourceConnection,
        ProductFactory $ProductFactory,
        OrderFactory $OrderFactory,
        ReportHelper $ReportHelper,
        \SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable $gridBlock
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_collection = $Collection;
        $this->_dataObjectFactory = $ObjectFactory;
        $this->_resourceConnection = $ResourceConnection;
        $this->_productFactory = $ProductFactory;
        $this->_orderFactory = $OrderFactory;
        $this->_reportHelper = $ReportHelper;
        $this->gridBlock = $gridBlock;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @throws \DomainException
     */
    public function execute()
    {
        $RequestParams = $this->getRequest()->getParams();
        $ResultJson = $this->_resultJsonFactory->create();

        $Product = $this->_productFactory
            ->create()
            ->load($RequestParams['product']);

        $Connection = $this->_resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $FromDate = $RequestParams['dateFrom'];
        $ToDate = $RequestParams['dateTo'];

        $Reservations = $this->_reportHelper->getRentalOrders([
            'use_turnover_date' => true,
            'start_date' => $FromDate,
            'end_date' => $ToDate,
            'conditions' => [
                'product_id' => ['eq' => $Product->getId()]
            ]
        ]);

        foreach ($Reservations as $Reservation) {
            $Order = $this->_orderFactory->create()->load($Reservation['order_id']);

            $DataRow = $this->_dataObjectFactory->create();
            $DataRow->setData($Reservation);
            $DataRow->setOrderIncrementId($Order->getIncrementId());
            $DataRow->setCustomerId($Order->getCustomerId());
            $DataRow->setCustomerName($Order->getCustomerName());

            $this->_collection->addItem($DataRow);
        }

        $this->gridBlock->setCollection($this->_collection);
        $this->gridBlock->setGridUrl($this->getUrl('*/*/*', ['_current' => true]));

        $this->gridBlock->addColumn('order_increment_id', [
            'header' => __('Order Number'),
            'index' => 'order_increment_id',
            'renderer' => 'SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer\Buildhtml',
            'renderConfig' => [
                'template' => '<a href="' . $this->getUrl('sales/order/view', ['order_id' => '{{order_id}}']) . '" target="_blank">#{{order_increment_id}}</a>'
            ]
        ]);

        $this->gridBlock->addColumn('customer_name', [
            'header' => __('Customer'),
            'index' => 'customer_name',
            'renderer' => 'SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer\Buildhtml',
            'renderConfig' => [
                'template' => '<a href="' . $this->getUrl('customer/index/edit', ['id' => '{{customer_id}}']) . '" target="_blank">{{customer_name}}</a>'
            ]
        ]);

        $this->gridBlock->addColumn('start_date_with_turnover', [
            'header' => __('Start Date W/Turnover'),
            'index' => 'start_date_with_turnover',
            'timezone' => false,
            'gmtoffset' => false,
            'type' => 'datetime'
        ]);

        $this->gridBlock->addColumn('start_date', [
            'header' => __('Start Date'),
            'index' => 'start_date',
            'timezone' => false,
            'gmtoffset' => false,
            'type' => 'datetime'
        ]);

        $this->gridBlock->addColumn('end_date', [
            'header' => __('End Date'),
            'index' => 'end_date',
            'timezone' => false,
            'gmtoffset' => false,
            'type' => 'datetime'
        ]);

        $this->gridBlock->addColumn('end_date_with_turnover', [
            'header' => __('End Date W/Turnover'),
            'index' => 'end_date_with_turnover',
            'timezone' => false,
            'gmtoffset' => false,
            'type' => 'datetime'
        ]);

        $this->gridBlock->addColumn('inventory', [
            'header' => __('Inventory'),
            'index' => 'qty',
            'renderer' => 'SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer\Buildhtml',
            'renderConfig' => [
                'template' => '<ul style="list-style:none;margin:0;padding:0;">
					<li>
						<div style="min-width: 65px;width:75%;float:left;">Reserved: </div>
						<div style="width:25%;float:left;">{{qty}}</div>
					</li>
					<li>
						<div style="min-width: 65px;width:75%;float:left;">Canceled: </div>
						<div style="width:25%;float:left;">{{qty_cancel}}</div>
					</li>
					<li>
						<div style="min-width: 65px;width:75%;float:left;">Shipped: </div>
						<div style="width:25%;float:left;">{{qty_shipped}}</div>
					</li>
					<li>
						<div style="min-width: 65px;width:75%;float:left;">Returned: </div>
						<div style="width:25%;float:left;">{{qty_returned}}</div>
					</li>
				</ul><div style="clear:both;display:table;"></div>'
            ]
        ]);

        $ResultJson->setData([
            'success' => true,
            'title' => $Product->getName(),
            'html' => $this->gridBlock->getGridBlock()->toHtml()
        ]);

        return $ResultJson;
    }
}
