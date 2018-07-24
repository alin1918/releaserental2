/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/grid/massactions'
], function (ko, _, utils, Massactions) {
    'use strict';

    return Massactions.extend({
        defaults: {
            shipProvider: 'ns = ${ $.ns }, index = qty_to_ship',//this is the most important part here we select our column
            serialsProvider: 'ns = ${ $.ns }, index = serials',//this is the most important part here we select our column
            modules: {
                ship_provider: '${ $.shipProvider }',
                serials_provider: '${ $.serialsProvider }'
            }
        },
        /**
         * Retrieves selections data from the selections provider.
         *
         * @returns {Object|Undefined}
         */
        getQtyShipped: function () {
            var provider = this.ship_provider(),
                selections = provider && provider.getPostedQty();

            return selections;
        },
        /**
         * Retrieves selections data from the selections provider.
         *
         * @returns {Object|Undefined}
         */
        getSerialsShipped: function () {
            var provider = this.serials_provider(),
                selections = provider && provider.getPostedSerials();

            return selections;
        },
        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        sendSelected: function (action, data) {
            data['namespace'] = 'rental_send_listing';
            //var qtyShipped = {
            //  'qty_actions': data.qty_actions,
            //'namespace': 'rental_send_listing'
            //};
            /*var newData = _.map(data, function (o) {
             return _.omit(o, 'qty_shipped');
             });*/
            //_.extend(qtyShipped, data || {});

            utils.submit({
                url: action.url,
                data: data
            });
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var data = this.getSelections(),
                action,
                callback;

            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }
            data['qty_actions'] = {
                qty_shipped: this.getQtyShipped(),
                serials_shipped: this.getSerialsShipped()
            };

            delete data.excluded;

            action = this.getAction(actionIndex);
            callback = this._getCallback(action, data);

            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },
    });
});
