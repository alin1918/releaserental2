<?php
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

class StoreHours extends AbstractModifier
{    
    protected $locator;

    protected $arrayManager;

    private $localeCurrency;

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }
    
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        
        $attrs = [
            'sirent_hour_next_day',        
            'sirent_store_open_time',        
            'sirent_store_close_time',        
            'sirent_store_open_monday',        
            'sirent_store_close_monday',        
            'sirent_store_open_tuesday',        
            'sirent_store_close_tuesday',        
            'sirent_store_open_wednesday',        
            'sirent_store_close_wednesday',        
            'sirent_store_open_thursday',        
            'sirent_store_close_thursday',        
            'sirent_store_open_friday',        
            'sirent_store_close_friday',        
            'sirent_store_open_saturday',        
            'sirent_store_close_saturday',        
            'sirent_store_open_sunday',        
            'sirent_store_close_sunday',                    
        ];
        
        foreach ($attrs as $attr) {
            if (!isset($data[$modelId][static::DATA_SOURCE_DEFAULT][$attr])) {
                $data[$modelId][static::DATA_SOURCE_DEFAULT]['use_config_' . $attr] = '1';            
            } else {

                $data = $this->parseTimeData($data, $attr);
            }                    
        }
        
        return $data;
    }    

    public function parseTimeData($data, $code) 
    {
        $modelId = $this->locator->getProduct()->getId();
        
        $value = $data[$modelId][static::DATA_SOURCE_DEFAULT][$code];

        preg_match("/(?P<hour>\d{2}):(?P<minute>\d{2}):(?P<second>\d{2})/", $value, $results);	            

        if (count($results['hour']) > 0
            && count($results['minute']) > 0
            && count($results['second']) > 0
        ) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT]["{$code}_hour"] = $results['hour'];
            $data[$modelId][static::DATA_SOURCE_DEFAULT]["{$code}_minute"] = $results['minute'];
            $data[$modelId][static::DATA_SOURCE_DEFAULT]["{$code}_second"] = $results['second'];                
        }
        
        return $data;
    }
    
    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeTimeMeta($meta, 'sirent_hour_next_day');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_time');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_time');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_monday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_monday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_tuesday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_tuesday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_wednesday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_wednesday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_thursday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_thursday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_friday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_friday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_saturday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_saturday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_open_sunday');        
        $meta = $this->customizeTimeMeta($meta, 'sirent_store_close_sunday');        
        
        return $meta;
    }    
    
    protected function customizeTimeMeta(array $meta, $code)
    {
        $path = $this->arrayManager->findPath($code, $meta, null, 'children');

        if ($path) {
            
            $meta = $this->arrayManager->merge(
                $path . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataType' => 'text',
                    'formElement' => Form\Element\Select::NAME,
                    'componentType' => Form\Field::NAME,
                    'dataScope' => $code . '_hour',
                    'additionalClasses' => 'admin__field-x-small',
                    'options' => $this->getHourOptions(),
                    'value' => null,
                    'imports' => [
                        'disabled' =>
                            '${$.parentName}.use_config_'
                            . $code
                            . ':checked',
                    ],
                    'validation' => [
                        'validate-digits' => true,
                        'validate-no-empty' => true
                    ],                    
                ]
            );    
            
            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . $code,
                $meta,
                null,
                'children'
            );
            
            $meta = $this->arrayManager->merge($containerPath . static::META_CONFIG_PATH, $meta, [
                'component' => 'Magento_Ui/js/form/components/group',
            ]);                        
            
            $meta = $this->arrayManager->merge(
                $containerPath,
                $meta,
                [
                    'children' => [
                        $code . '_minute' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => 'text',
                                        'formElement' => Form\Element\Select::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'dataScope' => $code . '_minute',
                                        'additionalClasses' => 'admin__field-x-small semicolon-before',                    
                                        'label' => '',
                                        'options' => $this->getMinuteOptions(),
                                        'imports' => [
                                            'disabled' =>
                                                '${$.parentName}.use_config_'
                                                . $code
                                                . ':checked',
                                        ],  
                                        'validation' => [
                                            'validate-digits' => true,
                                            'validate-no-empty' => true
                                        ],                                                            
                                    ],
                                ],
                            ],
                        ],
                        $code . '_second' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => 'text',
                                        'formElement' => Form\Element\Select::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'dataScope' => $code . '_second',
                                        'additionalClasses' => 'admin__field-x-small semicolon-before',                    
                                        'label' => '',
                                        'options' => $this->getSecondOptions(),
                                        'imports' => [
                                            'disabled' =>
                                                '${$.parentName}.use_config_'
                                                . $code
                                                . ':checked',
                                        ],  
                                        'validation' => [
                                            'validate-digits' => true,
                                            'validate-no-empty' => true
                                        ],                                                            
                                    ],
                                ],
                            ],
                        ],
                        $code . '_tooltip' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Form\Element\Hidden::NAME,                                        
                                        'componentType' => Form\Field::NAME,  
                                        'additionalClasses' => 'custom-hidden',
                                        'tooltip' => [
                                            'description' => __(
                                                'Format is HH:MM:SS'
                                            ),
                                        ],   
                                    ],
                                ],
                            ],
                        ],
                        'use_config_' . $code => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => 'number',
                                        'formElement' => Form\Element\Checkbox::NAME,
                                        'componentType' => Form\Field::NAME,
                                        'description' => __('Use Config Settings'),
                                        'dataScope' => 'use_config_' . $code,
                                        'valueMap' => [
                                            'false' => '0',
                                            'true' => '1',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            );
        }        
        
        return $meta;
    }
    
    protected function getHourOptions()
    {
        $range = array_map(function($el) {
            return ['label' => sprintf('%02d', $el), 'value' => sprintf('%02d', $el)];
        }, range(0, 23));                              

        return array_merge([['label' => 'HH', 'value' => null]], $range);            
    }
    
    protected function getMinuteOptions()
    {
        $range = array_map(function($el) {
            return ['label' => sprintf('%02d', $el), 'value' => sprintf('%02d', $el)];
        }, range(0, 59));                              

        return array_merge([['label' => 'MM', 'value' => null]], $range);            
    }
    
    protected function getSecondOptions()
    {
        $range = array_map(function($el) {
            return ['label' => sprintf('%02d', $el), 'value' => sprintf('%02d', $el)];
        }, range(0, 59));                              

        return array_merge([['label' => 'SS', 'value' => null]], $range);            
    }
}
