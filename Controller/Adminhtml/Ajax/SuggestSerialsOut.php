<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\SerialNumberDetailsRepository;

/**
 * Class SuggestSerials
 * @SuppressWarnings(PHPMD.LongVariableNames)
 *
 * @package SalesIgniter\Rental\Controller\Adminhtml\Ajax
 */
class SuggestSerialsOut extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SalesIgniter_Rental::return';

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
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Model\SerialNumberDetailsRepository
     */
    private $serialNumberDetailsRepository;
    /**
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param \Magento\Backend\App\Action\Context                                            $context
     * @param \Magento\Framework\DB\Helper                                                   $resourceHelper
     * @param \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory
     * @param \SalesIgniter\Rental\Model\SerialNumberDetailsRepository                       $serialNumberDetailsRepository
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface                  $reservationOrdersRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                   $searchCriteriaBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory                               $resultJsonFactory
     *
     * @internal param \Magento\Framework\Api\SearchCriteriaInterfaceFactory $searchCriteriaInterfaceFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \SalesIgniter\Rental\Model\ResourceModel\SerialNumberDetails\CollectionFactory $serialDetailsCollectionFactory,
        SerialNumberDetailsRepository $serialNumberDetailsRepository,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serialDetailsCollectionFactory = $serialDetailsCollectionFactory;
        $this->resourceHelper = $resourceHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serialNumberDetailsRepository = $serialNumberDetailsRepository;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
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
        $resultJson->setData(
            $this->getSuggestedSerials($this->getRequest()->getParam('res_order'), $this->getRequest()->getParam('search'))
        );
        return $resultJson;
    }

    /**
     * Suggest serials for the send rentals page
     *
     * @param int    $productId
     * @param string $serial this is a partial serial that the user types into the input field
     *
     * @return array
     */
    private function getSuggestedSerialsNoApi($productId, $serial)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($serial, ['position' => 'any']);
        /** @var dd $collection */
        $collection = $this->serialDetailsCollectionFactory->create();
        $collection->addFieldToFilter(
            'product_id',
            ['eq' => $productId]
        )->addFieldToFilter(
            'status',
            ['eq' => 'available']
        )->addFieldToFilter(
            'serialnumber',
            ['like' => $labelPart]
        )->addFieldToSelect(
            'serialnumber_details_id',
            'id'
        )->addFieldToSelect(
            'serialnumber',
            'label'
        )->setOrder(
            'serialnumber_details_id',
            'ASC'
        );
        return $collection->getData();
    }

    /**
     * @param $resOrder
     * @param $serial
     *
     * @return array
     */
    private function getSuggestedSerials($resOrder, $serial)
    {
        $reservationOrder = $this->reservationOrdersRepository->getById($resOrder);
        $serialsShipped = explode(',', $reservationOrder->getSerialsShipped());
        $serialsReturned = explode(',', $reservationOrder->getSerialsReturned());
        $returnData = [];
        $notReturnedSerials = array_diff($serialsShipped, $serialsReturned);
        foreach ($notReturnedSerials as $notReturnedSerial) {
            if (strpos($notReturnedSerial, $serial) !== false) {
                $returnData[] = [
                    'value' => $notReturnedSerial,
                    'text' => $notReturnedSerial,
                ];
            }
        }
        return $returnData;
    }
}
