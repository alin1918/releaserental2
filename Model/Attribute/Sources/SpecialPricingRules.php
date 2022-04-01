<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Attribute\Sources;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;

/**
 * Product status functionality model.
 */
class SpecialPricingRules extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
     * @var \Magento\CatalogRule\Api\FixedRentalNamesRepositoryInterface
     */
    private $catalogRuleRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ScopeConfigInterface                                         $scopeConfig
     * @param \Magento\CatalogRule\Api\FixedRentalNamesRepositoryInterface $catalogRuleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                 $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Helper\Calendar                         $helperCalendar
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FixedRentalNamesRepositoryInterface $catalogRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperCalendar = $helperCalendar;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve option array.
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        $options = [];

        $criteria = $this->searchCriteriaBuilder->create();
        $items = $this->catalogRuleRepository->getList($criteria)->getItems();

        foreach ($items as $item) {
            $options[$item->getNameId()] = $item->getName();
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];
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
     */
    public function getOptionsArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => (string)$index, 'label' => $value];
        }

        return $result;
    }
}
