<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Sources;

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Product status functionality model.
 */
class CatalogRules extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;
    /**
     * @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
     */
    private $catalogRuleRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ScopeConfigInterface                                    $scopeConfig
     * @param \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Helper\Calendar                    $helperCalendar
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperCalendar = $helperCalendar;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * This is needed until they fix the repository interface.
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList()
    {
        /** @var \Magento\CatalogRule\Model\ResourceModel\Rule $catalogRuleResource */
        $catalogRuleResource = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $connection = $catalogRuleResource->getConnection();
        $select = $connection->select();
        $select->from($catalogRuleResource->getMainTable(), ['rule_id', 'name']);
        $items = $connection->fetchAll($select);

        return $items;
    }

    /**
     * Retrieve option array.
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getList() as $item) {
            $options[$item['rule_id']] = $item['name'];
        }
        /*$criteria = $this->searchCriteriaBuilder->create();
        $items = $this->catalogRuleRepository->getList($criteria)->getItems();

        foreach($items as $item){
            $options[$item->getRuleId()] = $item->getName();
        }*/
        return $options;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            foreach ($this->getOptionArray() as $index => $value) {
                $this->_options[] = [
                    'label' => $value,
                    'value' => $index,
                ];
            }
        }

        return $this->_options;
    }

    /**
     * Retrieve option array with empty value.
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
