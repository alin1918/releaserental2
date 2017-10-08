<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Calendar;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Helper\Report as ReportHelper;
use SalesIgniter\Rental\Model\ReservationOrdersFactory;

class GetRentals extends BackendAction
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
     * @var ReservationOrdersFactory
     */
    protected $_orderFactory;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_reservationOrdersFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var ReportHelper
     */
    protected $_reportHelper;

    /**
     * getRentals constructor.
     *
     * @param Context                  $context
     * @param JsonFactory              $resultJsonFactory
     * @param ResourceConnection       $ResourceConnection
     * @param DataCollection           $Collection
     * @param DataObjectFactory        $ObjectFactory
     * @param OrderFactory             $OrderFactory
     * @param ReservationOrdersFactory $reservationOrdersFactory
     * @param ReportHelper             $ReportHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $ResourceConnection,
        DataCollection $Collection,
        DataObjectFactory $ObjectFactory,
        OrderFactory $OrderFactory,
        ReservationOrdersFactory $reservationOrdersFactory,
        ReportHelper $ReportHelper
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_collection = $Collection;
        $this->_dataObjectFactory = $ObjectFactory;
        $this->_resourceConnection = $ResourceConnection;
        $this->_orderFactory = $OrderFactory;
        $this->_reservationOrdersFactory = $reservationOrdersFactory;
        $this->_reportHelper = $ReportHelper;
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

        $EventRenderType = $this->getRequest()->getParam('renderer', 'byorder');

        $WithTurnover = true;

        $Reservations = $this->_reportHelper->getRentalOrders([
            'use_turnover_date' => $WithTurnover,
            'start_date' => $RequestParams['start'],
            'end_date' => $RequestParams['end']
        ]);

        $Events = [];
        foreach ($Reservations as $Reservation) {
            $Order = $this->_orderFactory->create()->load($Reservation['order_id']);

            $StartDate = new \DateTime($Reservation['start_date' . ($WithTurnover ? '_with_turnover' : '')]);
            $EndDate = new \DateTime($Reservation['end_date' . ($WithTurnover ? '_with_turnover' : '')]);

            if ($EventRenderType == 'bydate') {
                if (isset($Events['start'][$StartDate->format('Y-m-d')]) === false) {
                    $Events['start'][$StartDate->format('Y-m-d')] = [
                        'id' => 'start_' . $StartDate->format('Y-m-d'),
                        'title' => 'Rentals Beginning',
                        'allDay' => true,
                        'start' => $StartDate->format(\DateTime::ISO8601),
                        'end' => $StartDate->format(\DateTime::ISO8601),
                        'url' => '',
                        'className' => '',
                    ];
                }

                if (isset($Events['end'][$EndDate->format('Y-m-d')]) === false) {
                    $Events['end'][$EndDate->format('Y-m-d')] = [
                        'id' => 'end_' . $EndDate->format('Y-m-d'),
                        'title' => 'Rentals Ending',
                        'allDay' => true,
                        'start' => $EndDate->format(\DateTime::ISO8601),
                        'end' => $EndDate->format(\DateTime::ISO8601),
                        'url' => '',
                        'className' => '',
                    ];
                }
            } else {
                $Events[] = [
                    'id' => $Reservation['reservationorder_id'],
                    'title' => 'Order #' . $Order->getIncrementId() . ' ' . $Order->getCustomerName(),
                    'allDay' => false,
                    'start' => $StartDate->format(\DateTime::ISO8601),
                    'end' => $EndDate->format(\DateTime::ISO8601),
                    'url' => '',
                    'className' => '',
                ];
            }
        }

        if ($EventRenderType == 'bydate') {
            $RealEvents = [];
            foreach ($Events['start'] as $Date => $EventData) {
                $RealEvents[] = $EventData;
            }
            foreach ($Events['end'] as $Date => $EventData) {
                $RealEvents[] = $EventData;
            }

            $Events = $RealEvents;
        }

        $ResultJson->setData($Events);

        return $ResultJson;
    }
}
