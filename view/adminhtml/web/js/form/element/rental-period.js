/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
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
            isValid = this.disabled() || !this.visible() /*|| value === ''*/ || /[0-9]+[hmdwMy]{1}$/.test(value);
            if (!isValid && (this.index === 'period_additional' || this.index === 'sirent_min' || this.index === 'sirent_max' || this.index === 'sirent_padding' || this.index === 'sirent_turnover_before' || this.index === 'sirent_turnover_after') && value === '') {
                this.value('0d');
                isValid = true;
            }
            message = '';
            if (!isValid) {
                message = !this.disabled() && this.visible() ? $.mage.__('Enter valid period') : '';
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
