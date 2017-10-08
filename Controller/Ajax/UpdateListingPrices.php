<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Controller\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateListingPrices extends \Magento\Framework\App\Action\Action
{

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
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
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Action\Context           $context
     * @param \Magento\Catalog\Model\ProductFactory           $productModelFactory
     * @param \Magento\Framework\Registry                     $registry
     * @param \Magento\Catalog\Helper\Data                    $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \SalesIgniter\Rental\Helper\Calendar            $calendarHelper
     * @param \Magento\Framework\View\Result\PageFactory      $pageFactory
     * @param \SalesIgniter\Rental\Helper\Product             $productHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Session                  $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
        PageFactory $pageFactory,
        \SalesIgniter\Rental\Helper\Product $productHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Session $catalogSession
    )
    {
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->productModelFactory = $productModelFactory;
        parent::__construct($context);
        $this->calendarHelper = $calendarHelper;
        $this->productHelper = $productHelper;
        $this->catalogSession = $catalogSession;
        $this->pageFactory = $pageFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        $resultPage = $this->pageFactory->create();
        return $resultPage->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * Update Listing Prices
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        //sleep(7);
        $params = $this->getRequest()->getParams();
        $price = '';
        $responseContent = [
            'success' => false,
            'html' => '',
        ];
        if (isset($params['data-product-id'])) {
            $product = $this->productRepository->getById($params['data-product-id']);
            $priceRender = $this->getPriceRender();

            if ($priceRender) {
                $price = $priceRender->render(
                    \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                    $product,
                    [
                        'include_container' => true,
                        'display_minimal_price' => true,
                        'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    ]
                );
            }
            $responseContent = [
                'success' => true,
                'html' => $price,
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
