<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use SalesIgniter\Rental\Model\Attribute\Backend\PeriodType;

/**
 * Customize Reservation Advanced Pricing
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReservationAdvancedPricing extends AbstractModifier
{
    const CODE_PRICE = 'sirent_price';
    //const CODE_PRICE = 'sirent_price';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var PeriodType
     */
    protected $periodType;

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface                                        $locator
     * @param StoreManagerInterface                                   $storeManager
     * @param GroupRepositoryInterface                                $groupRepository
     * @param GroupManagementInterface                                $groupManagement
     * @param SearchCriteriaBuilder                                   $searchBuilder
     * @param ModuleManager                                           $moduleManager
     * @param Data                                                    $directoryHelper
     * @param ArrayManager                                            $arrayManager
     * @param \SalesIgniter\Rental\Model\Attribute\Backend\PeriodType $periodType
     * @param string                                                  $scopeName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchBuilder,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        PeriodType $periodType,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->groupManagement = $groupManagement;
        $this->searchBuilder = $searchBuilder;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->arrayManager = $arrayManager;
        $this->periodType = $periodType;
        $this->scopeName = $scopeName;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->customizeReservationPrice();

        return $this->meta;
    }

    /**
     * Customize rental price field
     *
     * @return $this
     */
    protected function customizeReservationPrice()
    {
        $reservationPricePath = $this->arrayManager->findPath(
            ReservationAdvancedPricing::CODE_PRICE,
            $this->meta,
            null,
            'children'
        );

        if ($reservationPricePath) {
            $this->meta = $this->arrayManager->merge(
                $reservationPricePath,
                $this->meta,
                $this->getReservationPriceStructure($reservationPricePath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($reservationPricePath, 0, -3)
                . '/' . ReservationAdvancedPricing::CODE_PRICE,
                $this->meta,
                $this->arrayManager->get($reservationPricePath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($reservationPricePath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    protected function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [
            [
                'label' => __('ALL GROUPS'),
                'value' => GroupInterface::CUST_GROUP_ALL,
            ],
        ];

        /** @var GroupInterface[] $groups */
        $groups = $this->groupRepository->getList($this->searchBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $customerGroups;
    }

    /**
     * Check rental_price attribute scope is global
     *
     * @return bool
     */
    protected function isScopeGlobal()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(ReservationAdvancedPricing::CODE_PRICE)
            ->isScopeGlobal();
    }

    /**
     * Get websites list
     *
     * @return array
     */
    protected function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ],
        ];
        $product = $this->locator->getProduct();

        if (!$this->isScopeGlobal() && $product->getStoreId()) {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->getStore()->getWebsite();

            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        } elseif (!$this->isScopeGlobal()) {
            $websitesList = $this->storeManager->getWebsites();
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($websitesList as $website) {
                /** @var \Magento\Store\Model\Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    protected function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    public function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    protected function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    protected function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    protected function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }

    /**
     * Get rental price dynamic rows structure
     *
     * @param string $reservationPricePath
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getReservationPriceStructure($reservationPricePath)
    {
        $tooltip = [
            'description' => __(
                'Use quantity and period. Format is like 1d = 1 day, 1w = 1 week, 1M = 1 month, 1m = 1 minute, 1h = 1 hour, 1y = 1 Year, 1s = 1 second. You can also use quantities other than 1 like 5d = 5 days'
            ),
        ];
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Reservation Price'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get($reservationPricePath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'website_id',
                                        'label' => __('Website'),
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                    ],
                                ],
                            ],
                        ],
                        'customer_group_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'customer_group_id',
                                        'label' => __('Customer Group'),
                                        'options' => $this->getCustomerGroups(),
                                        'value' => $this->getDefaultCustomerGroup(),
                                    ],
                                ],
                            ],
                        ],

                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Price'),
                                        'enableLabel' => true,
                                        'dataScope' => 'price',
                                        'addbefore' => $this->locator->getStore()
                                            ->getBaseCurrency()
                                            ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'period' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/rental-period',
                                        'tooltip' => $tooltip,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Period'),
                                        'enableLabel' => true,
                                        'dataScope' => 'period',
                                    ],
                                ],
                            ],
                        ],
                        'price_additional' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Price Additional'),
                                        'enableLabel' => true,
                                        'dataScope' => 'price_additional',
                                        'addbefore' => $this->locator->getStore()
                                            ->getBaseCurrency()
                                            ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'period_additional' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/rental-period',
                                        'tooltip' => $tooltip,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Period Additional'),
                                        'enableLabel' => true,
                                        'dataScope' => 'period_additional',
                                    ],
                                ],
                            ],
                        ],
                        'qty_start' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Qty Start'),
                                        'enableLabel' => true,
                                        'dataScope' => 'qty_start',
                                    ],
                                ],
                            ],
                        ],
                        'qty_end' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Qty End'),
                                        'enableLabel' => true,
                                        'dataScope' => 'qty_end',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve store
     *
     * @return \Magento\Store\Model\Store
     */
    protected function getStore()
    {
        return $this->locator->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
