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
            returnProvider: 'ns = ${ $.ns }, index = qty_to_return',//this is the most important part here we select our column
            serialsProvider: 'ns = ${ $.ns }, index = serials',//this is the most important part here we select our column
            modules: {
                return_provider: '${ $.returnProvider }',
                serials_provider: '${ $.serialsProvider }'
            }
        },
        /**
         * Retrieves selections data from the selections provider.
         *
         * @returns {Object|Undefined}
         */
        getQtyReturned: function () {
            var provider = this.return_provider(),
                selections = provider && provider.getPostedQty();

            return selections;
        },
        /**
         * Retrieves selections data from the selections provider.
         *
         * @returns {Object|Undefined}
         */
        getSerialsReturned: function () {
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
        returnSelected: function (action, data) {
            data['namespace'] = 'rental_returns_listing';
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
                qty_returned: this.getQtyReturned(),
                serials_returned: this.getSerialsReturned()
            };

            action = this.getAction(actionIndex);
            callback = this._getCallback(action, data);

            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },
    });
});
