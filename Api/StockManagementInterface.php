<?php
/**
 * Created by PhpStorm.
 * User: cristian
 * Date: 10/25/2016
 * Time: 2:30 PM
 */

namespace SalesIgniter\Rental\Api;

interface StockManagementInterface
{
    /*
     * Inventory Operations functions
     */
    public function cancelReservationQty($reservationOrder, $qtyCancel, $recordInDb = true);

    public function shipReservationQty($reservationOrder, $qtyShip, $serialShip);

    public function returnReservationQty($reservationOrder, $qtyReturn, $serialReturn);

    public function reserveOrder(\Magento\Sales\Api\Data\OrderInterface $order);

    public function saveFromArray($data, $updateStock = true, $useGridData = false);

    public function saveReservation(\SalesIgniter\Rental\Model\ReservationOrdersInterface $reservation, array $data);

    public function deleteReservation(\SalesIgniter\Rental\Model\ReservationOrdersInterface $reservation);

    public function deleteReservationById($idRes);

    public function deleteReservationsByOrderId($orderId);

    public function deleteReservationsByProductId($productId);

    /**
     * Inventory access functions
     */
    public function checkIntervalValid($product, $dates, $currentQuantity, $baseInventory = null);

    public function getUpdatedInventory($productId, $startDateWithTurnover, $endDateWithTurnover, $qty, $qtyCancel = 0, $orderId = 0, $baseInventory = null);

    public function getInventoryTable($product);

    public function updateSirentQuantity($productId, $qty);

    public function getSirentQuantity($product);

    public function getAvailableQuantity($product, $startDate = '', $endDate = '', $excludingReservationsIds);

    public function getFirstTimeAvailable($product = null);

    public function getFirstDateAvailable($product = null);
}
