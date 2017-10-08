<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\Ajax;

use Magento\Framework\Controller\ResultFactory;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeDatesOnProducts extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $quoteSession;

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
     * @param \Magento\Backend\Model\Session\Quote       $quoteSession
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
        \Magento\Backend\Model\Session\Quote $quoteSession,
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
        $this->quoteSession = $quoteSession;
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
        $events = [];
        if ($dates->getStartDate()) {
            $quoteArr = $this->quoteSession->getQuote()->getItemsCollection();
            foreach ($quoteArr as $quoteItem) {
                if (!($quoteItem->getParentItem() && $quoteItem->getParentItem()->getProductType() === Sirent::TYPE_RENTAL)) {
                    $optionCollection = $this->_objectManager->create(
                        'Magento\Quote\Model\Quote\Item\Option'
                    )->getCollection()->addItemFilter(
                        $quoteItem
                    );

                    foreach ($optionCollection as $option) {
                        if ($option->getCode() == 'info_buyRequest') {
                            $infoBuyRequest = unserialize($option->getValue());
                            $infoBuyRequest['calendar_selector']['from'] = $params['calendar_selector']['from'];
                            $infoBuyRequest['calendar_selector']['to'] = $params['calendar_selector']['to'];

                            foreach ($infoBuyRequest as $item => $value) {
                                if ($item !== 'configured') {
                                    $events['itemConfigs'][$quoteItem->getId()][$item] = $value;
                                }
                            }
                        }
                    }
                    $events['itemId'][] = $quoteItem->getId();
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($events);
        return $resultJson;
    }
}
