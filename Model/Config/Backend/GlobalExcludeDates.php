<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class GlobalExcludeDates extends ArraySerialized
{
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $helperRental;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Serialized constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $config
     * @param \Magento\Framework\App\Cache\TypeListInterface               $cacheTypeList
     * @param \SalesIgniter\Rental\Helper\Data                             $helperRental
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     * @param Json|null                                                    $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->helperRental = $helperRental;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $arrayVal = empty($value) ? false : $this->helperRental->unserialize($value);
            foreach ($arrayVal as $k => $val) {
                if ($val['all_day']) {
                    $val['all_day'] = '1';
                    $arrayVal[$k] = $val;
                } else {
                    $val['all_day'] = '0';
                    $arrayVal[$k] = $val;
                }
                if (isset($val['exclude_dates_from']) && is_array($val['exclude_dates_from'])) {
                    $val['exclude_dates_from'] = implode(',', $val['exclude_dates_from']);
                    $arrayVal[$k] = $val;
                }
            }

            $this->setValue($arrayVal);
        }
    }

    public function beforeSave()
    {
        $values = $this->getValue();
        foreach ($values as $k => $value) {
            if ($k !== '__empty') {
                if (isset($value['all_day'])) {
                    $value['all_day'] = true;
                    $values[$k] = $value;
                } else {
                    $value['all_day'] = false;
                    $values[$k] = $value;
                }
            }
        }
        $this->setValue($values);

        return parent::beforeSave();
    }
}
