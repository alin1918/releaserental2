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
            'css!css/tokenize/bootstrap',
            'css!css/tokenize/tokenize2',
            'tokenize'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('salesigniter.tokenizer', {
        options: {
            dataSource: '',
            tokensMaxItems: 0
        },
        _create: function () {
            this._initCalls();
        },
        _initCalls: function () {
            var self = this;
            this.element.tokenize2(this.options);
        }

    });
    return {
        tokenizer: $.salesigniter.tokenizer
    };
}));
