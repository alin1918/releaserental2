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
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;
use SalesIgniter\Rental\Model\Attribute\Backend\PeriodType;
use SalesIgniter\Rental\Model\Attribute\Sources\SerialStatus;

/**
 * Customize Reservation Advanced Pricing
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SerialNumbers extends AbstractModifier
{
    const CODE_SERIALS = 'sirent_serial_numbers';

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

        $this->customize();

        return $this->meta;
    }

    /**
     * Customize rental price field
     *
     * @return $this
     */
    protected function customize()
    {
        $codePath = $this->arrayManager->findPath(
            self::CODE_SERIALS,
            $this->meta,
            null,
            'children'
        );

        if ($codePath) {
            $this->meta = $this->arrayManager->merge(
                $codePath,
                $this->meta,
                $this->getStructure($codePath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($codePath, 0, -3)
                . '/' . self::CODE_SERIALS,
                $this->meta,
                $this->arrayManager->get($codePath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($codePath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Generates EAN-13 code
     *
     * @return string
     */
    private function generateRandomEAN()
    {
        $number = random_int(111, 999999999);
        $code = '200' . str_pad($number, 9, '0');
        $weightFlag = true;
        $sum = 0;
        // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
        // loop backwards to make the loop length-agnostic. The same basic functionality
        // will work for codes of different lengths.
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightFlag ? 3 : 1);
            $weightFlag = !$weightFlag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }

    /**
     * Get dynamic rows structure
     *
     * @param string $codePath
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getStructure($codePath)
    {
        //TODO add generate button should use EAN-13 codes
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Serial Numbers'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        /*'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'template' => 'ui/dynamic-rows/templates/default',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',*/
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get($codePath . '/arguments/data/config/sortOrder', $this->meta),
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
                        'serialnumber' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Serial Number'),
                                        'enableLabel' => true,
                                        'dataScope' => 'serialnumber',
                                    ],
                                ],
                            ],
                        ],
                        'notes' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Textarea::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Notes'),
                                        'enableLabel' => true,
                                        'dataScope' => 'notes',
                                    ],
                                ],
                            ],
                        ],
                        'date_acquired' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'component' => 'SalesIgniter_Rental/js/form/element/datetime',
                                        'formElement' => Input::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('Date Acquired'),
                                        'enableLabel' => true,
                                        'dataScope' => 'date_acquired',
                                    ],
                                ],
                            ],
                        ],
                        'status' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Select::NAME,
                                        'dataType' => Text::NAME,
                                        'options' => SerialStatus::getOptionsArray(),
                                        'label' => __('Status'),
                                        'enableLabel' => true,
                                        'dataScope' => 'status',
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
