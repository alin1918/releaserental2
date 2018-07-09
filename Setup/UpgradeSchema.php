<?php

namespace SalesIgniter\Rental\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
	/**
	 * {@inheritdoc}
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 *
	 * @throws \Zend_Db_Exception
	 */
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();
		$installer = $setup;

		if ( version_compare( $context->getVersion(), '1.0.20160224' ) < 0 ) {

			/* Install Reservation Prices Table */
			$tableName = $setup->getTable( 'sirental_price' );
			$setup->getConnection()->dropTable( $tableName );
			$ddlTable = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'price_id',
				DdlTable::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'entity_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'website_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'period_quantity',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ],
				'correlates to minute, hour, day, week, month'
			)->addColumn(
				'period_type',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ],
				'correlates to minute, hour, day, week, month'
			)->addColumn(
				'price',
				DdlTable::TYPE_FLOAT,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'qty_start',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'qty_end',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'customer_group_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'all_groups',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'additional_period_type',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'additional_price',
				DdlTable::TYPE_FLOAT,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'pricesbydate_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ],
				'foreign key to tie price to date and time'
			)->addIndex(
				$setup->getIdxName(
					$tableName,
					[ 'entity_id' ],
					AdapterInterface::INDEX_TYPE_INDEX
				),
				[ 'entity_id' ],
				[]
			);
			$setup->getConnection()->createTable( $ddlTable );

			/* Install Reservation Prices By Dates Table */
			$tableName = $setup->getTable( 'sirental_pricebydate' );
			$ddlTable  = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'pricebydate_id',
				DdlTable::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'description',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'store_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'type',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ],
				'correlates to minute, hour, day, week, month'
			)->addColumn(
				'date_from',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'date_to',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'repeat_days',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			);
			$setup->getConnection()->createTable( $ddlTable );
		}
		if ( version_compare( $context->getVersion(), '1.0.20160225' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_serialnumber_details' );
			$setup->getConnection()->changeColumn(// change description field to notes
				$tableName,
				'description', // old field name
				'notes', // new field name
				[ 'type' => DdlTable::TYPE_TEXT, 'nullable' => true, 'default' => null ],
				'Notes' // field comment
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160226' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_serialnumber_details' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'serialnumber', // field name
				[
					'type'     => DdlTable::TYPE_TEXT,
					'after'    => 'serialnumber_details_id', // if you want column added after specific existing column
					'nullable' => false,
					'comment'  => 'Serial Number',
				]
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160227' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_serialnumber_details' );
			$setup->getConnection()->dropColumn(
				$tableName,
				'name'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'date_edited'
			);
			$setup->getConnection()->changeColumn(
				$tableName,
				'date_added',
				'date_acquired',
				[ 'type' => DdlTable::TYPE_DATE, 'nullable' => true, 'default' => null ],
				'Date Acquired' // field comment
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160228' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_serialnumber_details' );
			$setup->getConnection()->addColumn(
				$tableName,
				'status',
				[ 'type' => DdlTable::TYPE_TEXT, 'nullable' => true, 'default' => null, 'comment' => 'Status' ]
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20160531' ) < 0 ) {

			/*
			 * removed start/end date from send_return table since these are already
			 * in the reservationorders table. Also remove return date, make separate
			 * table for returns. Rename sendreturn table to just send
			 */

			if ( $setup->getConnection()->isTableExists( 'sirental_sendreturn' ) ) {
				$tableName = $setup->getTable( 'sirental_sendreturn' );
				$setup->getConnection()->dropColumn(
					$tableName,
					'start_date'
				);
				$setup->getConnection()->dropColumn(
					$tableName,
					'end_date'
				);
				$setup->getConnection()->dropColumn(
					$tableName,
					'return_date'
				);
				$setup->getConnection()->changeColumn(
					$tableName,
					'sendreturn_id',
					'send_id',
					[
						'type'     => DdlTable::TYPE_INTEGER,
						'identity' => true,
						'unsigned' => true,
						'nullable' => false,
						'primary'  => true,
					]
				);
			}
			if ( $setup->getConnection()->isTableExists( 'sirental_sendreturn' ) ) {
				$setup->getConnection()->renameTable( 'sirental_sendreturn', 'sirental_send' );
			}

			/*
			 * Add separate table for returns
			 */

			$tableName = $setup->getTable( 'sirental_return' );
			$ddlTable  = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'return_id',
				DdlTable::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'type',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => false ],
				'rental or queue'
			)->addColumn(
				'quote_item_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'order_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'customer_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'product_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'return_date',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true, 'default' => null ]
			)->addColumn(
				'qty',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'serial_number',
				DdlTable::TYPE_TEXT,
				'64k',
				[ 'nullable' => true, 'default' => null ]
			)->addColumn(
				'reservationorder_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false, 'default' => 0 ]
			)->addColumn(
				'qty_parent',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false, 'default' => 0 ]
			)->addIndex(
				$installer->getIdxName(
					$tableName,
					[ 'product_id' ],
					AdapterInterface::INDEX_TYPE_INDEX
				),
				[ 'product_id' ],
				[]
			)->addIndex(
				$installer->getIdxName(
					$tableName,
					[ 'order_id' ],
					AdapterInterface::INDEX_TYPE_INDEX
				),
				[ 'order_id' ],
				[]
			)->addIndex(
				$installer->getIdxName(
					$tableName,
					[ 'customer_id' ],
					AdapterInterface::INDEX_TYPE_INDEX
				),
				[ 'customer_id' ],
				[]
			);
			$installer->getConnection()->createTable( $ddlTable );
		}

		if ( version_compare( $context->getVersion(), '1.0.20160614' ) < 0 ) {

			/*
			 * add serial number to reservationorders table for easier reports
			 */
			$tableName = $setup->getTable( 'sirental_reservationorders' );
			$setup->getConnection()->addColumn(
				$tableName,
				'serials_shipped',
				[
					'type'     => DdlTable::TYPE_TEXT,
					'nullable' => true,
					'comment'  => 'Serials Shipped',
				]
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160809' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_reservationquotes' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'quote_item_parent_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Quote Item Parent Id',
				]
			);

			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table

			$setup->getConnection()->addColumn(
				$tableName,
				'credit_memo_item_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Credit Memo Item Id',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'shipment_item_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Shipment Item Id',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'return_item_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Return Item Id',
				]
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'qty_cancel'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'qty_shipped'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'qty_returned'
			);
			$tableName = $setup->getTable( 'sirental_return' ); // existing table
			if ( $setup->getConnection()->tableColumnExists( $tableName, 'return_id' ) ) {
				$setup->getConnection()->changeColumn(
					$tableName,
					'return_id',
					'return_item_id',
					[
						'type'     => DdlTable::TYPE_INTEGER,
						'identity' => true,
						'unsigned' => true,
						'nullable' => false,
						'primary'  => true,
					]
				);
			}
			if ( $setup->getConnection()->isTableExists( 'sirental_sendreturn' ) ) {
				$setup->getConnection()->dropTable( 'sirental_sendreturn' );
			}
			if ( $setup->getConnection()->isTableExists( 'sirental_send' ) ) {
				$setup->getConnection()->dropTable( 'sirental_send' );
			}

			/*
			 * We add these columns just as a backup.
			 * We basically don't need them because we will use reservation_order table.
			 * We should remove them if never used
			 */
			$tableName = $setup->getTable( 'sales_shipment_item' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'reservationorder_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Reservation Order Id',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'start_date', // field name
				[
					'type'    => DdlTable::TYPE_DATETIME,
					[ 'nullable' => true ],
					'comment' => 'Start Date for The Reservation',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'end_date', // field name
				[
					'type'    => DdlTable::TYPE_DATETIME,
					[ 'nullable' => true ],
					'comment' => 'End Date for The Reservation',
				]
			);

			$tableName = $setup->getTable( 'sales_creditmemo_item' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'reservationorder_id', // field name
				[
					'type'    => DdlTable::TYPE_INTEGER,
					[ 'nullable' => true ],
					'comment' => 'Reservation Order Id',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'start_date', // field name
				[
					'type'    => DdlTable::TYPE_DATETIME,
					[ 'nullable' => true ],
					'comment' => 'Start Date for The Reservation',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'end_date', // field name
				[
					'type'    => DdlTable::TYPE_DATETIME,
					[ 'nullable' => true ],
					'comment' => 'End Date for The Reservation',
				]
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20160817' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_price' ); // existing table
			$setup->getConnection()->dropColumn(
				$tableName,
				'additional_price'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'period_type'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'additional_period_type'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'period_quantity'
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'price_additional', // field name
				[
					'type'    => DdlTable::TYPE_FLOAT,
					[ 'nullable' => true ],
					'comment' => 'Additional Price',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'period_additional', // field name
				[
					'type'    => DdlTable::TYPE_TEXT,
					[ 'nullable' => true ],
					'comment' => 'Additional Period',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'period', // field name
				[
					'type'    => DdlTable::TYPE_TEXT,
					[ 'nullable' => true ],
					'comment' => 'Period',
				]
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160824' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'qty_returned', // field name
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Returned Quantity',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'qty_shipped', // field name
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Shipped Quantity',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'qty_cancel', // field name
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Cancelled Quantity',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'serials_returned',
				[
					'type'     => DdlTable::TYPE_TEXT,
					'nullable' => true,
					'comment'  => 'Serials Returned',
				]
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'credit_memo_item_id'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'return_item_id'
			);
			$setup->getConnection()->dropColumn(
				$tableName,
				'shipment_item_id'
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160825' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table
			$setup->getConnection()
			      ->modifyColumn(
				      $tableName,
				      'order_id',
				      [
					      'type'     => DdlTable::TYPE_INTEGER,
					      'unsigned' => true,
					      'nullable' => false,
					      'default'  => 0,
				      ]
			      )
			      ->addIndex(
				      $tableName,
				      $setup->getIdxName( $tableName, [ 'order_id' ] ),
				      [ 'order_id' ]
			      );
			$setup->getConnection()
			      ->addForeignKey(
				      $setup->getFkName(
					      $tableName,
					      'order_id',
					      $setup->getTable( 'sales_order' ),
					      'entity_id'
				      ),
				      $tableName,
				      'order_id',
				      $setup->getTable( 'sales_order' ),
				      'entity_id',
				      DdlTable::ACTION_CASCADE
			      );
			$tableName = $setup->getTable( 'sirental_reservationquotes' ); // existing table
			$setup->getConnection()->dropTable( $tableName );
		}
		if ( version_compare( $context->getVersion(), '1.0.20160826' ) < 0 ) {
			$tableName = $setup->getTable( 'sales_order' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'pickup_date',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Pickup Date',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'dropoff_date',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Dropoff Date',
				]
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20160827' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'order_increment_id',
				[
					'type'     => DdlTable::TYPE_TEXT,
					'nullable' => true,
					'comment'  => 'Increment Id',
				]
			);

			/*
			 * Table For inventory manipulation on grid and reports
			 */

			$tableName = $setup->getTable( 'sirental_inventory_grid' );
			$ddlTable  = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'id',
				DdlTable::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'order_increment_ids',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'order_ids',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'start_date',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true, 'default' => null ]
			)->addColumn(
				'end_date',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true, 'default' => null ]
			)->addColumn(
				'qty',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ]
			);
			$installer->getConnection()->createTable( $ddlTable );
		}

		if ( version_compare( $context->getVersion(), '1.0.20160828' ) < 0 ) {
			/*
			 * When using early ship and return the start and end date will be those
			 * When order is reserved a row is added here, when a qty is canceled for order_item_id then the qty will be subtracted by finding the orderitem_id and prent_id =0
			 * When an order_item_id is shipped and is using early ship then the specific shipped_qty will be recorded to qty shipment_date will be start_date and end_date will remain end_date. If it has serials they will be recorded
			 * When an order is returned, it will search for the specific order_item_id // parent_id=resorder_id // and serials and if qty is lower it will break into 2 rows... the existing one will be qty=qty_returned and return_date and will add a new row qith qty-qty_returned and ship_date and serials not_shipped
			 * The flow is like this:
			 * for every order_item in an order->Order_item reserved->new row is added to resorder ->
			 */

			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'order_type',
				[
					'type'     => DdlTable::TYPE_TEXT,
					'nullable' => true,
					'comment'  => 'Order Type(normal,manual,maintenance)',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'ship_date',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Ship Date',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'return_date',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Return Date',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'cancel_date',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Return Date',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'start_date_use_grid',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'Start date to use in grid',
				]
			);
			$setup->getConnection()->addColumn(
				$tableName,
				'end_date_use_grid',
				[
					'type'     => DdlTable::TYPE_DATETIME,
					'nullable' => true,
					'comment'  => 'End Date to use in grid',
				]
			);

			$setup->getConnection()->addColumn(
				$tableName,
				'parent_id',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Parent Reservation Order',
				]
			);
			//drop sirental_return not needed
		}

		if ( version_compare( $context->getVersion(), '1.0.20160829' ) < 0 ) {
			/*
			 * The main problem is the way how inventory is created is hard to know which based on what orders those reserved qtys are.
			 * So we use the reservations orders table and if the period overlaps then it means that reservation order was used.
			 */
			$tableName = $setup->getTable( 'sirental_reservationorders' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'qty_use_grid',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Qty to check in grid',
				]
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20160830' ) < 0 ) {
			/*
			 * The main problem is the way how inventory is created is hard to know which based on what orders those reserved qtys are.
			 * So we use the reservations orders table and if the period overlaps then it means that reservation order was used.
			 */
			$tableName = $setup->getTable( 'sirental_inventory_grid' ); // existing table
			$setup->getConnection()->addColumn(
				$tableName,
				'product_id',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Product Id',
				]
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20161113' ) < 0 ) {
			/*
			 * Drop primary index
			 */
			$setup->getConnection()->dropForeignKey(
				$setup->getTable( 'sirental_reservationorders' ),
				$setup->getFkName(
					$setup->getTable( 'sirental_reservationorders' ),
					'order_id',
					$setup->getTable( 'sales_order' ),
					'entity_id'
				)
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20170814' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_serialnumber_details' );
			$setup->getConnection()->addColumn(
				$tableName,
				'reservationorder_id',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => true,
					'comment'  => 'FK Reservation order id',
				]
			);
		}

		if ( version_compare( $context->getVersion(), '1.0.20170904' ) < 0 ) {
			$tableName = $setup->getTable( 'sirental_pricebydate' );
			$setup->getConnection()->dropTable( $tableName );
			$tableName = $setup->getTable( 'sirental_fixed_dates' );
			$setup->getConnection()->dropTable( $tableName );
			$tableName = $setup->getTable( 'sirental_fixed_names' );
			$setup->getConnection()->dropTable( $tableName );
			$tableName = $setup->getTable( 'sirental_excludeddates' );
			$setup->getConnection()->dropTable( $tableName );
			$tableName = $setup->getTable( 'sirental_fixedrentaldates' );
			$setup->getConnection()->dropTable( $tableName );
			$tableName = $setup->getTable( 'sirental_fixedrentalnames' );
			$setup->getConnection()->dropTable( $tableName );

			/* Install Fixed Dates Table */
			$tableName = $setup->getTable( 'sirental_fixed_dates' );
			$ddlTable  = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'date_id',
				DdlTable::TYPE_INTEGER,
				10,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'name_id',
				DdlTable::TYPE_INTEGER,
				10,
				[
					'unsigned' => true,
					'nullable' => false,
				]
			)->addColumn(
				'store_id',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'repeat_type',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => false ],
				'never, daily, monthly, yearly'
			)->addColumn(
				'all_day',
				DdlTable::TYPE_INTEGER,
				null,
				[ 'nullable' => false ],
				'Bool 0,1'
			)->addColumn(
				'date_from',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'date_to',
				DdlTable::TYPE_DATETIME,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'repeat_days',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			)->addColumn(
				'week_month',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			);

			$setup->getConnection()->createTable( $ddlTable );

			/* Install Fixed Dates Table */
			$tableName = $setup->getTable( 'sirental_fixed_names' );
			$ddlTable  = $setup->getConnection()->newTable( $tableName );
			$ddlTable->addColumn(
				'name_id',
				DdlTable::TYPE_INTEGER,
				10,
				[
					'identity' => true,
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				]
			)->addColumn(
				'name',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => false ]
			)->addColumn(
				'catalog_rules',
				DdlTable::TYPE_TEXT,
				null,
				[ 'nullable' => true ]
			);
			$setup->getConnection()->createTable( $ddlTable );

			$setup->getConnection()
			      ->addForeignKey(
				      $setup->getFkName(
					      $setup->getTable( 'sirental_fixed_dates' ),
					      'name_id',
					      $setup->getTable( 'sirental_fixed_names' ),
					      'name_id'
				      ),
				      $setup->getTable( 'sirental_fixed_dates' ),
				      'name_id',
				      $setup->getTable( 'sirental_fixed_names' ),
				      'name_id',
				      DdlTable::ACTION_CASCADE
			      );
		}
		if ( version_compare( $context->getVersion(), '1.0.20180314' ) < 0 ) {

			$setup->getConnection()->addColumn(
				$setup->getTable( 'sales_order' ),
				'is_reserved',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Flag to know if the items have been reserved',
				]
			);
		}
		if ( version_compare( $context->getVersion(), '1.0.20180710' ) < 0 ) {

			$setup->getConnection()->changeColumn(
				$setup->getTable( 'sales_order' ),
				'is_reserved',
				'is_reserved',
				[
					'type'     => DdlTable::TYPE_INTEGER,
					'nullable' => false,
					'default'  => 0,
					'comment'  => 'Flag to know if the items have been reserved',
				]
			);
		}

		/*if (version_compare($context->getVersion(), '1.0.20171005') < 0) {
			$setup->getConnection()
				->addForeignKey(
					$setup->getFkName(
						$setup->getTable('sirental_price'),
						'entity_id',
						$setup->getTable('catalog_products_entity'),
						'entity_id'
					),
					$setup->getTable('sirental_price'),
					'entity_id',
					$setup->getTable('catalog_products_entity'),
					'entity_id',
					DdlTable::ACTION_CASCADE
				);
		}*/

		$setup->endSetup();
	}
}
