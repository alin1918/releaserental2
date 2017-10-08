<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use SalesIgniter\Rental\Model\Attribute\Backend\PeriodType;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom;

/**
 * Customize Reservation Advanced Pricing
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReservationExcludedDates extends AbstractModifier
{
    const CODE_EXCLUDED_DATES = 'sirent_excluded_dates';

    //const CODE_EXCLUDED_DATES = 'sirent_price';

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

        $this->customizeExcludedDates();
        //return  $this->customizeExcludedDatesGroup($meta);
        return $this->meta;
    }

    /**
     * Customization of allow gift message field
     *
     * @param array $meta
     *
     * @return array
     */
    protected function customizeExcludedDatesGroup(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::CODE_EXCLUDED_DATES);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::CODE_EXCLUDED_DATES,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::CODE_EXCLUDED_DATES, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $tooltip = [
            'description' => __(
                'Excluded Dates for this specific product. 
                When you do not want to include times in the dates check the all day checkbox.'
            ),
        ];

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    static::CODE_EXCLUDED_DATES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::CODE_EXCLUDED_DATES,
                                    'imports' => [
                                        'disabled' => '${$.parentName}.use_config_'
                                            . static::CODE_EXCLUDED_DATES
                                            . ':checked',
                                    ],
                                    'tooltip' => $tooltip,
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                ],
                            ],
                        ],
                    ],
                    'use_config_' . static::CODE_EXCLUDED_DATES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => 'use_config_' . static::CODE_EXCLUDED_DATES,
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Customize rental price field
     *
     * @return $this
     */
    protected function customizeExcludedDates()
    {
        $excludedDatesPath = $this->arrayManager->findPath(
            ReservationExcludedDates::CODE_EXCLUDED_DATES,
            $this->meta,
            null,
            'children'
        );

        if ($excludedDatesPath) {
            $this->meta = $this->arrayManager->merge(
                $excludedDatesPath,
                $this->meta,
                $this->getReservationExcludedDatesStructure($excludedDatesPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($excludedDatesPath, 0, -3)
                . '/' . ReservationExcludedDates::CODE_EXCLUDED_DATES,
                $this->meta,
                $this->arrayManager->get($excludedDatesPath, $this->meta)
            );

            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($excludedDatesPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Get rental price dynamic rows structure
     *
     * @param string $reservationExcludedDatesPath
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getReservationExcludedDatesStructure($reservationExcludedDatesPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Excluded Dates'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get($reservationExcludedDatesPath . '/arguments/data/config/sortOrder', $this->meta),
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
                                'is_collection' => false,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'date_from' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/datetime',
                                        'formElement' => Input::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('Date From'),
                                        'enableLabel' => true,
                                        'dataScope' => 'disabled_from',
                                    ],
                                ],
                            ],
                        ],
                        'date_to' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/datetime',
                                        'formElement' => Input::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('Date To'),
                                        'enableLabel' => true,
                                        'dataScope' => 'disabled_to',
                                    ],
                                ],
                            ],
                        ],
                        'all_day' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Checkbox::NAME,
                                        'prefer' => 'toggle',
                                        'valueMap' => [
                                            'false' => '0',
                                            'true' => '1',
                                        ],
                                        'label' => __('All Day'),
                                        'enableLabel' => true,
                                        'dataScope' => 'all_day',

                                    ],
                                ],
                            ],
                        ],
                        'disabled_type' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'dataType' => Text::NAME,
                                        'options' => [
                                            ['value' => 'none', 'label' => __('None')],
                                            ['value' => 'daily', 'label' => __('Daily')],
                                            ['value' => 'dayweek', 'label' => __('Day of Week')],
                                            ['value' => 'monthly', 'label' => __('Monthly')],
                                            ['value' => 'yearly', 'label' => __('Yearly')]
                                        ],
                                        'label' => __('Repeat Period'),
                                        'enableLabel' => true,
                                        'dataScope' => 'disabled_type',
                                    ],
                                ],
                            ],
                        ],
                        'exclude_dates_from' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => MultiSelect::NAME,
                                        'options' => [
                                            ['value' => ExcludedDaysWeekFrom::PRICE, 'label' => __('Price')],
                                            ['value' => ExcludedDaysWeekFrom::CALENDAR, 'label' => __('Calendar')],
                                            ['value' => ExcludedDaysWeekFrom::TURNOVER, 'label' => __('Turnover')]
                                        ],
                                        'dataType' => Text::NAME,
                                        'label' => __('Exclude From'),
                                        'enableLabel' => true,
                                        'dataScope' => 'exclude_dates_from',
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
