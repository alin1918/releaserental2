<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeGlobalDates extends \Magento\Framework\App\Action\Action
{

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar|Mage_Sales_Model_Resource_Order_Shipment_Item_Collection
     */
    private $calendarHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \SalesIgniter\Rental\Helper\Product
     */
    private $productHelper;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Catalog\Model\ProductFactory      $productModelFactory
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Catalog\Helper\Data               $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar       $calendarHelper
     * @param \SalesIgniter\Rental\Helper\Product        $productHelper
     * @param \Magento\Catalog\Model\Session             $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        \SalesIgniter\Rental\Helper\Product $productHelper,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        parent::__construct($context);
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->catalogSession = $catalogSession;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $dates = $this->calendarHelper->getDatesFromBuyRequest($params, null, true);
        if ($dates->getStartDate()) {
            $this->catalogSession->setStartDateGlobal($dates->getStartDate()->format('Y-m-d H:i:s'));
            $this->catalogSession->setEndDateGlobal($dates->getEndDate()->format('Y-m-d H:i:s'));
            $responseContent = [
                'success' => true,
                'message' => __('Dates were set'),
            ];
        } else {
            $responseContent = [
                'success' => false,
                'message' => __('Dates were not set'),
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
