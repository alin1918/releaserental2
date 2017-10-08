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
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

/**
 * Class GiftMessageDataProvider
 */
class DisableShipping extends AbstractModifier
{
    const FIELD_MESSAGE_AVAILABLE = 'sirent_disable_shipping';

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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $value = '';

        if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_MESSAGE_AVAILABLE])) {
            $value = $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_MESSAGE_AVAILABLE];
        }

        if ('' === $value || $value == \SalesIgniter\Rental\Helper\Data::USE_CONFIG_DEFAULT) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT][static::FIELD_MESSAGE_AVAILABLE] =
                $this->helperCalendar->getDisabledShipping(null, true);
            $data[$modelId][static::DATA_SOURCE_DEFAULT]['use_config_' . static::FIELD_MESSAGE_AVAILABLE] = '1';
        }

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
                'If set to yes shipping is disabled for this product'
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
                                    'imports' => [
                                        'disabled' => '${$.parentName}.use_config_'
                                            . static::FIELD_MESSAGE_AVAILABLE
                                            . ':checked',
                                    ],
                                    'tooltip' => $tooltip,
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                ],
                            ],
                        ],
                    ],
                    'use_config_' . static::FIELD_MESSAGE_AVAILABLE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => 'use_config_' . static::FIELD_MESSAGE_AVAILABLE,
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
}
