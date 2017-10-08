/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jquery:true */
/*eslint max-depth: 0*/

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/template',
            'jquery/ui',
            'mage/translate',
            'maskspin'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('salesigniter.pricingppr', {
        options: {
            updatePricingUrl: '',
            loaderIcon: '',
            priceBoxesSelector: '[data-role=priceBox]'
        },
        _create: function () {
            this.options.loaderCtx = this.element.parent();
            this._initCalls();
        },
        _initCalls: function () {
            var self = this;


            $.ajax({
                url: this.options.updatePricingUrl,
                data: 'data-product-id=' + self.element.attr('data-product-id'),
                type: 'post',
                dataType: 'json',
                beforeSend: function () {
                    self.options.loaderCtx.mask({
                        spinner: {lines: 10, length: 5, width: 3, radius: 8},
                        delay: 1000,
                        overlayOpacity: 1
                    });
                },
                success: function (res) {
                    self.options.loaderCtx.unmask({
                        spinner: {lines: 10, length: 5, width: 3, radius: 8},
                        delay: 500,
                        overlayOpacity: 1
                    });
                    self.options.loaderCtx.replaceWith(res.html);
                }
            });
        }

    });
    return {
        pricingppr: $.salesigniter.pricingppr
    };
}));
