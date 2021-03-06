/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** Creates datepicker binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'underscore',
    'jquery',
    'mage/translate',
], function (ko, _, $, $t) {
    'use strict';
    var defaults = {};

    ko.bindingHandlers.hideColumn = {
        'after': ['attr'], /*this can be used if we need to make something which needs attributes or use valueAccessor propertied*/
        /**
         * Initializes suggest widget on element and stores it's value to observable property.
         * Suggest binding takes either observable property or object
         *  { storage: {ko.observable}, options: {Object} }.
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        init: function (el, valueAccessor) {
            var config = valueAccessor(),
                observable,
                options = {};

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observable = config.storage;

                _.extend(options, config.options);
            } else {
                observable = config;
            }
            
            $(el).parent().hide();


        }
    };
});
