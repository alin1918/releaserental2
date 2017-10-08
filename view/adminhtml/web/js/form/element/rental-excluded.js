/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/multiselect',
    'jquery',
    'mage/validation'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {},

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this
                ._super();
        },
        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *
         * @returns {Object} Validate information.
         */
        validate: function () {
            var value = this.value(),
                message,
                isValid;
            isValid = this.disabled() || !this.visible() || value != '';
            //console.log('here' + value + '---' + (this.disabled() || !this.visible()) + '--' + this.index);
            /* if (!isValid) {
             this.value('-1');
             isValid = true;
             }*/
            message = '';
            if (!isValid) {
                message = this.disabled() || !this.visible() ? '' : $.mage.__('Please select some options');
            }
            this.error(message);
            this.bubble('error', message);
            if (!isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },
    });
});
