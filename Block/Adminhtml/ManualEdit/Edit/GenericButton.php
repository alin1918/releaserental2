<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Block\Adminhtml\ManualEdit\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ReservationOrdersRepositoryInterface
     */
    protected $reservationOrdersRepository;

    /**
     * @param Context                                                       $context
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     *
     */
    public function __construct(
        Context $context,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository
    ) {
        $this->context = $context;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
    }

    /**
     * Return Reservation ID
     *
     * @return int|null
     */
    public function getReservationId()
    {
        try {
            return $this->reservationOrdersRepository->getById(
                $this->context->getRequest()->getParam('id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array  $params
     *
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
