/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/multiselect', /*very important for accessors*/
    'jquery',
    'ko',
    'mage/template',
    'suggestserial'
], function (Column, $, ko, mageTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'SalesIgniter_Rental/grid/cells/serials',
            fieldClass: {
                'data-grid-serials-cell': true
            },

            serials_shipped: [],
            tokenizeValue: '',
            rowIndex: 0,
            listens: {
                'tokenizeValue': 'onChangedTokenizeValue'
            },
            /**
             * imports: {onSelectedChange}
             */
            modules: {
                source: '${ $.provider }'
            }
        },
        /**
         * Initializes observable properties.
         *
         * @returns {Multiselect} Chainable.
         */
        initObservable: function () {
            /*I must investigate the difference between track and observe*/
            this._super().observe(['tokenizeValue']);
            return this;
        },
        onChangedTokenizeValue: function (elVal) {
            console.log(elVal + '---' + this.rowIndex);
            this.serials_shipped[this.rowIndex] = elVal;
        },
        getResId: function (row) {
            return row[this.index + '_resorderid']
        },
        getUseSerials: function (row) {
            return row[this.index + '_use_serials']
        },
        getQtyVal: function (row) {
            return row[this.index + '_qty']
        },
        getSourceForSerial: function (row) {
            return row[this.index + '_source'];
        },
        getInputNames: function (row) {
            var useSerials = this.getUseSerials(row),
                qty = this.getQtyVal(row),
                resId = this.getResId(row);

            var inpNames = [];
            if (useSerials == '1') {
                while (qty > 0) {
                    inpNames.push('serial_' + qty);
                    qty--;
                }
            }

            return inpNames;
        },
        setRowIndex: function (index) {
            this.rowIndex = index;
        },

        /**
         * Returns selections data.
         *
         * @returns {Object}
         */
        getSerialsShipped: function () {
            return this.serials_shipped;
        },


    });
});
