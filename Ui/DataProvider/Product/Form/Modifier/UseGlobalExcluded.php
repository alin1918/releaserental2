<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

/**
 * Class GiftMessageDataProvider
 */
class UseGlobalExcluded extends AbstractModifier
{
    const FIELD_MESSAGE_AVAILABLE = 'sirent_global_exclude_dates';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @param LocatorInterface                     $locator
     * @param ArrayManager                         $arrayManager
     * @param ScopeConfigInterface                 $scopeConfig
     * @param \SalesIgniter\Rental\Helper\Calendar $helperCalendar
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        ScopeConfigInterface $scopeConfig,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeConfig = $scopeConfig;
        $this->helperCalendar = $helperCalendar;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->customize($meta);
    }

    /**
     * Customization of allow gift message field
     *
     * @param array $meta
     *
     * @return array
     */
    protected function customize(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . static::FIELD_MESSAGE_AVAILABLE);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . static::FIELD_MESSAGE_AVAILABLE,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(static::FIELD_MESSAGE_AVAILABLE, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $tooltip = [
            'description' => __(
                'If set to yes Global Excluded Dates defined in config will be used'
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
                    static::FIELD_MESSAGE_AVAILABLE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => static::FIELD_MESSAGE_AVAILABLE,
                                    'tooltip' => $tooltip,
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'component' => 'SalesIgniter_Rental/js/form/element/disable-toggle-excluded',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }
}
