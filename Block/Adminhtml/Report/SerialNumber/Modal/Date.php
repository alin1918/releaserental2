<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Report\SerialNumber\Modal;

use Magento\Backend\Block\Template as BlockTemplate;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable;
use SalesIgniter\Rental\Helper\Report as ReportHelper;

class Date extends BlockTemplate
{

    /**
     * @var ReportHelper
     */
    protected $_reportHelper;

    /**
     * @var \SalesIgniter\Rental\Model\ReservationOrders
     */
    protected $_reservationOrder;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * Date constructor.
     *
     * @param Context         $Context
     * @param AddressRenderer $AddressRenderer
     * @param ReportHelper    $ReportHelper
     * @param DataTable       $DataTableBlock
     * @param array           $data
     */
    public function __construct(
        Context $Context,
        AddressRenderer $AddressRenderer,
        ReportHelper $ReportHelper,
        DataTable $DataTableBlock,
        array $data = []
    ) {
        parent::__construct($Context, $data);

        $this->_addressRenderer = $AddressRenderer;
        $this->_reportHelper = $ReportHelper;
        $this->_dataTableBlock = $DataTableBlock;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('SalesIgniter_Rental::report/serialnumber/modal/date.phtml');
    }

    /**
     * @param mixed $ReservationOrder
     *
     * @return $this
     */
    public function setReservationOrder($ReservationOrder)
    {
        $this->_reservationOrder = $ReservationOrder;
        if (is_int($ReservationOrder)) {
            $this->_reservationOrder = $this
                ->_reportHelper
                ->getRentalOrder($ReservationOrder);
        }
        return $this;
    }

    /**
     * @return \SalesIgniter\Rental\Model\ReservationOrders
     */
    public function getReservationOrder()
    {
        return $this->_reservationOrder;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this
                ->_reportHelper
                ->getOrder($this->_reservationOrder->getOrderId());
        }
        return $this->_order;
    }

    /**
     * @param $Type
     *
     * @return null|string
     */
    public function getOrderAddressHtml($Type)
    {
        $Html = 'Invalid Address Type';
        if (strtolower(trim($Type)) == 'billing') {
            if ($this->getOrder()->getBillingAddress()) {
                $Html = $this->_addressRenderer->format($this->getOrder()->getBillingAddress(), 'html');
            }
        } elseif (strtolower(trim($Type)) == 'shipping') {
            if ($this->getOrder()->getShippingAddress()) {
                $Html = $this->_addressRenderer->format($this->getOrder()->getShippingAddress(), 'html');
            }
        }
        return $Html;
    }

    public function getCustomerViewUrl()
    {
        if (!$this->getOrder()->getCustomerIsGuest()) {
            return $this->getUrl('customer/index/edit', ['id' => $this->getOrder()->getCustomerId()]);
        }
        return null;
    }

    public function getCustomerName($escaped = true)
    {
        return (
        $escaped === true
            ? $this->escapeHtml($this->getOrder()->getCustomerName())
            : $this->getOrder()->getCustomerName()
        );
    }

    public function getCustomerEmail($escaped = true)
    {
        return (
        $escaped === true
            ? $this->escapeHtml($this->getOrder()->getCustomerEmail())
            : $this->getOrder()->getCustomerEmail()
        );
    }

    public function getOrderViewUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
    }

    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    public function getOrderStatusLabel()
    {
        return $this->getOrder()->getStatusLabel();
    }

    public function getTimezoneForStore()
    {
        return $this->_localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getOrder()->getStore()->getCode()
        );
    }

    public function getOrderAdminDate()
    {
        return $this->formatDate(
            $this->getOrder()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    public function getOrderStoreDate()
    {
        return $this->formatDate(
            $this->getOrder()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true,
            $this->getTimezoneForStore()
        );
    }

    public function getOrderProductsGridHtml()
    {
        $this->_dataTableBlock->setCollection($this->getOrder()->getItemsCollection());

        $this->_dataTableBlock->addColumn('sku', [
            'header' => __('SKU'),
            'index' => 'sku'
        ]);

        $this->_dataTableBlock->addColumn('name', [
            'header' => __('Product'),
            'index' => 'name'
        ]);

        $this->_dataTableBlock->addColumn('qty_ordered', [
            'header' => __('Ordered'),
            'index' => 'qty_ordered',
            'type' => 'number'
        ]);

        $this->_dataTableBlock->addColumn('qty_shipped', [
            'header' => __('Shipped'),
            'index' => 'qty_shipped',
            'type' => 'number'
        ]);

        $this->_dataTableBlock->addColumn('row_total', [
            'header' => __('Total'),
            'index' => 'row_total',
            'type' => 'currency'
        ]);

        return $this->_dataTableBlock->getGridBlock()->getHtml();
    }

    protected function _toHtml()
    {
        return parent::_toHtml(); // TODO: Change the autogenerated stub
    }
}
