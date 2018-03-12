<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Report\Inventory;

use Magento\Backend\Block\Template as BlockTemplate;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use SalesIgniter\Rental\Model\Product\Stock as RentalStock;

class Body extends BlockTemplate
{

    /**
     * @var JsonEncoder
     */
    protected $_jsonEncoder;

    /**
     * @var ProductCollection
     */
    protected $_collection;

    /**
     * @var StockStateInterface
     */
    protected $_stockState;

    /**
     * @var RentalStock
     */
    protected $_rentalStock;
    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	private $helperRental;

	/**
	 * Content constructor.
	 *
	 * @param Context                                           $context
	 * @param JsonEncoder                                       $JsonEncoder
	 * @param ProductCollection                                 $Collection
	 * @param StockStateInterface                               $StockState
	 * @param \SalesIgniter\Rental\Helper\Data                  $helperRental
	 * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
	 * @param RentalStock                                       $RentalStock
	 * @param array                                             $data
	 */
    public function __construct(
        Context $context,
        JsonEncoder $JsonEncoder,
        ProductCollection $Collection,
        StockStateInterface $StockState,
	    \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        RentalStock $RentalStock,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->_jsonEncoder = $JsonEncoder;

        $this->_collection = $Collection;
        $this->_collection->addAttributeToSelect('name');
        $this->_collection->addAttributeToSelect('sirent_quantity');
        $this->_collection->addAttributeToSelect('sirent_inv_bydate_serialized');

        $this->_stockState = $StockState;
        $this->_rentalStock = $RentalStock;
        $this->stockManagement = $stockManagement;
	    $this->helperRental = $helperRental;
    }

    /**
     * @return ProductCollection|Product[]
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param $Product
     *
     * @return float
     */
    public function getStock(Product $Product)
    {
        $Quantity = $Product->getSirentQuantity();

        /**
         * @TODO: Hook for other quantity source, like inventory center
         */
        return $Product->getSirentQuantity();
    }

    public function getAvailableByDate($Product, $Date)
    {
        $StartDate = new \DateTime();
        $StartDate->setTimestamp(strtotime($Date));
        $StartDate->setTime(0, 0, 0);

        $EndDate = new \DateTime();
        $EndDate->setTimestamp(strtotime($Date));
        $EndDate->setTime(23, 59, 59);

        return $this->stockManagement->getAvailableQuantity($Product, $StartDate, $EndDate);
    }

    public function getSerializedInventory($Product)
    {
        $SerializedString = $this->helperRental->getAttribute( $Product->getId(), 'sirent_inv_bydate_serialized' );

        $Json = [];
        if ($SerializedString) {
            $Unserialized = unserialize($SerializedString);
            if (is_array($Unserialized)) {
                $Json = $this->_jsonEncoder->encode($Unserialized);
            }
        }

        return $Json;
    }
}
