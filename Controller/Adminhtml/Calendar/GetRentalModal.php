<?php

namespace SalesIgniter\Rental\Controller\Adminhtml\Calendar;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use SalesIgniter\Rental\Model\ReservationOrdersFactory;

class GetRentalModal extends BackendAction
{

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var DataCollection
     */
    protected $_collection;

    /**
     * @var DataObjectFactory
     */
    protected $_dataObjectFactory;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_orderFactory;

    /**
     * @var ReservationOrdersFactory
     */
    protected $_reservationOrdersFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var \SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal
     */
    private $modalBlock;

    /**
     * getRentalModal constructor.
     *
     * @param Context                                             $context
     * @param Registry                                            $CoreRegistry
     * @param JsonFactory                                         $resultJsonFactory
     * @param PageFactory                                         $resultPageFactory
     * @param ResourceConnection                                  $ResourceConnection
     * @param DataCollection                                      $Collection
     * @param DataObjectFactory                                   $ObjectFactory
     * @param OrderFactory                                        $OrderFactory
     * @param ReservationOrdersFactory                            $reservationOrdersFactory
     * @param \SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal $modalBlock
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Registry $CoreRegistry,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        ResourceConnection $ResourceConnection,
        DataCollection $Collection,
        DataObjectFactory $ObjectFactory,
        OrderFactory $OrderFactory,
        ReservationOrdersFactory $reservationOrdersFactory,
        \SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal $modalBlock
    ) {
        parent::__construct($context);

        $this->_coreRegistry = $CoreRegistry;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_collection = $Collection;
        $this->_dataObjectFactory = $ObjectFactory;
        $this->_resourceConnection = $ResourceConnection;
        $this->_orderFactory = $OrderFactory;
        $this->_reservationOrdersFactory = $reservationOrdersFactory;
        $this->modalBlock = $modalBlock;
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

        /** @var \SalesIgniter\Rental\Block\Adminhtml\Calendar\Modal $ModalBlock */

        $this->modalBlock->setNameInLayout('rental.modal');
        $this->modalBlock->setTemplate('SalesIgniter_Rental::calendar/modal.phtml');

        if ($RequestParams['renderer'] == 'byorder') {
            $ReservationOrderId = $RequestParams['id'];
            $this->modalBlock->setReservationOrderId($ReservationOrderId);
        }

        $ResultJson->setData([
            'success' => true,
            'html' => $this->modalBlock->toHtml()
        ]);

        return $ResultJson;
    }
}
