<?php
namespace SalesIgniter\Rental\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ReservationOrdersRepositoryInterface
{
    public function getById($idRes);

    public function getByOrderItemId($orderItemId);

    public function getList(SearchCriteriaInterface $criteria);
}
