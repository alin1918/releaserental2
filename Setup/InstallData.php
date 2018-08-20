<?php

namespace SalesIgniter\Rental\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $catalogSetupFactory;

    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->catalogSetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

        // Add Rentals Tab To Product Edit for Rental Products
        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $catalogSetup->getDefaultAttributeSetId($entityTypeId);
        $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Rental', 60);


        /** Add Rental fields in order of Global (yes / no)  -  Number -  Type
         *  These will go under the Rental tab
         */

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_use_times', [
            'label' => 'Show Time Of Day Drop Down For Start and End Date',
            'group' => 'Rental',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '0',
            'type' => 'int',
            'sort_order' => 1
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_use_times_grid', [
            'label' => 'Show Time Of Day Busy Times Grid On Product Page',
            'group' => 'Rental',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '0',
            'type' => 'int',
            'sort_order' => 2
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_min_global', [
            'label' => 'Use Global Config for Minimum Period',
            'group' => 'Rental',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'sort_order' => 10
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_min_number', [
            'label' => 'Minimum Period',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'sort_order' => 11
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_min_type', [
            'label' => 'Minimum Period Type',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\PeriodType',
            'sort_order' => 12
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_max_global', [
            'label' => 'Use Global Config for Maximum Period',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 20
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_max_number', [
            'label' => 'Maximum Period',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'sort_order' => 21
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_max_type', [
            'label' => 'Maximum Period Type',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '1',
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\PeriodType',
            'sort_order' => 22
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_before_global', [
            'label' => 'Use Global Config for Turnover Before',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 30
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_before_number', [
            'label' => 'Rental Turnover Before',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'sort_order' => 31
        ]);


        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_before_type', [
            'label' => 'Rental Turnover Before Type',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\PeriodType',
            'sort_order' => 32
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_after_global', [
            'label' => 'Use Global Config for Turnover After',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 40
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_after_number', [
            'label' => 'Rental Turnover After',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'sort_order' => 41
        ]);


        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_turnover_after_type', [
            'label' => 'Rental Turnover After Type',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\PeriodType',
            'sort_order' => 42
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_excluded_days_global', [
            'label' => 'Use Global Config for Excluded Days of the Week',
            'group' => 'Rental',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 50
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_excluded_days', [
            'label' => 'Excluded Days of the Week',
            'group' => 'Rental',
            'input' => 'multiselect',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'text',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDaysWeek',
            'sort_order' => 51
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_has_shipping', [
            'label' => 'Enable Shipping For This Product?',
            'group' => 'Rental',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 1,
            'type' => 'int',
            'sort_order' => 20
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_future_limit', [
            'label' => 'Future Reservation Limit In Days (0 for no limit)',
            'group' => 'Rental',
            'input' => 'text',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 1,
            'type' => 'int',
            'sort_order' => 20
        ]);

        // No Tab Is Hidden From Input

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_inv_bydate_serialized', [
            'label' => 'Inventory Serialized By Date',
            'visible_on_front' => false,
            'user_defined' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => '',
            'type' => 'text',
            'sort_order' => 20
        ]);

        // Advanced Pricing group

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_deposit_global', [
            'label' => 'Use Global Config for Deposit?',
            'group' => 'Prices',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'sort_order' => 10
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_deposit', [
            'label' => 'Deposit',
            'group' => 'Prices',
            'input' => 'price',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'decimal',
            'sort_order' => 11
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_damage_waiver_global', [
            'label' => 'Use Global Config for Damage Waiver Amount',
            'note' => 'Either fixed amount or add % like 10% for percentage',
            'group' => 'Prices',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'sort_order' => 20
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_damage_waiver', [
            'label' => 'Damage Waiver Amount',
            'note' => 'Either fixed amount or add % like 10% for percentage',
            'group' => 'Prices',
            'input' => 'price',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'decimal',
            'sort_order' => 21
        ]);

        // General Group

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_pricingtype', [
            'label' => 'Pricing Type',
            'group' => 'General',
            'input' => 'select',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 1,
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\PricingType',
            'sort_order' => 5
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_price', [
            'label' => 'Price',
            'group' => 'General',
            'input' => 'text',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'type' => 'decimal',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\RentalPrice',
            'sort_order' => 6
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_bundle_price_type', [
            'label' => 'Bundle Price Type',
            'group' => 'General',
            'input' => 'text',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 1,
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\BundlePriceType',
            'sort_order' => 7
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_quantity', [
            'label' => 'Qty',
            'group' => 'General',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 1,
            'type' => 'int',
            'sort_order' => 7
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_rental_type', [
            'label' => 'Rental Product Type',
            'group' => 'General',
            'input' => 'select',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\RentalType',
            'sort_order' => 8
        ]);

        // Advanced Inventory Group

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_serial_numbers_use', [
            'label' => 'Use Serial Numbers?',
            'group' => 'General',
            'input' => 'boolean',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'int',
            'sort_order' => 20
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, 'sirent_serial_numbers', [
            'label' => 'Serial Numbers',
            'group' => 'General',
            'input' => 'text',
            'visible_on_front' => false,
            'required' => false,
            'global' => CatalogAttribute::SCOPE_STORE,
            'apply_to' => 'sirent',
            'default' => 0,
            'type' => 'text',
            'source' => 'SalesIgniter\Rental\Model\Attribute\Backend\SerialNumbers',
            'sort_order' => 21
        ]);      
        
        $attrs = [
            [
                'code'          => 'sirent_hour_next_day',
                'label'         => 'Hour For Next Day',
                'sort_order'    => 70,
            ],
            [
                'code'          => 'sirent_store_open_time',
                'label'         => 'Store Open Time',
                'sort_order'    => 71,
            ],
            [
                'code'          => 'sirent_store_close_time',
                'label'         => 'Store Close Time',
                'sort_order'    => 72,
            ],
            [
                'code'          => 'sirent_store_open_monday',
                'label'         => 'Store Open Time Monday',
                'sort_order'    => 73,
            ],
            [
                'code'          => 'sirent_store_close_monday',
                'label'         => 'Store Close Time Monday',
                'sort_order'    => 74,
            ],
            [
                'code'          => 'sirent_store_open_tuesday',
                'label'         => 'Store Open Time Tuesday',
                'sort_order'    => 75,
            ],
            [
                'code'          => 'sirent_store_close_tuesday',
                'label'         => 'Store Close Time Tuesday',
                'sort_order'    => 76,
            ],
            [
                'code'          => 'sirent_store_open_wednesday',
                'label'         => 'Store Open Time Wednesday',
                'sort_order'    => 77,
            ],
            [
                'code'          => 'sirent_store_close_wednesday',
                'label'         => 'Store Close Time Wednesday',
                'sort_order'    => 78,
            ],
            [
                'code'          => 'sirent_store_open_thursday',
                'label'         => 'Store Open Time Thursday',
                'sort_order'    => 79,
            ],
            [
                'code'          => 'sirent_store_close_thursday',
                'label'         => 'Store Close Time Thursday',
                'sort_order'    => 80,
            ],
            [
                'code'          => 'sirent_store_open_friday',
                'label'         => 'Store Open Time Friday',
                'sort_order'    => 81,
            ],
            [
                'code'          => 'sirent_store_close_friday',
                'label'         => 'Store Close Time Friday',
                'sort_order'    => 82,
            ],
            [
                'code'          => 'sirent_store_open_saturday',
                'label'         => 'Store Open Time Saturday',
                'sort_order'    => 83,
            ],
            [
                'code'          => 'sirent_store_close_saturday',
                'label'         => 'Store Close Time Saturday',
                'sort_order'    => 84,
            ],
            [
                'code'          => 'sirent_store_open_sunday',
                'label'         => 'Store Open Time Sunday',
                'sort_order'    => 85,
            ],
            [
                'code'          => 'sirent_store_close_sunday',
                'label'         => 'Store Close Time Sunday',
                'sort_order'    => 86,
            ],
        ];

        foreach ($attrs as $attr) {
                $catalogSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attr['code'],
                [
                    'type'              => 'text',
                    'group'             => 'Rental',                    
                    'input'             => 'text',
                    'label'             => $attr['label'],
                    'global'            => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'required'          => false,
                    'visible_on_front'  => false,
                    'apply_to'          => 'sirent',   
                    'backend'           => 'SalesIgniter\Rental\Model\Attribute\Backend\Time',
                    'sort_order'        => $attr['sort_order'],                    
                ]
            );                  
        }             
    }
    //
    //test
}
