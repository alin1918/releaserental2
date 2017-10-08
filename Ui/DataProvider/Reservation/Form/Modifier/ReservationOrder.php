<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Ui\DataProvider\Reservation\Form\Modifier;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\ReservationOrdersInterface;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReservationOrder extends AbstractModifier
{
    const DATA_SCOPE = '';
    const DATA_SCOPE_RESERVATIONS = 'reservations';
    const GROUP_RESERVATIONS = 'reservations';

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
     * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
     */
    private $reservationOrdersRepository;

    /**
     * @param UrlInterface                                                  $urlBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                  $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
     * @param \Magento\Framework\App\RequestInterface                       $request
     * @param string                                                        $scopeName
     * @param string                                                        $scopePrefix
     */
    public function __construct(
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ReservationOrdersRepositoryInterface $reservationOrdersRepository,
        \Magento\Framework\App\RequestInterface $request,
        $scopeName = '',
        $scopePrefix = ''
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeName = $scopeName;
        $this->scopePrefix = $scopePrefix;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reservationOrdersRepository = $reservationOrdersRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_RESERVATIONS => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_RESERVATIONS => $this->getStructure(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Reservations'),
                                'collapsible' => true,
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        $dataScope = self::DATA_SCOPE_RESERVATIONS;
        $idEditedOrder = $this->request->getParam('id');
        $this->searchCriteriaBuilder->addFilter('main_table.order_id', $idEditedOrder);
        $this->searchCriteriaBuilder->addFilter('parent_id', 0);
        $criteria = $this->searchCriteriaBuilder->create();
        $items = $this->reservationOrdersRepository->getList($criteria)->getItems();

        foreach ($items as $item) {
            $data[$idEditedOrder][$dataScope][] = $this->fillData($item);
        }
        //$data['data']['items'] = $data[$dataScope];
        return $data;
    }

    /**
     * Prepare data column
     *
     *
     * @param \SalesIgniter\Rental\Model\ReservationOrdersInterface $reservation
     *
     * @return array
     */
    protected function fillData(ReservationOrdersInterface $reservation)
    {
        return [
            'reservationorder_id' => $reservation->getReservationorderId(),
            'reservation_id' => $reservation->getReservationorderId(),
            'is_shipped' => $reservation->getQtyShipped() > 0 ? 1 : 0,
            'start_date' => $reservation->getStartDate(),
            'end_date' => $reservation->getEndDate(),
            'product_id' => $reservation->getProductId(),
            'qty' => $reservation->getQty(),
            'comments' => $reservation->getComments(),

        ];
    }

    /**
     * Retrieve all data scopes
     *
     * @return array
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_RESERVATIONS,
        ];
    }

    /**
     * Get dynamic rows structure
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
                        'addButton' => false,
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
                        'reservationorder_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Reservation Id'),
                                        'enableLabel' => false,
                                        'visible' => false,
                                        'dataScope' => 'reservationorder_id',
                                    ],
                                ],
                            ],
                        ],
                        'reservation_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Reservation Id'),
                                        'enableLabel' => false,
                                        'visible' => false,
                                        'dataScope' => 'reservation_id',
                                    ],
                                ],
                            ],
                        ],
                        'is_shipped' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/shipped',
                                        'class' => 'SalesIgniter\Rental\Ui\Component\Form\Source\ShippedData',
                                        'label' => __('Is Shipped'),
                                        'enableLabel' => false,
                                        'visible' => false,
                                        'dataScope' => 'is_shipped',
                                    ],
                                ],
                            ],
                        ],
                        'start_date' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/datetime',
                                        'formElement' => Input::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('Start Date'),
                                        'enableLabel' => true,
                                        'dataScope' => 'start_date',
                                    ],
                                ],
                            ],
                        ],
                        'end_date' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/datetime',
                                        'formElement' => Input::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('End Date'),
                                        'enableLabel' => true,
                                        'dataScope' => 'end_date',
                                    ],
                                ],
                            ],
                        ],
                        'qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Quantity'),
                                        'enableLabel' => true,
                                        'dataScope' => 'qty',
                                    ],
                                ],
                            ],
                        ],
                        'product_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/productsearcher',
                                        'class' => 'SalesIgniter\Rental\Ui\Component\Form\Source\ProductSearchUrl',
                                        'dataType' => Number::NAME,
                                        'label' => __('Product'),
                                        'enableLabel' => true,
                                        'dataScope' => 'product_id',
                                    ],
                                ],
                            ],
                        ],
                        'comments' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Textarea::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Comments'),
                                        'enableLabel' => true,
                                        'dataScope' => 'comments',
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
     * Retrieve grid
     *
     * @param string $scope
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getGrid($scope)
    {
        $dataProvider = $scope . '_product_listing';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $dataProvider,
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'status' => 'status_text',
                            'attribute_set' => 'attribute_set_text',
                            'sku' => 'sku',
                            'price' => 'price',
                            'thumbnail' => 'thumbnail_src',
                        ],
                        'links' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                        ],
                        'sortOrder' => 2,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => $this->fillMeta(),
                ],
            ],
        ];
    }

    /**
     * Retrieve meta column
     *
     * @return array
     */
    protected function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', false, __('ID'), 0),
            'thumbnail' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'dataType' => Text::NAME,
                            'dataScope' => 'thumbnail',
                            'fit' => true,
                            'label' => __('Thumbnail'),
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ],
            'name' => $this->getTextColumn('name', false, __('Name'), 20),
            'status' => $this->getTextColumn('status', true, __('Status'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'sku' => $this->getTextColumn('sku', true, __('SKU'), 50),
            'price' => $this->getTextColumn('price', true, __('Price'), 60),
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 70,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Number::NAME,
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 80,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve text column structure
     *
     * @param string $dataScope
     * @param bool   $fit
     * @param Phrase $label
     * @param int    $sortOrder
     *
     * @return array
     */
    protected function getTextColumn($dataScope, $fit, Phrase $label, $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'component' => 'Magento_Ui/js/form/element/text',
                        'dataType' => Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $column;
    }
}
