<?php
namespace SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal;

use Magento\Backend\Block\Template as BlockTemplate;

class RentalInfo
	extends BlockTemplate
{

	protected $_dateFormat = 'F jS, Y g:i a';
	/**
	 * @return \Magento\Sales\Model\Order
	 */
	public function getOrder()
	{
		return $this->getParentBlock()->getOrder();
	}

	/**
	 * @return \SalesIgniter\Rental\Model\ReservationOrders
	 */
	public function getReservationOrder()
	{
		return $this->getParentBlock()->getReservationOrder();
	}

	/**
	 * @return string
	 */
	public function getCustomerName()
	{
		return $this->getOrder()->getCustomerName();
	}

	/**
	 * @return mixed
	 */
	public function getStartDate()
	{
		$Date = new \DateTime($this->getReservationOrder()->getStartDate());
		return $Date->format($this->_dateFormat);
	}

	/**
	 * @return mixed
	 */
	public function getEndDate()
	{
		$Date = new \DateTime($this->getReservationOrder()->getEndDate());
		return $Date->format($this->_dateFormat);
	}

	/**
	 * @return mixed
	 */
	public function getEndDateWithTurnover()
	{
		$Date = new \DateTime($this->getReservationOrder()->getEndDateWithTurnover());
		return $Date->format($this->_dateFormat);
	}

	public function getCustomerUrl()
	{
		return $this->getUrl('customer/index/edit', ['id' => $this->getOrder()->getCustomerId()]);
	}

	public function getOrderUrl()
	{
		return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
	}

	public function getOrderNumber()
	{
		return $this->getOrder()->getIncrementId();
	}
}
