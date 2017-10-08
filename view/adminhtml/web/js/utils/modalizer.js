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
            //'css!css/tingle/tingle',
            //'tingle',
            'Magento_Ui/js/modal/modal'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($, tgc, tingle) {
    'use strict';

    $.widget('salesigniter.modalizer', {
        options: {
            openerCssClass: null
        },
        _create: function () {
            this._initCalls();
        },
        _initCalls: function () {
            var self = this;
            /*
             var modalContent = new tingle.modal({
             footer: false,
             stickyFooter: false,
             onOpen: function () {
             //console.log('modal open');
             },
             onClose: function () {
             //console.log('modal closed');
             }
             });
             */
            var btnOpen = $(this.options.openerCssClass);
            btnOpen.on('click', function () {
                self.element.modal({
                    type: 'slide',
                    buttons: [],
                    closed: function (e, modal) {
                        modal.modal.remove();
                    }
                });
                self.element.modal('openModal');

                //variablesContent.evalScripts.bind(variablesContent).defer();
                //self.element.modal('openModal');


                //modalContent.setContent(self.element.html());
                //modalContent.open();

            });

        }

    });
    return {
        modalizer: $.salesigniter.modalizer
    };
}));
