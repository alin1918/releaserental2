/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            'salesigniter/report': 'SalesIgniter_Rental/js/report',
            'salesigniter/fullcalendar': 'SalesIgniter_Rental/js/fullcalendar',
            'suggestserial': 'SalesIgniter_Rental/js/knockout/bindings/suggestserial',
            'suggestproduct': 'SalesIgniter_Rental/js/knockout/bindings/suggestproduct',
            'shippeddisabler': 'SalesIgniter_Rental/js/knockout/bindings/shippeddisabler',
            'hiddencolumn': 'SalesIgniter_Rental/js/knockout/bindings/hiddencolumn',
            'css': 'SalesIgniter_Rental/js/utils/css',
            'tokenize': 'SalesIgniter_Rental/js/utils/tokenize2',
            'tingle': 'SalesIgniter_Rental/js/utils/tingle',
            'pprdatepicker': 'SalesIgniter_Rental/js/pprdatepicker',
            'sirentcreateorder': 'SalesIgniter_Rental/js/sirentcreateorder',
            'spin': 'SalesIgniter_Rental/js/utils/spin',
            'maskspin': 'SalesIgniter_Rental/js/utils/jquery.maskspin',
            'pprtimepicker': 'SalesIgniter_Rental/js/utils/jquery.timepicker',
            'tokenizer': 'SalesIgniter_Rental/js/utils/jquery.tokenizer',
            'select2': 'SalesIgniter_Rental/js/utils/select2',
            'ajaxq': 'SalesIgniter_Rental/js/utils/ajaxq',
            'modalizer': 'SalesIgniter_Rental/js/utils/modalizer',
            'salesigniter/qtip': 'SalesIgniter_Rental/js/jquery.qtip',
            'moment': 'SalesIgniter_Rental/js/utils/moment215/moment.min',
            'fullcalendar': 'SalesIgniter_Rental/js/utils/fullcalendar301/fullcalendar.min'
        },
        shim: {
            'fullcalendar': {
                'deps': ['jquery']
            }
        }
    }
};
