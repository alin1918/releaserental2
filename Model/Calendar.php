<?php
namespace SalesIgniter\Rental\Model;

/**
 * Class Calendar
 * Everything related to calendar
 *
 * @package SalesIgniter\Rental\Model
 */

class Calendar
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session             $catalogSession
     * @param \Magento\Framework\Registry                $coreRegistry
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
    }
}
