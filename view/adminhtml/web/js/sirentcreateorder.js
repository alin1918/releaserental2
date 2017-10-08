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
            'prototype',
            'Magento_Catalog/catalog/product/composite/configure',
            'Magento_Sales/order/create/scripts',
            'Magento_Sales/order/create/form',
            /*'mage/adminhtml/grid'*/
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function (jQuery) {
    'use strict';
    var AdminOrderPrototype = AdminOrder.prototype;
    var ProductConfigurePrototype = ProductConfigure.prototype;

    /**
     * function not used but can be useful
     * @param tblId
     * @returns {*|jQuery}
     */
    function getTableFromId(tblId) {
        var arr = $(tblId + ' > tbody > tr').map(function () {
            return $(this).children().map(function () {
                return $(this);
            });
        });
        return arr;//access by arr[row][col].text();
    }

    ProductConfigurePrototype._showWindow = function () {

        if (!this.notShow || !order.hasGlobalDatesSetup) {
            this.dialog.modal('openModal');
        } else {
            jQuery(this.blockForm).trigger('processStart');
        }
        //this._toggleSelectsExceptBlock(false);

        if (Object.isFunction(this.showWindowCallback[this.current.listType])) {
            this.showWindowCallback[this.current.listType]();
        }
    }

    AdminOrderPrototype.productGridRowClick = function (grid, event) {
        var trElement = Event.findElement(event, 'tr');

        var qtyElement = trElement.select('input[name="qty"]')[0];
        var eventElement = Event.element(event);
        var isInputCheckbox = eventElement.tagName == 'INPUT' && eventElement.type == 'checkbox';
        var isInputQty = eventElement.tagName == 'INPUT' && eventElement.name == 'qty';
        if (trElement && !isInputQty) {
            var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0];
            var confLink = Element.select(trElement, 'a')[0];
            var priceColl = Element.select(trElement, '.price')[0];
            if (checkbox) {
                // processing non composite product
                if (confLink.readAttribute('disabled')) {
                    var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                    grid.setCheckboxChecked(checkbox, checked);
                    // processing composite product
                } else if (isInputCheckbox && !checkbox.checked) {
                    grid.setCheckboxChecked(checkbox, false);
                    // processing composite product
                } else if (!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                    var listType = confLink.readAttribute('list_type');
                    var productId = confLink.readAttribute('product_id');
                    if (typeof this.productPriceBase[productId] == 'undefined') {
                        var priceBase = priceColl.innerHTML.match(/.*?([\d,]+\.?\d*)/);
                        if (!priceBase) {
                            this.productPriceBase[productId] = 0;
                        } else {
                            this.productPriceBase[productId] = parseFloat(priceBase[1].replace(/,/g, ''));
                        }
                    }
                    productConfigure.setConfirmCallback(listType, function () {
                        // sync qty of popup and qty of grid
                        var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                        if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                            qtyElement.value = confirmedCurrentQty.value;
                        }
                        // calc and set product price
                        var productPrice = this._calcProductPrice();
                        if (this._isSummarizePrice()) {
                            productPrice += this.productPriceBase[productId];
                        }
                        productPrice = parseFloat(Math.round(productPrice + "e+2") + "e-2");
                        priceColl.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                        if (productConfigure.isRentType) {
                            priceColl.innerHTML = this.priceReservation;
                        }
                        if (this.datePickersActive) {
                            this.datePickersActive._destroy();
                        }
                        jQuery(this.blockForm).trigger('processStop');
                        jQuery('body').trigger('processStop');
                        // and set checkbox checked
                        grid.setCheckboxChecked(checkbox, true);

                    }.bind(this));
                    productConfigure.setCancelCallback(listType, function () {
                        if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                            grid.setCheckboxChecked(checkbox, false);
                        }
                        if (this.datePickersActive) {
                            this.datePickersActive._destroy();
                        }

                    });
                    productConfigure.setShowWindowCallback(listType, function () {
                        // sync qty of grid and qty of popup
                        var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                        if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                            formCurrentQty.value = qtyElement.value;
                        }
                    }.bind(this));
                    productConfigure.notShow = false;
                    productConfigure.isRentType = false;

                    if (eventElement.tagName != 'A' && confLink.hasClassName('action-configure-notfirst')) {
                        productConfigure.notShow = true;
                    }
                    if (confLink.hasClassName('is_rent_type')) {
                        productConfigure.isRentType = true;
                    }
                    productConfigure.showItemConfiguration(listType, productId);

                }
            }
        }
    };

    order.productGridRowClick = AdminOrderPrototype.productGridRowClick;
    sales_order_create_search_gridJsObject.rowClickCallback = order.productGridRowClick.bind(order);

}));
