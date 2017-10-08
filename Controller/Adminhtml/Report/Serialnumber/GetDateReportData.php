<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Report\Serialnumber;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Helper\Report as ReportHelper;
use SalesIgniter\Rental\Model\SerialNumberDetailsFactory as SerialFactory;

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
     * @var ReportHelper
     */
    protected $_reportHelper;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var SerialFactory
     */
    protected $_serialFactory;
    /**
     * @var \SalesIgniter\Rental\Block\Adminhtml\Report\SerialNumber\Modal\Date
     */
    private $block;

    /**
     * GetDateReportData constructor.
     *
     * @param Context                                                             $context
     * @param JsonFactory                                                         $resultJsonFactory
     * @param DataObjectFactory                                                   $ObjectFactory
     * @param DataCollection                                                      $Collection
     * @param ProductFactory                                                      $ProductFactory
     * @param SerialFactory                                                       $SerialFactory
     * @param OrderFactory                                                        $OrderFactory
     * @param ReportHelper                                                        $ReportHelper
     * @param \SalesIgniter\Rental\Block\Adminhtml\Report\SerialNumber\Modal\Date $block
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        DataObjectFactory $ObjectFactory,
        DataCollection $Collection,
        ProductFactory $ProductFactory,
        SerialFactory $SerialFactory,
        OrderFactory $OrderFactory,
        ReportHelper $ReportHelper,
        \SalesIgniter\Rental\Block\Adminhtml\Report\SerialNumber\Modal\Date $block
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_collection = $Collection;
        $this->_dataObjectFactory = $ObjectFactory;
        $this->_productFactory = $ProductFactory;
        $this->_serialFactory = $SerialFactory;
        $this->_orderFactory = $OrderFactory;
        $this->_reportHelper = $ReportHelper;
        $this->block = $block;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $RequestParams = $this->getRequest()->getParams();
        $ResultJson = $this->_resultJsonFactory->create();

        $Serial = $this->_serialFactory
            ->create()
            ->load($RequestParams['serial'], 'serialnumber');

        if (isset($RequestParams['reservation_order_id'])) {
            $Reservation = $this->_reportHelper
                ->getRentalOrder($RequestParams['reservation_order_id']);

            /** @var \SalesIgniter\Rental\Block\Adminhtml\Report\SerialNumber\Modal\Date $this ->block */

            $this->block->setNameInLayout('report.modal.content');
            $this->block->setReservationOrder($Reservation);

            $ResultJson->setData([
                'success' => true,
                'title' => __('Serial Number') . ': ' . $Serial->getSerialnumber(),
                'html' => $this->block->toHtml()
            ]);
        } else {
            $ResultJson->setData([
                'success' => true,
                'title' => __('Serial Number') . ': ' . $Serial->getSerialnumber(),
                'html' => __('No Reservation Orders For This Date.')
            ]);
        }

        return $ResultJson;
    }
}
