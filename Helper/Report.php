<?php

namespace SalesIgniter\Rental\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Model\ReservationOrdersFactory;

class Report extends AbstractHelper
{

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_reservationOrdersFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * Report constructor.
     *
     * @param Context                  $Context
     * @param ResourceConnection       $ResourceConnection
     * @param OrderFactory             $OrderFactory
     * @param ReservationOrdersFactory $ReservationOrdersFactory
     */
    public function __construct(
        Context $Context,
        ResourceConnection $ResourceConnection,
        OrderFactory $OrderFactory,
        ReservationOrdersFactory $ReservationOrdersFactory
    ) {
        parent::__construct($Context);

        $this->_resourceConnection = $ResourceConnection;
        $this->_orderFactory = $OrderFactory;
        $this->_reservationOrdersFactory = $ReservationOrdersFactory;
    }

    /**
     * @param $OrderId
     *
     * @return $this
     */
    public function getOrder($OrderId)
    {
        return $this
            ->_orderFactory
            ->create()
            ->load($OrderId);
    }

    /**
     * @param integer $ReservationOrderId
     *
     * @return mixed
     */
    public function getRentalOrder($ReservationOrderId)
    {
        return $this
            ->_reservationOrdersFactory
            ->create()
            ->load((int)$ReservationOrderId);
    }

    /**
     * @param $Settings
     *
     * @return array
     * @throws \DomainException
     */
    public function getRentalOrders($Settings)
    {
        $Connection = $this->_resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $Data = array_merge([], [
            'use_turnover_date' => true,
            'include_order_data' => false,
            'return_collection' => false,
            'start_date' => '',
            'end_date' => '',
            'start_date_column' => 'start_date',
            'end_date_column' => 'end_date',
            'conditions' => []
        ], $Settings);

        if ($Data['start_date'] instanceof \DateTime) {
            $Data['start_date'] = $Data['start_date']->format('Y-m-d H:i:s');
        }

        if ($Data['end_date'] instanceof \DateTime) {
            $Data['end_date'] = $Data['end_date']->format('Y-m-d H:i:s');
        }

        /*if ($Data['use_turnover_date'] === true) {
            $Data['start_date_column'] .= '_with_turnover';
            $Data['end_date_column'] .= '_with_turnover';
        }*/
        $Data['start_date_column'] .= '_use_grid';
        $Data['end_date_column'] .= '_use_grid';

        $SelectColumns = ['reservationOrders.*'];
        if ($Data['return_collection'] === false && $Data['include_order_data'] === true) {
            $SelectColumns[] = 'orders.increment_id';
            $SelectColumns[] = 'orders.customer_id';
            $SelectColumns[] = 'CONCAT(orders.customer_firstname, \' \', orders.customer_lastname) as customer_name';
        }

        $FromTables = [$this->_resourceConnection->getTableName('sirental_reservationorders') . ' reservationOrders '];
        if ($Data['return_collection'] === false && $Data['include_order_data'] === true) {
            $FromTables[] = 'INNER JOIN ' . $this->_resourceConnection->getTableName('sales_order') . ' orders ' .
                'ON orders.entity_id = reservationOrders.order_id ';
        }

        $Query = 'SELECT ';
        $Query .= implode(', ', $SelectColumns) . ' ';
        $Query .= 'FROM ';
        $Query .= implode(' ', $FromTables);
        $Query .= 'WHERE ';
        /**
         * Parent_id if the order is not shipped is 0
         * If order is shipped it will use the row with qty_use_grid > 0 because the parent_id == 0 row will have qty_use_grid = 0
         */
        $Query .= 'reservationOrders.parent_id >= 0 AND reservationOrders.qty_use_grid > 0 AND ';

        if (empty($Data['conditions']) === false) {
            $Conditions = [];
            foreach ($Data['conditions'] as $ColumnName => $Condition) {
                $ConditionString = $ColumnName . ' ';
                if (is_array($Condition)) {
                    $Comparator = array_keys($Condition);
                    if ($Comparator[0] == 'null') {
                        if ($Condition[$Comparator[0]] === true) {
                            $ConditionString .= 'IS NULL';
                        } elseif ($Condition[$Comparator[0]] === false) {
                            $ConditionString .= 'IS NOT NULL';
                        }
                    } elseif ($Comparator[0] == 'like') {
                        $ConditionString .= $this->_getComparatorLiteral($Comparator[0]) . ' "%' . $Condition[$Comparator[0]] . '%"';
                    } else {
                        $ConditionString .= $this->_getComparatorLiteral($Comparator[0]) . ' "' . $Condition[$Comparator[0]] . '"';
                    }
                } else {
                    $ConditionString .= '= "' . $Condition . '"';
                }

                $Conditions[] = $ConditionString;
            }
            $Query .= implode(' AND ', $Conditions) . ' AND ';
        }

        $Query .= '(
			(
				CAST(reservationOrders.' . $Data['start_date_column'] . ' AS DATETIME) = CAST("' . $Data['start_date'] . '" AS DATETIME)
				OR
				CAST(reservationOrders.' . $Data['start_date_column'] . ' AS DATETIME) = CAST("' . $Data['end_date'] . '" AS DATETIME)
			) OR (
				CAST(reservationOrders.' . $Data['end_date_column'] . ' AS DATETIME) = CAST("' . $Data['start_date'] . '" AS DATETIME)
				OR
				CAST(reservationOrders.' . $Data['end_date_column'] . ' AS DATETIME) = CAST("' . $Data['end_date'] . '" AS DATETIME)
			) OR (
				CAST(reservationOrders.' . $Data['start_date_column'] . ' AS DATETIME) BETWEEN CAST("' . $Data['start_date'] . '" AS DATETIME) AND CAST("' . $Data['end_date'] . '" AS DATETIME)
				OR
				CAST(reservationOrders.' . $Data['end_date_column'] . ' AS DATETIME) BETWEEN CAST("' . $Data['start_date'] . '" AS DATETIME) AND CAST("' . $Data['end_date'] . '" AS DATETIME)
			) OR (
				CAST("' . $Data['start_date'] . '" AS DATETIME) BETWEEN CAST(reservationOrders.' . $Data['start_date_column'] . ' AS DATETIME) AND CAST(reservationOrders.' . $Data['end_date_column'] . ' AS DATETIME)
				OR
				CAST("' . $Data['end_date'] . '" AS DATETIME) BETWEEN CAST(reservationOrders.' . $Data['start_date_column'] . ' AS DATETIME) AND CAST(reservationOrders.' . $Data['end_date_column'] . ' AS DATETIME)
			)
		) ';
        //$Query .= 'GROUP BY reservationOrders.order_id';

        $Result = $Connection->fetchAll($Query);
        if ($Data['return_collection'] === true) {
            return $this->_populateCollection($Result, $Data);
        }
        return $Result;
    }

    protected function _getComparatorLiteral($Comparator)
    {
        $Literal = '=';
        if ($Comparator == 'like') {
            $Literal = 'LIKE';
        } elseif ($Comparator == 'neq') {
            $Literal = '!=';
        } elseif ($Comparator == 'lt') {
            $Literal .= '<';
        } elseif ($Comparator == 'gt') {
            $Literal .= '>';
        } elseif ($Comparator == 'lteq') {
            $Literal .= '<=';
        } elseif ($Comparator == 'gteq') {
            $Literal .= '>=';
        }
        return $Literal;
    }

    protected function _populateCollection(array $Data, $Settings)
    {
        $OrderIds = [];
        foreach ($Data as $Item) {
            $OrderIds[] = $Item['reservationorder_id'];
        }

        /** @var \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders\Collection $Collection */
        $Collection = $this->_reservationOrdersFactory
            ->create()
            ->getCollection();
        $Collection->addFieldToFilter('reservationorder_id', ['in' => $OrderIds]);

        if ($Settings['include_order_data'] === true) {
            $Collection->join(
                ['o' => $Collection->getTable('sales_order')],
                'o.entity_id = main_table.order_id',
                ['increment_id', 'customer_id', 'CONCAT(o.customer_firstname, \' \', o.customer_lastname) as customer_name']
            );
        }

        return $Collection;
    }
}
