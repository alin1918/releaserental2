<?php

namespace SalesIgniter\Rental\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script.
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface {
	/**
	 * Catalog setup factory.
	 *
	 * @var EavSetupFactory
	 */
	private $_eavSetupFactory;

	/**
	 * @var \Magento\Framework\Logger\Monolog
	 */
	protected $_logger;

	/**
	 * @var
	 */
	protected $_currentVersion;

	/**
	 * @var EavSetup
	 */
	protected $_eavSetup;

	/**
	 * UpgradeData constructor.
	 *
	 * @param EavSetupFactory                   $_eavSetupFactory
	 * @param \Magento\Framework\Logger\Monolog $logger
	 */
	public function __construct(
		EavSetupFactory $_eavSetupFactory,
		\Magento\Framework\Logger\Monolog $logger
	) {
		$this->_eavSetupFactory = $_eavSetupFactory;

		$this->_logger = $logger;
		$this->_logger->pushHandler( new \Monolog\Handler\StreamHandler( BP . '/var/log/rental_upgrade_data.log' ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();

		$this->_currentVersion = $context->getVersion();
		$this->_logger->addDebug( 'Current Version: ' . $this->_currentVersion );

		$this->_eavSetup = $this->_eavSetupFactory->create( [ 'setup' => $setup ] );

		if ( $this->shouldProcessUpdate( '1.0.20160224' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160224' );

			$this->updateProductEavAttributes( [
				'sirent_price'                 => [
					'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\RentalPrice',
					'apply_to'      => 'sirent,configurable,bundle',
					'source_model'  => '',
				],
				'sirent_rental_type'           => [
					'apply_to' => 'sirent,configurable,bundle',
				],
				'sirent_bundle_price_type'     => [
					'source_model'   => 'SalesIgniter\Rental\Model\Attribute\Backend\BundlePriceType',
					'apply_to'       => 'bundle',
					'frontend_input' => 'select',
				],
				'sirent_min_type'              => [
					'frontend_input' => 'select',
				],
				'sirent_max_type'              => [
					'frontend_input' => 'select',
				],
				'sirent_turnover_before_type'  => [
					'frontend_input' => 'select',
				],
				'sirent_turnover_after_type'   => [
					'frontend_input' => 'select',
				],
				'sirent_inv_bydate_serialized' => [
					'visible'    => false,
					'system'     => false,
					'is_visible' => false,
					'is_system'  => false,
				],
			] );

			$this->addProductEavAttributes( [
				'sirent_buyout_onproduct' => [
					'label'            => 'Buyout on product view page',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent',
					'default'          => 1,
					'type'             => 'int',
					'sort_order'       => 50,
				],
				'sirent_enable_buyout'    => [
					'label'            => 'Enable Buyout',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 60,
				],
				'sirent_buyoutprice'      => [
					'label'            => 'Buyout Price',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent',
					'default'          => 1,
					'type'             => 'decimal',
					'sort_order'       => 70,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160302' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160302' );

			$this->updateProductEavAttribute( 'sirent_serial_numbers', [
				'backend_type' => 'int',
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160630' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160630' );

			$this->updateProductEavAttribute( 'sirent_price', [
				'backend_type'  => 'decimal',
				'default'       => null,
				'default_value' => null,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160705' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160705' );

			$this->addProductEavAttributes( [
				'sirent_global_paddingdays'     => [
					'label'            => 'Use Global Config for Use Global Padding Days',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => 0,
					'type'             => 'int',
					'input'            => 'boolean',
					'sort_order'       => 50,
				],
				'sirent_paddingdays'            => [
					'label'            => 'Padding Days',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '0',
					'type'             => 'int',
					'sort_order'       => 21,
				],
				'sirent_excludeddays_startglob' => [
					'label'            => 'Use Global Config for Excluded Days of the Week for Start Date',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => 0,
					'type'             => 'int',
					'input'            => 'boolean',
					'sort_order'       => 50,
				],
				'sirent_excludeddays_start'     => [
					'label'            => 'Excluded Days of the Week for Start Date',
					'group'            => 'Rental',
					'input'            => 'multiselect',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => 0,
					'type'             => 'text',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDaysWeek',
					'sort_order'       => 51,
				],
				'sirent_excludeddays_endglob'   => [
					'label'            => 'Use Global Config for Excluded Days of the Week for End Date',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => 0,
					'type'             => 'int',
					'input'            => 'boolean',
					'sort_order'       => 50,
				],
				'sirent_excludeddays_end'       => [
					'label'            => 'Excluded Days of the Week for End Date',
					'group'            => 'Rental',
					'input'            => 'multiselect',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => 0,
					'type'             => 'text',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDaysWeek',
					'sort_order'       => 51,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160806' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160806' );

			$this->removeProductEavAttributes( [
				'sirent_global_paddingdays',
				'sirent_paddingdays',
				'sirent_padding_days',
				'sirent_min_number',
				'sirent_min_type',
				'sirent_max_type',
				'sirent_max_number',
				'sirent_turnover_after_type',
				'sirent_turnover_after_number',
				'sirent_turnover_before_type',
				'sirent_turnover_before_number',

				'sirent_deposit_global',
				'sirent_deposit',
				'sirent_damage_waiver',
				'sirent_damage_waiver_global',

				'sirent_excludeddays_endglob',
				'sirent_excludeddays_startglob',
				'sirent_excluded_days_global',
				'sirent_turnover_after_global',
				'sirent_turnover_before_global',
				'sirent_max_global',
				'sirent_min_global',

				'sirent_use_times',
				'sirent_use_times_grid',
				'sirent_global_padding',
				'sirent_use_global_padding_days',
				'sirent_future_limit',
				'sirent_has_shipping',

				'sirent_configurable_price_type',
				//'sirent_bundle_price_type'
			] );

			$this->addProductEavAttributes( [
				'sirent_disable_shipping'        => [
					'label'            => 'Disable Shipping:',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 19,
				],
				'sirent_use_times'               => [
					'label'            => 'Product Use Time:',
					'group'            => 'General',
					'input'            => 'select',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'int',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Backend\UseTimes',
					'sort_order'       => 20,
				],
				'sirent_padding'                 => [
					'label'            => 'Padding',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 21,
				],
				'sirent_min'                     => [
					'label'            => 'Minimum Period Allowed for Rent',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 22,
				],
				'sirent_max'                     => [
					'label'            => 'Maximum Period Allowed For Rent',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 23,
				],
				'sirent_turnover_before'         => [
					'label'            => 'Turnover Before',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 24,
				],
				'sirent_turnover_after'          => [
					'label'            => 'Turnover After',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 25,
				],
				'fixed_length'                   => [
					'label'            => 'Fixed Rental Length',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 26,
				],
				'sirent_configurable_price_type' => [
					'label'            => 'Configurable Price Type',
					'group'            => 'General',
					'input'            => 'select',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'configurable',
					'default'          => 1,
					'type'             => 'int',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Backend\ConfigurablePriceType',
					'sort_order'       => 7,
				],
				'sirent_deposit'                 => [
					'label'            => 'Deposit',
					'group'            => 'Rental',
					'input'            => 'price',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'decimal',
					'sort_order'       => 11,
				],
				'sirent_damage_waiver'           => [
					'label'            => 'Damage Waiver Amount',
					'note'             => 'Either fixed amount or add % like 10% for percentage',
					'group'            => 'Rental',
					'input'            => 'price',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'decimal',
					'sort_order'       => 21,
				],
				'sirent_excluded_dates'          => [
					'label'            => 'Excluded Dates for Product',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => null,
					'type'             => 'text',
					'source'           => '',
					'backend'          => 'Magento\Eav\Model\Entity\Attribute\Backend\Serialized',
					'sort_order'       => 27,
				],
				'sirent_future_limit'            => [
					'label'            => 'Future Reservation Limit',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent',
					'default'          => '',
					'type'             => 'text',
					'sort_order'       => 28,
				],
			] );

			$this->updateProductEavAttribute( 'sirent_serial_numbers', [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\SerialNumbers',
				'apply_to'      => 'sirent,configurable,bundle',
				'source_model'  => '',
				'backend_type'  => 'decimal',
				'default'       => null,
				'default_value' => null,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160813' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160813' );

			$this->removeProductEavAttributes( [
				'sirent_inv_bydate_serialized',
				'sirent_global_exclude_dates',
				'sirent_excluded_dates',
			] );

			$this->addProductEavAttributes( [
				'sirent_inv_bydate_serialized' => [
					'label'            => 'Inventory Serialized By Date',
					'visible_on_front' => false,
					'user_defined'     => false,
					'required'         => false,
					'visible'          => false,
					'system'           => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent',
					'default'          => null,
					'type'             => 'text',
					'sort_order'       => 20,
				],
				'sirent_allow_overbooking'     => [
					'label'            => 'Allow Overbooking:',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 19,
				],
				'sirent_global_exclude_dates'  => [
					'label'            => 'Use Global Exclude Dates',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 1,
					'type'             => 'int',
					'sort_order'       => 26,
				],
				'sirent_excluded_dates'        => [
					'label'            => 'Excluded Dates for Product',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'default'          => null,
					'type'             => 'text',
					'source'           => '',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDates',
					'sort_order'       => 27,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160817' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160817' );

			$this->removeProductEavAttributes( [
				'fixed_length',
				'sirent_fixed_length',
				'sirent_allow_extend_order',
			] );

			$this->addProductEavAttributes( [
				'sirent_allow_extend_order' => [
					'label'            => 'Allow Extend Order:',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 28,
				],
				'sirent_fixed_length'       => [
					'label'            => 'Fixed Rental Length',
					'group'            => 'Rental',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
					'default'          => null,
					'type'             => 'text',
					'sort_order'       => 26,
				],
			] );

			$TextTypeSettings = [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
				'default'       => null,
				'default_value' => null,
				'backend_type'  => 'text',
			];

			$BooleanTypeSettings = [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
				'default'       => 0,
				'default_value' => 0,
				'backend_type'  => 'int',
			];

			$MultiselectTypeSettings = [
				'apply_to'      => 'sirent,configurable,bundle',
				'source_model'  => 'SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeek',
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDays',
				'default'       => null,
				'default_value' => null,
				'backend_type'  => 'text',
			];

			$IntegerTypeSettings = [
				'backend_type'  => 'int',
				'default'       => 0,
				'default_value' => 0,
			];

			$this->updateProductEavAttributes( [
				'sirent_min'             => $TextTypeSettings,
				'sirent_max'             => $TextTypeSettings,
				'sirent_padding'         => $TextTypeSettings,
				'sirent_turnover_before' => $TextTypeSettings,
				'sirent_turnover_after'  => $TextTypeSettings,
				'sirent_fixed_length'    => $TextTypeSettings,
				'sirent_future_limit'    => $TextTypeSettings,
				'sirent_deposit'         => $TextTypeSettings,
				'sirent_damage_waiver'   => $TextTypeSettings,

				'sirent_allow_overbooking'  => $BooleanTypeSettings,
				'sirent_allow_extend_order' => $BooleanTypeSettings,
				'sirent_disable_shipping'   => $BooleanTypeSettings,

				'sirent_excluded_days'      => array_merge( [], $MultiselectTypeSettings, [
					'sort'       => 27,
					'sort_order' => 27,
				] ),
				'sirent_excludeddays_start' => $MultiselectTypeSettings,
				'sirent_excludeddays_end'   => $MultiselectTypeSettings,

				'sirent_rental_type'       => array_merge( [], $IntegerTypeSettings, [
					'source_model' => 'SalesIgniter\Rental\Model\Attribute\Sources\RentalType',
				] ),
				'sirent_bundle_price_type' => array_merge( [], $IntegerTypeSettings, [
					'source_model' => 'SalesIgniter\Rental\Model\Attribute\Sources\BundlePriceType',
				] ),
				'sirent_pricingtype'       => array_merge( [], $IntegerTypeSettings, [
					'source_model' => 'SalesIgniter\Rental\Model\Attribute\Sources\PricingType',
				] ),
				'sirent_use_times'         => array_merge( [], $IntegerTypeSettings, [
					'source_model' => 'SalesIgniter\Rental\Model\Attribute\Sources\UseTimes',
				] ),

				'sirent_global_exclude_dates' => [
					'sort'       => 28,
					'sort_order' => 28,
				],

				'sirent_serial_numbers_use' => [
					'apply_to'   => 'sirent,configurable,bundle',
					'sort'       => 8,
					'sort_order' => 8,
				],
				'sirent_serial_numbers'     => [
					'apply_to'   => 'sirent,configurable,bundle',
					'sort'       => 7,
					'sort_order' => 7,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160818' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160818' );

			$this->removeProductEavAttributes( [
				'sirent_enable_buyout',
				'sirent_buyout_price',
			] );

			$this->addProductEavAttributes( [
				'sirent_enable_buyout' => [
					'label'            => 'Enable Buyout',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 60,
				],
				'sirent_buyout_price'  => [
					'label'            => 'Buyout Price',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 0,
					'type'             => 'decimal',
					'sort_order'       => 70,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160819' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160819' );

			$this->removeProductEavAttribute( 'sirent_configurable_price_type' );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160820' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160820' );

			$this->updateProductEavAttributes( [
				'sirent_rental_type'           => [
					'apply_to' => 'sirent,configurable,bundle',
				],
				'sirent_bundle_price_type'     => [
					'source_model'   => 'SalesIgniter\Rental\Model\Attribute\Backend\BundlePriceType',
					'apply_to'       => 'bundle',
					'frontend_input' => 'select',
				],
				'sirent_inv_bydate_serialized' => [
					'visible'       => false,
					'system'        => false,
					'is_visible'    => false,
					'is_system'     => false,
					'default'       => null,
					'default_value' => null,
				],
				'sirent_serial_numbers'        => [
					'backend_type' => 'int',
				],
				'sirent_price'                 => [
					'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\RentalPrice',
					'apply_to'      => 'sirent,configurable,bundle',
					'source_model'  => '',
					'backend_type'  => 'decimal',
					'default'       => null,
					'default_value' => null,
				],
			] );
		}
		if ( $this->shouldProcessUpdate( '1.0.20160831' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160831' );

			$this->updateProductEavAttributes( [
				'sirent_bundle_price_type' => [
					'source_model'   => 'SalesIgniter\Rental\Model\Attribute\Sources\BundlePriceType',
					'apply_to'       => 'bundle',
					'frontend_input' => 'select',
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160901' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160901' );
			$applyTo = explode(
				',',
				$this->_eavSetup->getAttribute( \Magento\Catalog\Model\Product::ENTITY, 'weight', 'apply_to' )
			);
			if ( ! in_array( 'sirent', $applyTo ) ) {
				$applyTo[] = 'sirent';
				$this->updateProductEavAttributes( [
					'weight' => [
						'apply_to' => implode( ',', $applyTo ),
					],
				] );
			}

			$applySettings = [
				'apply_to' => 'sirent,bundle',
			];
			$this->updateProductEavAttributes( [
				'sirent_min'                => $applySettings,
				'sirent_max'                => $applySettings,
				'sirent_padding'            => $applySettings,
				'sirent_turnover_before'    => $applySettings,
				'sirent_turnover_after'     => $applySettings,
				'sirent_fixed_length'       => $applySettings,
				'sirent_future_limit'       => $applySettings,
				'sirent_deposit'            => $applySettings,
				'sirent_damage_waiver'      => $applySettings,
				'sirent_allow_overbooking'  => $applySettings,
				'sirent_allow_extend_order' => $applySettings,
				'sirent_disable_shipping'   => $applySettings,
				'sirent_excluded_days'      => $applySettings,
				'sirent_excludeddays_start' => $applySettings,
				'sirent_excludeddays_end'   => $applySettings,
				'sirent_pricingtype'        => $applySettings,
				'sirent_price'              => $applySettings,
				'sirent_use_times'          => $applySettings,
//                'sirent_buyout_price' => $applySettings,
//                'sirent_enable_buyout' => $applySettings,
//                'sirent_global_exclude_dates' => $applySettings,
//                'sirent_serial_numbers_use' => $applySettings,
//                'sirent_serial_numbers' => $applySettings,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20160902' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20160902' );
			$applySettings = [
				'apply_to' => 'sirent,bundle',
			];
			$this->updateProductEavAttributes( [
				'sirent_excluded_dates' => $applySettings,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20161111' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20161111' );
			$applySettings = [
				'apply_to' => 'sirent,bundle',
			];
			$this->updateProductEavAttributes( [
				//'sirent_buyout_price' => $applySettings,
				'sirent_enable_buyout'        => $applySettings,
				'sirent_global_exclude_dates' => $applySettings,
				'sirent_serial_numbers_use'   => $applySettings,
				'sirent_serial_numbers'       => $applySettings,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20161112' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20161112' );

			$this->removeProductEavAttributes( [
				'sirent_rent_price',
			] );

			$this->addProductEavAttribute( 'sirent_price', [
				'label'            => 'Price',
				'group'            => 'General',
				'input'            => 'text',
				'visible_on_front' => false,
				'required'         => false,
				'global'           => CatalogAttribute::SCOPE_STORE,
				'apply_to'         => 'sirent,bundle',
				'type'             => 'decimal',
				'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\RentalPrice',
				'sort_order'       => 6,
				'default'          => null,
				'default_value'    => null,
				'source_model'     => '',
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20161116' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20161116' );

			$this->removeProductEavAttributes( [
				'sirent_buyout_price',
				'sirent_buyoutprice',
				'sirent_buyout_onproduct',
			] );

			$this->addProductEavAttributes( [
				'sirent_buyout_price' => [
					'label'            => 'Buyout Price',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle',
					'default'          => 0,
					'type'             => 'decimal',
					'sort_order'       => 70,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170216' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170216' );
			$this->removeProductEavAttributes( [
				'sirent_fixed_type',
			] );
			$this->addProductEavAttributes( [
				'sirent_fixed_type' => [
					'label'            => 'Fixed Selection Type',
					'group'            => 'Rental',
					'input'            => 'select',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'default'          => 'disabled',
					'type'             => 'text',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Sources\FixedType',
					'sort_order'       => 27,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170308' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170308' );
			$applyTo = explode(
				',',
				$this->_eavSetup->getAttribute( \Magento\Catalog\Model\Product::ENTITY, 'tax_class_id', 'apply_to' )
			);
			if ( ! in_array( 'sirent', $applyTo ) ) {
				$applyTo[] = 'sirent';
				$this->updateProductEavAttributes( [
					'tax_class_id' => [
						'apply_to' => implode( ',', $applyTo ),
					],
				] );
			}
			$this->removeProductEavAttributes( [
				'sirent_excludeddays_from',
			] );
			$this->addProductEavAttributes( [
				'sirent_excludeddays_from' => [
					'label'            => 'Excluded Days of the Week From',
					'group'            => 'Rental',
					'input'            => 'multiselect',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Sources\ExcludedDaysWeekFrom',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\ExcludedDaysFrom',
					'default'          => null,
					'default_value'    => null,
					'type'             => 'text',
					'sort_order'       => 51,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170310' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170310' );
			$applySettings    = [
				'default'       => 1,
				'default_value' => 1,
			];
			$textTypeSettings = [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
				'default'       => null,
				'default_value' => null,
				'backend_type'  => 'text',
			];
			$applySettings2   = [
				'default'       => 0,
				'default_value' => 0,
			];
			$this->updateProductEavAttributes( [
				'sirent_enable_buyout'        => $applySettings2,
				'sirent_global_exclude_dates' => $applySettings,
			] );
			$this->updateProductEavAttributes( [
				'sirent_fixed_length' => $textTypeSettings,
				'sirent_fixed_type'   => $textTypeSettings,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170526' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170526' );

			$this->removeProductEavAttributes( [
				'sirent_hotel_mode',
			] );

			$this->addProductEavAttributes( [
				'sirent_hotel_mode' => [
					'label'            => 'Enable Hotel Mode',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 60,
				],
			] );
		}
		if ( $this->shouldProcessUpdate( '1.0.20170628' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170628' );
			$textTypeSettings  = [
				'apply_to' => 'sirent,configurable,bundle',
			];
			$textTypeSettings2 = [
				'apply_to' => 'sirent',
			];
			$this->updateProductEavAttributes( [
				'sirent_fixed_length'      => $textTypeSettings,
				'sirent_fixed_type'        => $textTypeSettings,
				'sirent_hotel_mode'        => $textTypeSettings2,
				'sirent_excludeddays_from' => $textTypeSettings2,
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170711' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170711' );

			$this->removeProductEavAttributes( [
				'sirent_always_show',
			] );

			$this->addProductEavAttributes( [
				'sirent_always_show' => [
					'label'            => 'Always Show Calendar',
					'group'            => 'Rental',
					'input'            => 'boolean',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
					'default'          => 0,
					'type'             => 'int',
					'sort_order'       => 60,
				],
			] );
		}

		/*This is the correct way to add a multiselect*/

		if ( $this->shouldProcessUpdate( '1.0.20170904' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170904' );

			$this->removeProductEavAttributes( [
				'sirent_special_rules',
				'sirent_fixed_selection_type',
			] );

			$this->addProductEavAttributes( [
				'sirent_special_rules' => [
					'label'            => 'Special Pricing Rules',
					'group'            => 'Rental',
					'input'            => 'multiselect',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,bundle,configurable',
					'source'           => 'SalesIgniter\Rental\Model\Attribute\Sources\SpecialPricingRules',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SpecialPricingRules',
					'default'          => null,
					'default_value'    => null,
					'type'             => 'text',
					'sort_order'       => 51,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20170915' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20170915' );

			$this->removeProductEavAttributes( [
				'sirent_deposit',
				'sirent_damage_waiver',
			] );

			$this->addProductEavAttributes( [
				'sirent_damage_waiver' => [
					'label'            => 'Damage Waiver Amount',
					'note'             => 'Either fixed amount or add % like 10% for percentage',
					'group'            => 'Rental',
					'input'            => 'text',
					'visible_on_front' => false,
					'required'         => false,
					'global'           => CatalogAttribute::SCOPE_STORE,
					'apply_to'         => 'sirent,configurable,bundle',
					'backend'          => 'SalesIgniter\Rental\Model\Attribute\Backend\SirentBackendConfig',
					'default'          => null,
					'type'             => 'text',
					'sort_order'       => 21,
				],
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20171002' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20171002' );
			$multiTypeSettings = [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\Multiselect',
				'default'       => null,
				'default_value' => null,
				'backend_type'  => 'text',
			];
			$this->updateProductEavAttributes( [
				'sirent_excludeddays_from'  => $multiTypeSettings,
				'sirent_excludeddays_start' => $multiTypeSettings,
				'sirent_excludeddays_end'   => $multiTypeSettings,
				'sirent_special_rules'      => $multiTypeSettings,
			] );

		}
		if ( $this->shouldProcessUpdate( '1.0.20171003' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20171003' );
			$multiTypeSettings = [
				'backend_model' => 'SalesIgniter\Rental\Model\Attribute\Backend\Multiselect',
				'default'       => null,
				'default_value' => null,
				'backend_type'  => 'text',
			];
			$this->updateProductEavAttributes( [
				'sirent_excluded_days' => $multiTypeSettings,
			] );

			$this->removeProductEavAttributes( [
				'sirent_allow_extend_order',
			] );
		}

		if ( $this->shouldProcessUpdate( '1.0.20180101' ) ) {
			$this->_logger->addDebug( 'Running Updates For Version: 1.0.20180101' );

			$applySettings = [
				'apply_to' => 'sirent,configurable,bundle',
			];
			$this->updateProductEavAttributes( [
				'sirent_use_times' => $applySettings,
			] );
		}

        if (version_compare($context->getVersion(),	'1.0.20180101', '>=')) {
            
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
                $this->_eavSetup->addAttribute(
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
                        
		$setup->endSetup();
	}

	protected function shouldProcessUpdate( $CheckVersion ) {
		$this->_logger->addDebug( 'Checking If Update Should Run For Version: ' . (string) $CheckVersion );
		if ( version_compare( (string) $this->_currentVersion, (string) $CheckVersion ) < 0 ) {
			$ShouldProcess = true;
		} else {
			$ShouldProcess = false;
		}

		return $ShouldProcess;
	}

	protected function addEavAttribute( $EntityTypeId, $AttributeCode, $AttributeData ) {
		$this->_eavSetup->addAttribute( $EntityTypeId, $AttributeCode, $AttributeData );

		return $this;
	}

	protected function addProductEavAttribute( $AttributeCode, $AttributeData ) {
		$this->_logger->addDebug( 'Adding Product Eav Attribute: ' . $AttributeCode . ' :: ' . print_r( $AttributeData, true ) );

		$this->_eavSetup->addAttribute( Product::ENTITY, $AttributeCode, $AttributeData );

		return $this;
	}

	protected function addProductEavAttributes( $Attributes ) {
		foreach ( $Attributes as $AttributeCode => $AttributeData ) {
			$this->addProductEavAttribute( $AttributeCode, $AttributeData );
		}

		return $this;
	}

	protected function removeEavAttribute( $EntityTypeId, $AttributeCode ) {
		$this->_eavSetup->removeAttribute( $EntityTypeId, $AttributeCode );

		return $this;
	}

	protected function removeProductEavAttribute( $AttributeCode ) {
		$this->_logger->addDebug( 'Removing Product Eav Attribute: ' . $AttributeCode );

		return $this->removeEavAttribute( Product::ENTITY, $AttributeCode );
	}

	protected function removeProductEavAttributes( $AttributeCodes ) {
		foreach ( $AttributeCodes as $AttributeCode ) {
			$this->removeProductEavAttribute( $AttributeCode );
		}

		return $this;
	}

	protected function updateEavAttribute( $EntityTypeId, $AttributeCode, $UpdateKey, $UpdateValue ) {
		$this->_eavSetup->updateAttribute(
			$EntityTypeId,
			$AttributeCode,
			$UpdateKey,
			$UpdateValue
		);

		return $this;
	}

	protected function updateProductEavAttribute( $AttributeCode, $Updates ) {
		$this->_logger->addDebug( 'Updating Product Eav Attribute: ' . $AttributeCode . ' :: ' . print_r( $Updates, true ) );

		foreach ( $Updates as $UpdateKey => $UpdateValue ) {
			$this->updateEavAttribute(
				Product::ENTITY,
				$AttributeCode,
				$UpdateKey,
				$UpdateValue
			);
		}

		return $this;
	}

	protected function updateProductEavAttributes( $Attributes ) {
		foreach ( $Attributes as $UpdateKey => $UpdateArr ) {
			$this->updateProductEavAttribute( $UpdateKey, $UpdateArr );
		}

		return $this;
	}
}
