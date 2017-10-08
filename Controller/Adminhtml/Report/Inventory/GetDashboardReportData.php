<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Report\Inventory;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Controller\Result\JsonFactory;

class GetDashboardReportData extends BackendAction
{

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var Product
     */
    protected $_productModel;

    /**
     * GetProductReportData constructor.
     *
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     * @param Product     $ProductModel
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Product $ProductModel
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_productModel = $ProductModel;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $RequestParams = $this->getRequest()->getParams();
        $ResultJson = $this->_resultJsonFactory->create();

        $Product = $this->_productModel
            ->load($RequestParams['product']);

        $ResultJson->setData([
            'success' => true,
            'reportData' => []
        ]);

        return $ResultJson;
    }
}
