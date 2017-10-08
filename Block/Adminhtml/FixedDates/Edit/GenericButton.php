<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Block\Adminhtml\FixedDates\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;

/**
 * Class GenericButton.
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var FixedRentalNamesRepositoryInterface
     */
    protected $fixedRentalNamesRepository;

    /**
     * @param Context                                                      $context
     * @param \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
     */
    public function __construct(
        Context $context,
        FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
    ) {
        $this->context = $context;
        $this->fixedRentalNamesRepository = $fixedRentalNamesRepository;
    }

    /**
     * Return Reservation ID.
     *
     * @return int|null
     */
    public function getNameId()
    {
        if ($this->context->getRequest()->getParam('id')) {
            try {
                return $this->fixedRentalNamesRepository->getById(
                    $this->context->getRequest()->getParam('id')
                )->getId();
            } catch (NoSuchEntityException $e) {
            }
        }

        return null;
    }

    /**
     * Generate url by route and parameters.
     *
     * @param string $route
     * @param array  $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
