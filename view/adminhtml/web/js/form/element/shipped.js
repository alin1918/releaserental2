/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mageUtils',
    'Magento_Ui/js/form/element/abstract',
    'shippeddisabler'
], function (moment, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            options: {},
            elementTmpl: 'SalesIgniter_Rental/grid/cells/shipped'
        },
        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super();

            return this;
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            this._super();
            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this._super();
        },


    });
});
