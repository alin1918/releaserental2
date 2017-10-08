<?php

namespace SalesIgniter\Rental\Model\Report;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\UrlInterface as UrlBuilder;
use SalesIgniter\Rental\Model\Product\Stock as RentalStock;

class Inventory
{

    /**
     * @var UrlBuilder
     */
    protected $_urlBuilder;

    /**
     * @var ProductCollection|Product[]
     */
    protected $_collection;

    /**
     * @var RentalStock
     */
    protected $_rentalStock;

    /**
     * @var string
     */
    protected $_rendererName;

    /**
     * @var
     */
    protected $_dateTo;

    /**
     * @var
     */
    protected $_dateFrom;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \SalesIgniter\Rental\Api\StockManagementInterface
     */
    private $stockManagement;

    /**
     * Inventory constructor.
     *
     * @param UrlBuilder                                        $urlBuilder
     * @param ProductCollection                                 $ProductCollection
     * @param \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement
     * @param RentalStock                                       $RentalStock
     */
    public function __construct(
        UrlBuilder $urlBuilder,
        ProductCollection $ProductCollection,
        \SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
        RentalStock $RentalStock
    ) {
        $this->_urlBuilder = $urlBuilder;

        $this->_collection = $ProductCollection;
        $this->_collection->addAttributeToSelect('name');
        $this->_collection->addAttributeToSelect('sirent_quantity');
        $this->_collection->addFieldToFilter('type_id', \SalesIgniter\Rental\Model\Product\Type\Sirent::TYPE_RENTAL);

        $this->_rentalStock = $RentalStock;
        $this->stockManagement = $stockManagement;
    }

    /**
     * @return \Magento\Catalog\Model\Product[]|ProductCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param $Collection
     *
     * @return $this
     */
    public function setCollection($Collection)
    {
        $this->_collection = $Collection;
        return $this;
    }

    public function getRendererCode()
    {
        return $this->getRequest()->getParam('rendererCode', 'month');
    }

    /**
     * @param $DateFrom
     *
     * @return $this
     */
    public function setDateFrom($DateFrom)
    {
        $this->_dateFrom = $DateFrom;
        return $this;
    }

    /**
     * @param $DateTo
     *
     * @return $this
     */
    public function setDateTo($DateTo)
    {
        $this->_dateTo = $DateTo;
        return $this;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $Request
     *
     * @return $this
     */
    public function setRequest(\Magento\Framework\App\RequestInterface $Request)
    {
        $this->_request = $Request;
        return $this;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $RequestParams = $this->getRequest()->getParams();
        $this->setDateFrom($this->getRequest()->getParam('dateFrom', date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')))));
        $this->setDateTo($this->getRequest()->getParam('dateTo', date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') + 1, 0, date('Y')))));

        $DataArray = [
            'calendar' => [
                'dateDataUrl' => $this->_urlBuilder->getUrl('*/report_inventory/getDateReportData'),
                'rendererCode' => $this->getRendererCode()
            ],
            'products' => []
        ];
        foreach ($this->_collection as $Product) {
            $DataArray['products'][] = [
                'id' => $Product->getId(),
                'sku' => $Product->getSku(),
                'name' => $Product->getName(),
                'sirent_quantity' => $Product->getSirentQuantity(),
                'availability' => $this->getAvailabilityDates($Product)
            ];
        }

        return $DataArray;
    }

    public function applyFilters()
    {
        $Filters = $this->getRequest()->getParam('filter', null);
        if ($Filters) {
            if (isset($Filters['option']) && empty($Filters['option']) === false) {
                foreach ($Filters['option'] as $FilterName) {
                    switch ($FilterName) {
                        case 'product_name':
                            $this->_collection->addFieldToFilter('name', ['like' => '%' . $Filters['text'] . '%']);
                            break;
                        case 'product_sku':
                            $this->_collection->addFieldToFilter('sku', ['like' => '%' . $Filters['text'] . '%']);
                            break;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $Timestamp
     *
     * @return \DateTime
     */
    protected function getDateTimeObj($Timestamp)
    {
        $DateTime = new \DateTime();
        $DateTime->setTimezone(new \DateTimeZone('UTC'));
        $DateTime->setTimestamp($Timestamp);

        return $DateTime;
    }

    /**
     * @param $Product
     *
     * @return array
     */
    protected function getAvailabilityDates($Product)
    {
        $Availabilites = [];

        $HourTime = (60 * 60);
        $DayTime = ($HourTime * 24);
        $WeekTime = ($DayTime * 7);

        $StartDate = $this->getDateTimeObj(strtotime($this->_dateFrom));
        $StartDate->setTime(0, 0, 0);

        $EndDate = $this->getDateTimeObj(strtotime($this->_dateTo));
        $EndDate->setTime(23, 59, 59);

        if ($this->getRendererCode() == 'day') {
            $CurrentHour = 0;
            for ($i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $HourTime) {
                $_checkStartDate = $this->getDateTimeObj($i);
                $_checkStartDate->setTime($CurrentHour, 0, 0);

                $_checkEndDate = $this->getDateTimeObj($i);
                $_checkEndDate->setTime($_checkStartDate->format('H'), 59, 0);

                $Availabilites[$i] = [
                    'from' => $_checkStartDate->format('Y-m-d H:i:s'),
                    'to' => $_checkEndDate->format('Y-m-d H:i:s'),
                    'result' => $this->stockManagement->getAvailableQuantity($Product, $_checkStartDate, $_checkEndDate)
                ];

                $CurrentHour++;
                if ($CurrentHour > 23) {
                    $CurrentHour = 0;
                }
            }
        } elseif ($this->getRendererCode() == 'week') {
            for ($i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime) {
                $_checkStartDate = $this->getDateTimeObj($i);
                $_checkStartDate->setTime(0, 0, 0);

                $_checkEndDate = $this->getDateTimeObj($i);
                $_checkEndDate->setTime(23, 59, 0);

                $Availabilites[$i] = [
                    'from' => $_checkStartDate->format('Y-m-d H:i:s'),
                    'to' => $_checkEndDate->format('Y-m-d H:i:s'),
                    'result' => $this->stockManagement->getAvailableQuantity($Product, $_checkStartDate, $_checkEndDate)
                ];
            }
        } elseif ($this->getRendererCode() == 'month') {
            for ($i = $StartDate->getTimestamp(); $i < $EndDate->getTimestamp(); $i += $DayTime) {
                $_checkStartDate = $this->getDateTimeObj($i);
                $_checkStartDate->setTime(0, 0, 0);

                $_checkEndDate = $this->getDateTimeObj($i);
                $_checkEndDate->setTime(23, 59, 0);

                $Availabilites[$i] = [
                    'from' => $_checkStartDate->format('Y-m-d H:i:s'),
                    'to' => $_checkEndDate->format('Y-m-d H:i:s'),
                    'result' => $this->stockManagement->getAvailableQuantity($Product, $_checkStartDate, $_checkEndDate)
                ];
            }
        }

        return $Availabilites;
    }

    /**
     * @param $timestamp
     *
     * @return bool|string
     */
    protected function getDateFormatted($timestamp)
    {
        return date('m/d', $timestamp);
    }
}
