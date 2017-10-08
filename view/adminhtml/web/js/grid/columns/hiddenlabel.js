/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column', /*very important for accessors*/
    'jquery',
    'ko',
    'mage/template',
    'hiddencolumn'
], function (Column, $, ko, mageTemplate) {
    'use strict';

    return Column.extend({

        /**
         * Initializes observable properties.
         *
         * @returns {Multiselect} Chainable.
         */
        initObservable: function () {
            this._super();

            return this;
        }

    });
});
