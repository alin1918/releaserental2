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
    'css!css/select2/select2',
    'select2'
], function (ko, _, $, $t) {
    'use strict';
    var defaults = {
        buttonText: $t('Select')
    };

    ko.bindingHandlers.suggestProduct = {
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

            function checkQtyAvailable() {
                var dataFormSerializedAsArray = $(el).parent().parent().parent().parent().find(':input').serializeArray();
                var index;
                var hasProductId = false;
                for (index = 0; index < dataFormSerializedAsArray.length; ++index) {
                    if (dataFormSerializedAsArray[index].name == "product_id" || dataFormSerializedAsArray[index].name.indexOf('[product_id]') !== -1) {
                        hasProductId = true;
                        break;
                    }
                }

                if (hasProductId === false && $(el).attr('value_attr') && $(el).attr('value_attr') !== '') {
                    dataFormSerializedAsArray.push({
                        name: "product_id_orig",
                        value: $(el).attr('value_attr')
                    });
                }
                $.ajax({
                    url: $(el).attr('url_qty'),
                    data: $.param(dataFormSerializedAsArray),
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function () {
                        $('body').trigger('processStart');

                    },
                    success: function (res) {
                        $('body').trigger('processStop');
                        if (res.newData !== false) {
                            var $option = $('<option selected>' + formatProductSelection(res.newData[0]) + '</option>').val(res.newData[0].id);
                            $(el).append($option).trigger('change'); // append the option and update Select2
                            $(el).attr('value_attr', '');
                        }
                        observable(res.productId);
                        $('.qty_available').html(res.availableQuantity);
                    }
                });
            }

            function formatProduct(product) {
                if (product.loading) {
                    return product.text;
                }
                var imageUrl = '/';
                if (product.image) {
                    imageUrl = product.image;
                }
                var $product = $(
                    '<span>' + product.text + '</span>'
                );
                /*<img src="' + imageUrl + '" /> */
                return $product;
            };

            function formatProductSelection(product) {
                if (typeof product.sku !== 'undefined') {
                    return 'Name: ' + product.text + ' - SKU: ' + product.sku;
                } else if (typeof product.text !== 'undefined') {
                    return product.text;
                }
                return '';
            }

            var $qtyField = $('<div class="qty_available" ></div>');
            $qtyField.insertAfter($(el).parent());

            $(el).select2({
                templateResult: formatProduct,
                templateSelection: formatProductSelection,
                escapeMarkup: function (markup) {
                    return markup;
                },
                ajax: {
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    url: $(el).attr('url'),
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        $(el).attr('value_attr', '');
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < 1
                            }
                        };
                    }/*,
                     data: function (params) {
                     var query = {
                     search: params.term,
                     page: params.page
                     }
                     if($(el).attr('value_attr') && $(el).attr('value_attr') !== '') {
                     query.product_id = $(el).attr('value_attr');
                     }

                     return query;
                     }*/
                }
            });

            $(el).on("select2:select", function (e) {
                //$(el).attr('value_attr', '');
                checkQtyAvailable();
            });
            $(el).parent().parent().parent().parent().find(':input').on('change', function () {
                checkQtyAvailable();
            });
            $(el).trigger('change');

        }
    };
});
