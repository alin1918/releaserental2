/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/single-checkbox',
    'jquery'
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
        checkValues: function () {
            if (this.value() == 0) {
                $('div[data-index=sirent_serial_numbers]').find(':input').prop('disabled', true);
            } else {
                $('div[data-index=sirent_serial_numbers]').find(':input').prop('disabled', false);
            }
        },
        setInitialValue: function () {

            this._super();
            this.checkValues();
            return this;
        },

        /**
         * Handle checked state changes for checkbox / radio button.
         *
         * @param {Boolean} newChecked
         */
        onCheckedChanged: function (newChecked) {
            this
                ._super();
            this.checkValues();
        },
    });
});
