<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Report;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Theme\Block\Html\Pager;

class GetReportData extends BackendAction
{

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var Pager
     */
    protected $_pagerBlock;

    /**
     * GetReportData constructor.
     *
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     * @param Pager       $PagerBlock
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Pager $PagerBlock
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_pagerBlock = $PagerBlock;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $ResultJson = $this->_resultJsonFactory->create();

        /** @var \SalesIgniter\Rental\Model\Report\Inventory $ReportModel */
        $ReportModel = $this->_objectManager
            ->create('SalesIgniter\Rental\Model\Report\\' . ucfirst($this->getRequest()->getParam('code')));
        $ReportModel->setRequest($this->getRequest());
        $ReportModel->applyFilters();

        /** @var \Magento\Backend\Block\Template $Filter */
        $Filter = $this->_objectManager
            ->create('Magento\Backend\Block\Template');
        if (strtolower($this->getRequest()->getParam('code')) == 'serialnumber') {
            $Filter->setTemplate('SalesIgniter_Rental::report/serialnumber/filter.phtml');
        } else {
            $Filter->setTemplate('SalesIgniter_Rental::report/filter.phtml');
        }

        $this->_pagerBlock
            ->setTemplate('SalesIgniter_Rental::report/pager.phtml')
            ->setIsOutputRequired(true)
            ->setAvailableLimit([5 => 5, 10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200])
            ->setCollection($ReportModel->getCollection());
        if ($this->getRequest()->getParam('limit')) {
            $this->_pagerBlock->setLimit($this->getRequest()->getParam('limit'));
        } else {
            $this->_pagerBlock->setLimit(5);
        }

        $ResultJson->setData([
            'success' => true,
            'filterBlock' => $Filter->toHtml(),
            'pagerBlock' => $this->_pagerBlock->toHtml(),
            'reportData' => $ReportModel->getData()
        ]);

        return $ResultJson;
    }
}
