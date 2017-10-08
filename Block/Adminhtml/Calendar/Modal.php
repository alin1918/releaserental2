<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Calendar;

use Magento\Backend\Block\Template as BlockTemplate;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Helper\Report as ReportHelper;
use SalesIgniter\Rental\Model\ReservationOrdersFactory;

class Modal extends BlockTemplate
{

    /**
     * @var ReservationOrdersFactory
     */
    protected $_order;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_reservationOrder;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_orderFactory;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_reservationOrdersFactory;

    /**
     * @var ReportHelper
     */
    protected $_reportHelper;

    /**
     * Modal constructor.
     *
     * @param Context                  $context
     * @param OrderFactory             $OrderFactory
     * @param ReservationOrdersFactory $reservationOrdersFactory
     * @param ReportHelper             $ReportHelper
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        OrderFactory $OrderFactory,
        ReservationOrdersFactory $reservationOrdersFactory,
        ReportHelper $ReportHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_orderFactory = $OrderFactory;
        $this->_reservationOrdersFactory = $reservationOrdersFactory;
        $this->_reportHelper = $ReportHelper;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setReservationOrderId($id)
    {
        $this->_reservationOrder = $this->_reservationOrdersFactory
            ->create()
            ->load($id);

        $this->_order = $this->_orderFactory
            ->create()
            ->load($this->_reservationOrder->getOrderId());

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return SalesIgniter\Rental\Model\ReservationOrders
     */
    public function getReservationOrder()
    {
        return $this->_reservationOrder;
    }

    /**
     * @return \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\Collection
     * @throws \DomainException
     */
    public function getReservationOrders()
    {
        $IdArray = explode('_', $this->getRequest()->getParam('id'));
        $withTurnover = true;//
        $ReservationOrders = $this->_reportHelper->getRentalOrders([
            'use_turnover_date' => $withTurnover,
            'include_order_data' => true,
            'return_collection' => true,
            'start_date' => $IdArray[1] . ' 00:00:00',
            'end_date' => $IdArray[1] . ' 23:59:59'
        ]);

        return $ReservationOrders;
    }

    protected function _beforeToHtml()
    {
        $this->getLayout()->addBlock($this);

        if ($this->getRequest()->getParam('renderer') == 'bydate') {
            $OrdersBlock = $this->getLayout()
                ->createBlock('SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal\Orders');
            $OrdersBlock->setTemplate('SalesIgniter_Rental::calendar/modal/orders.phtml');

            $this->append($OrdersBlock);
        } else {
            $RentalInfoBlock = $this->getLayout()
                ->createBlock('SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal\RentalInfo');
            $RentalInfoBlock->setTemplate('SalesIgniter_Rental::calendar/modal/rentalinfo.phtml');

            $ProductsBlock = $this->getLayout()
                ->createBlock('SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal\Products');
            $ProductsBlock->setTemplate('SalesIgniter_Rental::calendar/modal/products.phtml');

            $this->append($RentalInfoBlock);
            $this->append($ProductsBlock);
        }

        return parent::_beforeToHtml();
    }
}
