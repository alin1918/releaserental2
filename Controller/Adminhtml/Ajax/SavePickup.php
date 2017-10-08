<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Sales\Model\OrderRepository;

/**
 * Class SuggestSerials
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Ajax
 */
class SavePickup extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SalesIgniter_Rental::send';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\SuggestedSet
     */
    protected $suggestedSet;
    /**
     * @var \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetailsCollectionFactory
     */
    private $serialDetailsCollectionFactory;
    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $resourceHelper;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param \Magento\Backend\App\Action\Context                                            $context
     * @param \Magento\Framework\DB\Helper                                                   $resourceHelper
     * @param \Magento\Sales\Model\OrderRepository                                           $orderRepository
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory                               $resultJsonFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        OrderRepository $orderRepository,
        \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serialDetailsCollectionFactory = $serialDetailsCollectionFactory;
        $this->resourceHelper = $resourceHelper;
        $this->orderRepository = $orderRepository;
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Action for attribute set selector
     *
     * @return \Magento\Framework\Controller\Result\Json
     */

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if ($this->getRequest()->getParam('order_id')) {
            $orderObj = $this->orderRepository->get($this->getRequest()->getParam('order_id'));
            $orderObj->setPickupDate($this->getRequest()->getParam('date_from'));
            $orderObj->setDropoffDate($this->getRequest()->getParam('date_to'));
            $this->orderRepository->save($orderObj);
        }
        $resultJson->setData(
            ['success' => true]
        );

        return $resultJson;
    }
}
