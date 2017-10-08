<?php
namespace SalesIgniter\Rental\Api;

use SalesIgniter\Rental\Model\PriceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PriceRepositoryInterface
{
    public function save(PriceInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(PriceInterface $page);

    public function deleteById($id);
}
