<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Ui\DataProvider\FixedDates\Form\Modifier;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeek;

/**
 * Class Related.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FixedRentalDates extends AbstractModifier
{
    const DATA_SCOPE = 'rental-fixeddate';
    const DATA_SCOPE_FIXEDDATES = 'fixeddates';
    const GROUP_FIXEDDATES = 'fixeddates';

    /**
     * @var string
     */
    private static $previousGroup = 'search-engine-optimization';

    /**
     * @var int
     */
    private static $sortOrder = 90;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @var string
     */
    protected $scopePrefix;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Columns\Price
     */
    private $priceModifier;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \SalesIgniter\Rental\Ui\DataProvider\Reservation\Form\Modifier\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface
     */
    private $fixedRentalDatesRepository;

    /**
     * @param UrlInterface                                                 $urlBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                 $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository
     * @param \Magento\Framework\App\RequestInterface                      $request
     * @param string                                                       $scopeName
     * @param string                                                       $scopePrefix
     */
    public function __construct(
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository,
        \Magento\Framework\App\RequestInterface $request,
        $scopeName = '',
        $scopePrefix = ''
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeName = $scopeName;
        $this->scopePrefix = $scopePrefix;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        $this->fixedRentalDatesRepository = $fixedRentalDatesRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_FIXEDDATES => [
                    'children' => [
                        $this->scopePrefix.static::DATA_SCOPE_FIXEDDATES => $this->getStructure(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Dates'),
                                'collapsible' => false,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$previousGroup,
                                    self::$sortOrder
                                ),
                            ],
                        ],

                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        $dataScope = self::DATA_SCOPE_FIXEDDATES;
        $idName = $this->request->getParam('id');
        $this->searchCriteriaBuilder->addFilter('main_table.name_id', $idName);
        $criteria = $this->searchCriteriaBuilder->create();
        $items = $this->fixedRentalDatesRepository->getList($criteria)->getItems();
        foreach ($items as $item) {
            $data[$idName][self::DATA_SCOPE][$dataScope][] = $this->fillData($item);
        }
        //$data['data']['items'] = $data[$dataScope];
        return $data;
    }

    /**
     * Prepare data column.
     *
     *
     * @param \SalesIgniter\Rental\Model\FixedRentalDates $fixedDate
     *
     * @return array
     */
    protected function fillData(\SalesIgniter\Rental\Model\FixedRentalDates $fixedDate)
    {
        $weekMonth = '';
        $repeatDays = '';
        if ($fixedDate->getWeekMonth()) {
            $weekMonth = array_map('intval', unserialize($fixedDate->getWeekMonth()));
        }
        if ($fixedDate->getRepeatDays()) {
            $repeatDays = array_map('intval', unserialize($fixedDate->getRepeatDays()));
        }

        return [
            // 'name_id' => $fixedDate->getNameId(),
            //'date_id' => $fixedDate->getDateId(),
            'date_from' => $fixedDate->getDateFrom(),
            'date_to' => $fixedDate->getDateTo(),
            'all_day' => $fixedDate->getAllDay(),
            'repeat_type' => $fixedDate->getRepeatType(),
            'repeat_days' => $repeatDays,
            'week_month' => $weekMonth,
        ];
    }

    /**
     * Retrieve all data scopes.
     *
     * @return array
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_FIXEDDATES,
        ];
    }

    /**
     * Get dynamic rows structure.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getStructure()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        //'template' => 'ui/dynamic-rows/templates/collapsible',
                        //'label' => '',
                        'additionalClasses' => 'admin__field-wide',
                        //'collapsibleHeader' => true,
                        'addButton' => true,
                        'label' => __(''),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
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
                        /*'name_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Name Id'),
                                        'enableLabel' => false,
                                        'visible' => false,
                                        'dataScope' => 'name_id',
                                    ],
                                ],
                            ],
                        ],
                        'date_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Date Id'),
                                        'enableLabel' => false,
                                        'visible' => false,
                                        'dataScope' => 'date_id',
                                    ],
                                ],
                            ],
                        ],*/
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
                                        'dataScope' => 'date_from',
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
                                        'dataScope' => 'date_to',
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
                        'repeat_type' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'dataType' => Text::NAME,
                                        'options' => [
                                            ['value' => 'none', 'label' => __('None')],
                                            ['value' => 'daily', 'label' => __('Daily')],
                                            ['value' => 'monthly', 'label' => __('Monthly')],
                                            ['value' => 'yearly', 'label' => __('Yearly')],
                                        ],
                                        'label' => __('Repeat Period'),
                                        'enableLabel' => true,
                                        'dataScope' => 'repeat_type',
                                    ],
                                ],
                            ],
                        ],
                        'repeat_days' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => MultiSelect::NAME,
                                        'options' => ExcludedDaysWeek::getOptionsArray([ExcludedDaysWeek::NONE]),
                                        'dataType' => Text::NAME,
                                        'label' => __('Repeat Days(Daily Only)'),
                                        'enableLabel' => true,
                                        'dataScope' => 'repeat_days',
                                    ],
                                ],
                            ],
                        ],
                        'week_month' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => MultiSelect::NAME,
                                        'options' => [
                                            ['value' => 1, 'label' => __('Week 1')],
                                            ['value' => 2, 'label' => __('Week 2')],
                                            ['value' => 3, 'label' => __('Week 3')],
                                            ['value' => 4, 'label' => __('Week 4')],
                                            ['value' => 5, 'label' => __('Week 5')],
                                            ['value' => 6, 'label' => __('Week 6')],
                                        ],
                                        'dataType' => Text::NAME,
                                        'label' => __('Weeks of Month'),
                                        'enableLabel' => true,
                                        'dataScope' => 'week_month',
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
}
