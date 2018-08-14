/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/grid/provider'
], function (ko, _, utils, Provider) {
    'use strict';

    return Provider.extend({
        defaults: {
            firstLoad: true,
            lastError: false,
            storageConfig: {
                component: 'Magento_Ui/js/grid/data-storage',
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                updateUrl: '${ $.update_url }',
                indexField: 'reservationorder_id'
            },
            listens: {
                params: 'onParamsChange',
                requestConfig: 'updateRequestConfig'
            },
            ignoreTmpls: {
                data: true
            }
        },
    });
});

