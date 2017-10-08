<?php

namespace SalesIgniter\Rental\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        /** Install Excluded Dates Table */
        $tableName = $installer->getTable('sirental_excludeddates');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'excludeddate_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'product_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'store_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'disabled_type',
            DdlTable::TYPE_TEXT,
            255,
            ['nullable' => false]
        )->addColumn(
            'disabled_from',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )->addColumn(
            'disabled_to',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => false]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        /** Install Fixed Rental Names Table
         *  Create before Fixed Rental Dates table so we can
         *  add the FK to Fixed Rental Dates via the fixedrentalnames_id
         */
        $tableName = $installer->getTable('sirental_fixedrentalnames');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'fixedrentalnames_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'name',
            DdlTable::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null]
        );
        $installer->getConnection()->createTable($ddlTable);

        /** Install Fixed Rental Dates Table */
        $tableName = $installer->getTable('sirental_fixedrentaldates');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'fixedrentaldate_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'fixedrentalnames_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false,
                'unsigned' => true,
                'primary' => true]
        )->addColumn(
            'repeat_type',
            DdlTable::TYPE_TEXT,
            255,
            ['nullable' => false]
        )->addColumn(
            'start_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'repeat_days',
            DdlTable::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null]
        )->addForeignKey(
            $installer->getFkName(
                $tableName,
                'fixedrentalnames_id',
                $installer->getTable('sirental_fixedrentalnames'),
                'fixedrentalnames_id'
            ),
            'fixedrentalnames_id',
            $installer->getTable('sirental_fixedrentalnames'),
            'fixedrentalnames_id',
            DdlTable::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($ddlTable);

        /**
         * Install Reservation Orders Table
         * this table stores reservation dates, turnover before / after dates,
         * and quantity shipped & returned information
         *
         * Notes: dropped columns: sendreturn_id, item_booked_serialize, product_type - are these needed?
         */
        $tableName = $installer->getTable('sirental_reservationorders');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'reservationorder_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'order_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'product_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'qty',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'qty_cancel',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'order_item_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'start_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'start_date_with_turnover',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date_with_turnover',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'comments',
            DdlTable::TYPE_TEXT,
            '64k',
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'extend_notification_sent',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'dropoff',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'pickup',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'fixedrentaldates_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'qty_shipped',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'qty_returned',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['order_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['order_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['start_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['start_date'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['end_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['end_date'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        /**
         * Install Reservation Quotes Table
         * this table stores reservation dates, turnover before / after dates,
         * and quantity shipped & returned information for products added to shopping cart before order is placed
         *
         * Notes: dropped columns: sendreturn_id, item_booked_serialize, product_type - are these needed?
         */
        $tableName = $installer->getTable('sirental_reservationquotes');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'reservationquote_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'quote_item_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'quote_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'product_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'qty',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'start_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'start_date_with_turnover',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date_with_turnover',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['quote_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['quote_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['start_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['start_date'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['end_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['end_date'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        /** Install Send Return Table  */

        $tableName = $installer->getTable('sirental_sendreturn');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'sendreturn_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'type',
            DdlTable::TYPE_TEXT,
            null,
            ['nullable' => false],
            'rental or queue'
        )->addColumn(
            'quote_item_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'order_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'customer_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'product_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'start_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'end_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'send_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'return_date',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'qty',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false]
        )->addColumn(
            'serial_number',
            DdlTable::TYPE_TEXT,
            '64k',
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'reservationorder_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0]
        )->addColumn(
            'qty_parent',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['order_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['order_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['customer_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['customer_id'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['start_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['start_date'],
            []
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['end_date'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['end_date'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        /** Install Serial Number Details Table  */

        $tableName = $installer->getTable('sirental_serialnumber_details');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'serialnumber_details_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'product_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'name',
            DdlTable::TYPE_TEXT,
            '64k',
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'description',
            DdlTable::TYPE_TEXT,
            '64k',
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'cost',
            DdlTable::TYPE_FLOAT,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'date_added',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'date_edited',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['product_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['product_id'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        /** Install Payment Transaction Table  */

        $tableName = $installer->getTable('sirental_payment_transaction');
        $ddlTable = $installer->getConnection()->newTable($tableName);
        $ddlTable->addColumn(
            'transaction_id',
            DdlTable::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        )->addColumn(
            'parent_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true]
        )->addColumn(
            'order_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'payment_id',
            DdlTable::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'txn_id',
            DdlTable::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'parent_txn_id',
            DdlTable::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'txn_type',
            DdlTable::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'is_closed',
            DdlTable::TYPE_SMALLINT,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'additional_information',
            DdlTable::TYPE_BLOB,
            null,
            ['nullable' => true, 'default' => null]
        )->addColumn(
            'created_at',
            DdlTable::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null]
        )->addIndex(
            $installer->getIdxName(
                $tableName,
                ['order_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['order_id'],
            []
        );
        $installer->getConnection()->createTable($ddlTable);

        $installer->endSetup();
    }
}
