/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/multiselect', /*very important for accessors*/
    'jquery',
    'ko',
    'mage/template',
], function (Column, $, ko, mageTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'SalesIgniter_Rental/grid/cells/postinput',
            fieldClass: {
                'data-grid-postinput-cell': false,
                'data-grid-checkbox-cell': false
            },
            //    posted_qty_val: 0,
            posted_qty: [],
            rowIndex: 0,
            /*
             imports: {
             exportInputValue: 'posted_qty'
             }
             ,*/
            listens: {
                /*'${ $.provider }:params.posted_qty': 'onInputValue'*//*this is for live updates like on changing the input the grid is rendered again*/
                //      posted_qty_val: 'onInputValue'

            },
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
            this._super()
            //.track([
            //   'posted_qty_val'
            //])
            //    .observe([
            //      'posted_qty_val',
            //]);
            ;
            return this;
        },

        getValue: function (row) {
            return row[this.index + '_value']
        },
        setRowIndex: function (index) {
            return ko.computed({
                read: function () {
                    return this.posted_qty[index];
                },
                write: function (inp_value) {
                    this.posted_qty[index] = inp_value;
                }
            }, this);
        },
        /**
         * Returns selections data.
         *
         * @returns {Object}
         */
        getPostedQty: function () {
            return this.posted_qty;
        },
        /**
         * Exports input data to the dataProvider if
         * sorting of a column is enabled.
         */
        exportInputValue: function () {
            var self = this;
            console.log('here exports' + this.inp_value + '---' + self.posted_qty);

            this.source('set', 'params.posted_qty', {
                field: self.row_index,
                inp_value: self.posted_qty
            });
        }

    });
});
