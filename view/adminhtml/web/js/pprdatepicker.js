/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jquery:true */
/*eslint max-depth: 0*/

/**
 * First we add function to the datepicker prototype
 *
 * Then we make our own widget based off of the datepicker prototype
 */

/**
 * The entire process is:
 * There are 2 inputs which are transformed in datetimepickers with onSelect Events and onChangeTimeEvents
 * Because time picker from default magento is not allwoing disabled times we have added our own timepicker and hide the magento one, we still use it for the datetime
 * We have an onChangeTime event and onSelect Event for every datepicker. When fixed Lengths are used we hide the end date picker and autoselect the enddate.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'Magento_Catalog/js/price-utils',
            'mage/template',
            'underscore',
            'mage/translate',
            'Magento_Ui/js/modal/alert',
            'mage/calendar',
            'pprtimepicker',
            'Magento_Catalog/js/price-box',
            'maskspin',
            'ajaxq'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($, utils, mageTemplate, _, _t, alert) {
    'use strict';

    /**
     * Main namespace for Salesigniter widgets
     * @type {Object}
     */
    $.salesigniter = $.mage || {};

    var datePickerPrototype = $.datepicker.constructor.prototype;
    var timePickerPrototype = $.timepicker.constructor.prototype;
    /* $.extend(datePickerPrototype, {

     });
     */

    /*
     * Public Utility to format date and time
     * dateObj is a Date Object
     * @returns a formatted datetime string
     */
    $.datepicker.formatDateTime = function (dateFormat, timeFormat, dateObj) {
        var dateFormatted = $.datepicker.formatDate(dateFormat, dateObj);
        var timeFormatted = $.datepicker.formatTime(timeFormat, {
                hour: dateObj.getHours(),
                minute: dateObj.getMinutes(),
                second: dateObj.getSeconds(),
                milliseconds: dateObj.getMilliseconds(),
                microseconds: dateObj.getMicroseconds()
            }
        );
        return dateFormatted + ' ' + timeFormatted;
    };

    $.datepicker._getDateNoTime = function (dateObj) {

        if (typeof dateObj === 'string') {
            dateObj = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateObj);
        }
        var dateModified = new Date(dateObj.getTime());
        dateModified.setHours(0);
        dateModified.setMinutes(0);
        dateModified.setSeconds(0);
        return dateModified;
    }

    $.datepicker._getDateFromDateAndTime = function (dateObj, timeObj, timeFormat) {

        if (typeof dateObj === 'string') {
            dateObj = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateObj);
        }
        var dateModified;
        if (typeof timeObj === 'string' && typeof timeFormat !== 'undefined') {
            timeObj = $.datepicker.parseTime(timeFormat, timeObj);
            dateModified = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), timeObj.hour, timeObj.minute, timeObj.second);
        } else {
            dateModified = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), timeObj.getHours(), timeObj.getMinutes(), timeObj.getSeconds());
        }


        return dateModified;
    }


    $.datepicker.dateDifference = function (fromDate, toDate, type) {
        var startDate = new Date(1970, 0, 1, 0).getTime(),
            now = new Date(),
            toDate = toDate && toDate instanceof Date ? toDate : now,
            diff = toDate - fromDate;
        if (typeof type !== 'undefined' && type == 1) {
            var minutes = Math.floor(diff / 60000);
            var seconds = ((diff % 60000) / 1000).toFixed(0);
            diffDate = {
                years: 0,
                months: 0,
                days: 0,
                hours: 0,
                minutes: minutes,
                seconds: seconds
            };
        } else {
            var date = new Date(startDate + diff),
                years = date.getFullYear() - 1970,
                months = date.getMonth(),
                days = date.getDate() - 1,
                hours = date.getHours(),
                minutes = date.getMinutes(),
                seconds = date.getSeconds(),
                diffDate = {
                    years: 0,
                    months: 0,
                    days: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0
                };

            if (years < 0) return diffDate;
            diffDate.years = years > 0 ? years : 0;
            diffDate.months = months > 0 ? months : 0;
            diffDate.days = days > 0 ? days : 0;
            diffDate.hours = hours > 0 ? hours : 0;
            diffDate.minutes = minutes > 0 ? minutes : 0;
            diffDate.seconds = seconds > 0 ? seconds : 0;
        }
        return diffDate;
    };

    $.datepicker.isRecurringDate = function (recurringDate, dateObj, type) {
        if (type == 'none') {
            return $.datepicker._compareDateTimeObj(recurringDate, dateObj, false) === 0;
        }
        if (type == 'daily') {
            return (recurringDate.getHours() == dateObj.getHours() && recurringDate.getMinutes() == dateObj.getMinutes());
        }
        if (type == 'dayweek') {
            return recurringDate.getDay() == dateObj.getDay();
        }
        if (type == 'monthly') {
            return recurringDate.getDate() == dateObj.getDate();
        }
        if (type == 'yearly') {
            return recurringDate.getDate() == dateObj.getDate() && recurringDate.getMonth() == dateObj.getMonth();
        }

        return false;
    }

    $.datepicker.isRecurringDateBetween = function (recurringDateStart, recurringDateEnd, dateObj, type) {
        if (type == 'notime') {
            return ($.datepicker._compareDateTimeObj(recurringDateStart, dateObj, false) === -1 || $.datepicker._compareDateTimeObj(recurringDateStart, dateObj, false) === 0) && ($.datepicker._compareDateTimeObj(recurringDateEnd, dateObj, false) === 1 || $.datepicker._compareDateTimeObj(recurringDateEnd, dateObj, false) === 0);
        }
        if (type == 'none') {
            return $.datepicker._compareDateTimeObj(recurringDateStart, dateObj, true) === -1 && $.datepicker._compareDateTimeObj(recurringDateEnd, dateObj, true) === 1;
        }
        var dailyResult = (recurringDateStart.getHours() == dateObj.getHours() && recurringDateStart.getMinutes() < dateObj.getMinutes() || recurringDateStart.getHours() < dateObj.getHours()) &&
            (recurringDateEnd.getHours() == dateObj.getHours() && recurringDateEnd.getMinutes() > dateObj.getMinutes() || recurringDateEnd.getHours() > dateObj.getHours());
        if (type == 'daily') {
            return dailyResult;

        }
        if (type == 'dayweek') {
            return (recurringDateStart.getDay() <= dateObj.getDay() && recurringDateEnd.getDay() >= dateObj.getDay()) && dailyResult;
        }
        if (type == 'monthly') {
            return (recurringDateStart.getDate() >= dateObj.getDate() && recurringDateStart.getDate() <= dateObj.getDate()) && dailyResult;
        }
        var yearlyResult = (recurringDateStart.getMonth() == dateObj.getMonth() && recurringDateStart.getDate() <= dateObj.getDate() || recurringDateStart.getMonth() < dateObj.getMonth()) &&
            (recurringDateEnd.getMonth() == dateObj.getMonth() && recurringDateEnd.getDate() > dateObj.getDate() || recurringDateEnd.getMonth() > dateObj.getMonth());
        if (type == 'yearly') {
            return yearlyResult && dailyResult;
        }

        return false;
    }

    $.datepicker.checkDatesOverlap = function (StartA, EndA, StartB, EndB) {
        return (StartA.getTime() < EndB.getTime()) && (EndA.getTime() > StartB.getTime());
    }

    /*
     * Create new public method to set only time, callable as $().datepicker('setTime', date)
     */
    $.datepicker._setTimeDatepicker = function (target, date, withDate) {
        var inst = this._getInst(target);

        if (!inst) {
            return;
        }

        var picker = (inst.settings.showsTime ? 'datetimepicker' : 'datepicker');
        var fromObj = inst.settings.fromObj,
            toObj = inst.settings.toObj,
            fromDate = fromObj[picker]('getDate'),
            toDate = toObj[picker]('getDate');
        var isFrom = false;
        if (inst.id.indexOf('_from') > -1) {
            isFrom = true;
        }

        if (isFrom && !fromDate) {
            return;
        } else if (!isFrom && !toDate) {
            return;
        }

        var tp_inst = this._get(inst, 'timepicker');

        if (tp_inst) {
            this._setDateFromField(inst);
            //tp_inst.$input.prop('focus', true);//this is added so the autofocus is not trigger when the date is changed.
            var tp_date;
            if (date) {
                if (typeof date === "string") {
                    tp_inst._parseTime(date, withDate);
                    tp_date = new Date();
                    tp_date.setHours(tp_inst.hour, tp_inst.minute, tp_inst.second, tp_inst.millisec);
                    tp_date.setMicroseconds(tp_inst.microsec);
                } else {
                    tp_date = new Date(date.getTime());
                    tp_date.setMicroseconds(date.getMicroseconds());
                }
                if (tp_date.toString() === 'Invalid Date') {
                    tp_date = undefined;
                }
                this._setTime(inst, tp_date);
            }
        }

    };

    /**
     *
     * @param timeStringStart
     * @param timeStringEnd
     * @param timeFormat
     * @returns {number}
     * @private
     */
    datePickerPrototype._compareTimeOnly = function (timeStringStart, timeStringEnd, timeFormat) {
        var timeObjStart = $.datepicker.parseTime(timeFormat, timeStringStart),
            dateStart = new Date(2016, 7, 2, timeObjStart.hour, timeObjStart.minute, timeObjStart.second);

        var timeObjEnd = $.datepicker.parseTime(timeFormat, timeStringEnd),
            dateEnd = new Date(2016, 7, 2, timeObjEnd.hour, timeObjEnd.minute, timeObjEnd.second);

        return datePickerPrototype._compareDateTimeObj(dateStart, dateEnd, true);
    };

    /**
     *
     * @param timeString
     * @param ampm
     * @param type if 0 then adds, if 1 then subtract
     * @param interval in minutes
     * @returns String
     */
    datePickerPrototype._addSubtractTime = function (timeString, timeFormat, interval, type) {

        if (typeof type === 'undefined') {
            type = 0;
        }

        var timeObj = $.datepicker.parseTime(timeFormat, timeString),
            date = new Date(2016, 7, 2, timeObj.hour, timeObj.minute, timeObj.second);

        if (type == 0) {
            date.setMinutes(date.getMinutes() + parseInt(interval));
        } else {
            date.setMinutes(date.getMinutes() - parseInt(interval));
        }

        return $.datepicker.formatTime(timeFormat, {
            hour: date.getHours(),
            minute: date.getMinutes(),
            second: date.getSeconds(),
            milliseconds: date.getMilliseconds(),
            microseconds: date.getMicroseconds()
        });
    };

    /**
     * Returns day names
     * @returns Array
     */
    datePickerPrototype._getDayNames = function () {
        return ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    };

    /**
     * Return time format from date
     * @param date
     * @param timeFormat
     * @returns {string|*}
     * @private
     */
    datePickerPrototype._getTimeFromDate = function (date, timeFormat) {
        return $.datepicker.formatTime(timeFormat, {
            hour: date.getHours(),
            minute: date.getMinutes(),
            second: date.getSeconds(),
            milliseconds: date.getMilliseconds(),
            microseconds: date.getMicroseconds()
        });
    };

    /**
     * Return date format from datetime
     * @param dateObj
     * @param dateFormat
     * @returns {string|*}
     * @private
     */
    datePickerPrototype._getDateFromDateTime = function (dateObj, dateFormat) {
        return $.datepicker.formatDate(dateFormat, dateObj);//this.options.dateFormat
    };
    /**
     *
     * @param Date dateObj1
     * @param Date dateObj2
     * @returns {number}
     * @private
     */
    datePickerPrototype._compareDateTimeObj = function (dateObj1, dateObj2, withTime) {
        if (dateObj1.getFullYear() > dateObj2.getFullYear()) return 1;
        if (dateObj1.getFullYear() < dateObj2.getFullYear()) return -1;
        if (dateObj1.getFullYear() == dateObj2.getFullYear()) {
            if (dateObj1.getMonth() > dateObj2.getMonth()) return 1;
            if (dateObj1.getMonth() < dateObj2.getMonth()) return -1;
            if (dateObj1.getMonth() == dateObj2.getMonth()) {
                if (dateObj1.getDate() > dateObj2.getDate()) return 1;
                if (dateObj1.getDate() < dateObj2.getDate()) return -1;
                if (dateObj1.getDate() == dateObj2.getDate()) {
                    if (!withTime) {
                        return 0;
                    } else {
                        if (dateObj1.getHours() > dateObj2.getHours()) return 1;
                        if (dateObj1.getHours() < dateObj2.getHours()) return -1;
                        if (dateObj1.getHours() == dateObj2.getHours()) {
                            if (dateObj1.getMinutes() > dateObj2.getMinutes()) return 1;
                            if (dateObj1.getMinutes() < dateObj2.getMinutes()) return -1;
                            if (dateObj1.getMinutes() == dateObj2.getMinutes()) {
                                if (dateObj1.getSeconds() > dateObj2.getSeconds()) return 1;
                                if (dateObj1.getSeconds() < dateObj2.getSeconds()) return -1;
                                if (dateObj1.getSeconds() == dateObj2.getSeconds()) return 0;
                            }

                        }
                    }
                }
            }
        }
    };

    var attachHandlersOriginal = datePickerPrototype._attachHandlers;
    /*overwrite attach handlers*/

    datePickerPrototype._attachHandlers = function (inst) {
        var id = "#" + inst.id.replace(/\\\\/g, "\\");
        //var inst = $.datepicker._getInst($(id)[0]);
        if (!inst.settings.isAdmin) {
            console.log('mousover');
            $('.itimetd').mouseover(function () {
                datePickerPrototype._doMouseOver(this, id, parseInt($(this).attr('itimetd')));
            });
            $('.itimetd').mouseout(function () {
                datePickerPrototype._doMouseOut(this, id);
            });

            $('.itimetd').removeClass($.datepicker._dayOverClass).removeClass('turnover').removeClass('turnoverBefore').removeClass('turnoverAfter').removeClass('legend-start').removeClass('legend-mail');
        }
        return attachHandlersOriginal.call(this, inst);
    };

    /* Update the datepicker when hovering over a date.
     @param  td         (element) the current cell
     @param  id         (string) the ID of the datepicker instance
     @param  timestamp  (number) the timestamp for this date */
    datePickerPrototype._doMouseOver = function (td, id, timestamp) {
        var inst = $.datepicker._getInst($(id)[0]);
        $(td).addClass($.datepicker._dayOverClass);
        /**
         * create turnover highlight
         */
        var turnoverBeforeValue = $.datepicker._get(inst, 'turnoverBefore');
        turnoverBeforeValue = parseInt(turnoverBeforeValue / 1440);

        var turnoverAfterValue = $.datepicker._get(inst, 'turnoverAfter');
        turnoverAfterValue = parseInt(turnoverAfterValue / 1440);
        var iCounter = 0;

        var arrTurnover = [];
        var tdStart = $(td);
        var tdPrev;
        var monthNotVisible;
        var tdStartTemp, tdStartTemp1;
        var picker = (inst.settings.showsTime ? 'datetimepicker' : 'datetimepicker');
        var fromObj = inst.settings.fromObj,
            toObj = inst.settings.toObj,
            nrSel = inst.settings.fixedRentalLength,
            fromDate = fromObj[picker]('getDate'),
            toDate = toObj[picker]('getDate');
        var isFrom = false;
        if (id.indexOf('_from') > -1) {
            isFrom = true;
        }
        if (nrSel >= 1440) {
            nrSel = parseInt(nrSel / 1440) - 1;
        } else {
            nrSel = 0;
        }

        if (tdStart.hasClass('available-date') && !tdStart.hasClass('ui-state-disabled')) {
            if (isFrom) {
                monthNotVisible = false;
                for (iCounter = 0; iCounter < turnoverBeforeValue; iCounter++) {
                    tdPrev = tdStart.prev();
                    while (true) {
                        if (tdPrev.hasClass('ui-datepicker-other-month')) {
                            tdStart = tdStart.closest('.ui-datepicker-group').prev().find('table tr td').not('.ui-datepicker-other-month').last();
                            tdPrev = tdStart;
                            if (tdPrev.length == 0) {
                                monthNotVisible = true;
                                break;
                            }
                        } else if (tdPrev.length == 0 && tdStart.length > 0) {
                            tdStart = tdStart.parent().prev().find('td').last();
                            tdPrev = tdStart;
                        } else {
                            tdStart = tdPrev;
                            break;
                        }
                    }
                    if (monthNotVisible) break;

                    tdStartTemp1 = tdStart;
                    while (tdStart.hasClass('disabled-day-turnover')) {
                        tdStartTemp1 = tdStart.prev();
                        if (tdStartTemp1.hasClass('ui-datepicker-other-month')) {
                            tdStartTemp1 = tdStart.closest('.ui-datepicker-group').prev().find('table tr td').not('.ui-datepicker-other-month').last();
                        } else if (tdStartTemp1.length == 0) {
                            tdStartTemp1 = tdStart.parent().prev().find('td').last();
                        }
                        tdStart = tdStartTemp1;
                    }

                    tdStart.addClass('turnover turnoverBefore');
                    arrTurnover.push(tdStart);
                }
                tdStart.addClass('legend-start');
            }
            if (nrSel > 0) {
                fromDate = new Date(tdStart.attr('itimetd'));
            }
            if (fromDate && !isFrom) {
                tdStart = $(td);
                monthNotVisible = false;
                var counterTotal = parseInt(turnoverAfterValue);
                if (nrSel > 0) {
                    counterTotal += parseInt(nrSel);
                }
                var fromDateNoTime = $.datepicker._getDateNoTime(fromDate);
                var endDateMouseOver = tdStart.attr('itimetd');

                while (fromDateNoTime.getTime() < endDateMouseOver) {
                    $('td[itimetd="' + fromDateNoTime.getTime() + '"').addClass('legend-selected');
                    fromDateNoTime.setDate(fromDateNoTime.getDate() + 1);
                    arrTurnover.push($('td[itimetd="' + fromDateNoTime.getTime() + '"').first());
                }

                for (iCounter = 0; iCounter < counterTotal; iCounter++) {
                    tdPrev = tdStart.next();
                    while (true) {
                        if (tdPrev.hasClass('ui-datepicker-other-month')) {
                            tdStart = tdStart.closest('.ui-datepicker-group').next().find('table tr td').not('.ui-datepicker-other-month').first();
                            tdPrev = tdStart;
                            if (tdPrev.length == 0) {
                                monthNotVisible = true;
                                break;
                            }
                        } else if (tdPrev.length == 0 && tdStart.length > 0) {
                            tdStart = tdStart.parent().next().find('td').first();
                            tdPrev = tdStart;
                        } else {
                            tdStart = tdPrev;
                            break;
                        }
                    }
                    if (monthNotVisible) break;
                    tdStartTemp = tdStart;
                    while (tdStart.hasClass('disabled-day-turnover')) {
                        tdStartTemp = tdStart.next();
                        if (tdStartTemp.hasClass('ui-datepicker-other-month')) {
                            tdStartTemp = tdStart.closest('.ui-datepicker-group').next().find('table tr td').not('.ui-datepicker-other-month').first();
                        } else if (tdStartTemp.length == 0) {
                            tdStartTemp = tdStart.parent().next().find('td').first();
                        }
                        tdStart = tdStartTemp;
                    }
                    if (iCounter < nrSel) {
                        tdStart.addClass($.datepicker._dayOverClass);
                    } else {
                        tdStart.addClass('turnover turnoverAfter');
                    }
                    arrTurnover.push(tdStart);
                }
                if (arrTurnover.length - 1 >= 0) {
                    arrTurnover[arrTurnover.length - 1].addClass('legend-mail');
                }
            }
            $('.' + $.datepicker._dayOverClass).find('a').addClass('ui-state-active');
            $(td).data('arrTurnover', arrTurnover);

        }
    };

    /* Update the datepicker when no longer hovering over a date.
     @param  td  (element) the current cell
     @param  id  (string) the ID of the datepicker instance */
    datePickerPrototype._doMouseOut = function (td, id) {
        var inst = this._getInst($(id)[0]);
        $('.' + $.datepicker._dayOverClass).find('a').removeClass('ui-state-active');
        $(td).removeClass($.datepicker._dayOverClass);
        if ($(td).data('arrTurnover')) {
            var arrTurnover = $(td).data('arrTurnover');
            var lengthTurnover = arrTurnover.length,
                elementTurnover = null;
            $(td).removeClass('legend-start');
            for (var i = 0; i < lengthTurnover; i++) {
                elementTurnover = arrTurnover[i];
                elementTurnover.removeClass('legend-selected').removeClass('turnover').removeClass('turnoverBefore').removeClass('turnoverAfter').removeClass($.datepicker._dayOverClass).removeClass('legend-mail').removeClass('legend-start');
            }
        }


    };


    /**
     *
     * Functions disables the rest of day based on store_open store_close for that date
     * @param dateObj
     * @param storeHours
     * @param ampm
     * @param timeFormat
     * @returns Array
     * in the form 'disableTimeRanges':
     * [['1am', '2am'], ['3am', '4:01am']]
     *
     */
    datePickerPrototype._disableTimeRangesPerDate = function (dateObj, storeHours, ampm, timeFormat, bookedDates, disabledDates, minimumTime, currentQuantity, availableQuantity) {
        var day;
        var dayNamesEn = $.datepicker._getDayNames();
        var self = this;
        if (dateObj === '') {
            day = 'monday';
        } else {
            day = dayNamesEn[dateObj.getDay()];
        }

        var disabledTimes = [],
            start = storeHours.start[day],
            end = $.datepicker._addSubtractTime(storeHours.end[day], timeFormat, 1);

        if (dateObj !== '' && minimumTime !== 'none') {
            if (datePickerPrototype._compareTimeOnly(start, minimumTime, timeFormat) === -1) {
                start = minimumTime;
            }
        }

        if (ampm) {
            disabledTimes.push(['12am', start]);
            disabledTimes.push([end, '11:55pm']);
        } else {
            disabledTimes.push(['00:00', start]);
            disabledTimes.push([end, '23:55']);
        }
        if (typeof bookedDates !== 'undefined' && (dateObj !== '')) {
            _.each(bookedDates, function (dateElem) {
                //dateobj needs to be in between s-e
                //if notime(dateobj==s) && notime(dateobj) ==e
                //if currentquantity > availablequantity
                //some times can be available, but because of turnovers is impossible to select them
                //the same it is for end dates.The problem are the different quantities.Can be created the possible array for end dates
                //but the time invested is not good right now

                var startDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.s);
                var endDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.e);
                var dateObjNoTime = $.datepicker._getDateNoTime(dateObj);
                //if ($.datepicker.isRecurringDateBetween(startDate, endDate, dateObj, 'none')) {
                //if($.datepicker._getDateNoTime(tartDate) == $.datepicker._getDateNoTime(dateObj)){
                if (currentQuantity > -1 && $.datepicker.isRecurringDateBetween(startDate, endDate, dateObj, 'notime')/*$.datepicker.checkDatesOverlap(startDate, endDate, dateObjNoTime, dateObjNoTime)*/) {
                    if (currentQuantity > availableQuantity - dateElem.q) {
                        if ($.datepicker._getDateNoTime(startDate).getTime() == dateObjNoTime.getTime() && $.datepicker._getDateNoTime(endDate).getTime() == dateObjNoTime.getTime()) {
                            var startTimeFormatted = $.datepicker._getTimeFromDate(startDate, timeFormat);

                            var endTimeFormatted = $.datepicker._getTimeFromDate(endDate, timeFormat);
                            disabledTimes.push([startTimeFormatted, endTimeFormatted]);
                        } else if ($.datepicker._getDateNoTime(startDate).getTime() === dateObjNoTime.getTime()) {
                            var startTimeFormatted = $.datepicker._getTimeFromDate(startDate, timeFormat);
                            var endTimeFormatted = end;
                            disabledTimes.push([startTimeFormatted, endTimeFormatted]);
                        } else if ($.datepicker._getDateNoTime(endDate).getTime() === dateObjNoTime.getTime()) {
                            var startTimeFormatted = start;
                            var endTimeFormatted = $.datepicker._getTimeFromDate(endDate, timeFormat);
                            disabledTimes.push([startTimeFormatted, endTimeFormatted]);
                        }
                    }
                }
                //}

                //}
            });
        }

        if (typeof disabledDates !== 'undefined' && (dateObj !== '')) {
            _.each(disabledDates, function (dateElem) {
                var startDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.s);
                if (dateElem.r == 'daily' || $.datepicker.isRecurringDate(startDate, dateObj, dateElem.r)) {
                    var endDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.e);
                    var startTimeFormatted = $.datepicker._getTimeFromDate(startDate, timeFormat);
                    var endTimeFormatted = $.datepicker._getTimeFromDate(endDate, timeFormat);
                    disabledTimes.push([startTimeFormatted, endTimeFormatted]);
                }
            });
        }


        return disabledTimes;

    };

    timePickerPrototype._injectPprTimepicker = function (dp_inst, date) {
        var idTimepicker = "#" + dp_inst.id.replace(/\\\\/g, "\\") + '-timepicker-selector',
            id = "#" + dp_inst.id.replace(/\\\\/g, "\\"),
            showTimeSelect = $.datepicker._get(dp_inst, 'showTime'),
            timeNoGrid = $.datepicker._get(dp_inst, 'timeNoGrid'),
            ampm = $.datepicker._get(dp_inst, 'ampm'),
            storeHours = $.datepicker._get(dp_inst, 'storeHours'),
            timeFormat = $.datepicker._get(dp_inst, 'timeFormat'),
            bookedDates = dp_inst.settings.bookedDates,
            disableDates = dp_inst.settings.disabledDates,
            minimumPeriod = dp_inst.settings.minimumPeriod,
            stepMinutes = $.datepicker._get(dp_inst, 'stepMinute');

        var currentDate = $.datepicker._getDateDatepicker($(id)[0]);
        var disabledTimeRanges = [],
            timeFormatNew;
        var today = datePickerPrototype._getTimezoneDate();

        var picker = (dp_inst.settings.showsTime ? 'datetimepicker' : 'datetimepicker');
        var fromObj = dp_inst.settings.fromObj,
            toObj = dp_inst.settings.toObj,
            fixedRentalLength = dp_inst.settings.fixedRentalLength,
            fromDate = fromObj[picker]('getDate'),
            firstTimeAvailable = dp_inst.settings.firstTimeAvailable,
            currentQuantity = parseInt(dp_inst.settings.currentQuantity),
            availableQuantity = parseInt(dp_inst.settings.availableQuantity),
            toDate = toObj[picker]('getDate');
        var isFrom = false;
        var idTo = '';
        if (id.indexOf('_from') > -1) {
            isFrom = true;
            idTo = "#" + dp_inst.settings.to.id.replace(/\\\\/g, "\\");
        }


        if (showTimeSelect) {
            //here we check if from date or todate
            if (!timeNoGrid) {
                if (currentDate && datePickerPrototype._compareDateTimeObj(today, currentDate, false) === 0) {
                    disabledTimeRanges = $.datepicker._disableTimeRangesPerDate((currentDate ? currentDate : ''), storeHours, ampm, timeFormat, bookedDates, disableDates, firstTimeAvailable, currentQuantity, availableQuantity);
                } else {
                    if (currentDate && !isFrom && fromDate) {
                        if (datePickerPrototype._compareDateTimeObj(currentDate, fromDate, false) === 0) {
                            var minimumTime = datePickerPrototype._getTimeFromDate(fromDate, timeFormat);
                            if (minimumPeriod >= 1440) {
                                minimumPeriod = (fromDate.getTime() - $.datepicker._getDateNoTime(fromDate).getTime()) / 3600000 - 60;
                            }
                            minimumTime = datePickerPrototype._addSubtractTime(minimumTime, timeFormat, minimumPeriod);
                            disabledTimeRanges = $.datepicker._disableTimeRangesPerDate((currentDate ? currentDate : ''), storeHours, ampm, timeFormat, bookedDates, disableDates, minimumTime, currentQuantity, availableQuantity);
                        } else {
                            disabledTimeRanges = $.datepicker._disableTimeRangesPerDate((currentDate ? currentDate : ''), storeHours, ampm, timeFormat, bookedDates, disableDates, 'none', currentQuantity, availableQuantity);
                        }
                    } else {
                        disabledTimeRanges = $.datepicker._disableTimeRangesPerDate((currentDate ? currentDate : ''), storeHours, ampm, timeFormat, bookedDates, disableDates, 'none', currentQuantity, availableQuantity);
                    }
                }
            }
            var onChangeTime = $.datepicker._get(dp_inst, 'onChangeTime');
            if (ampm) {
                timeFormatNew = 'h:i a';
            } else {
                timeFormatNew = 'H:i';
            }

            $(idTimepicker).pprtimepicker('remove');
            /**
             * The order of events is very important
             * The events needs to be run on timepicker creation, because they were defined after the creation, they couldn't be accessed.
             *
             */
            $(idTimepicker).on('changeTime', function () {
                if (onChangeTime) {
                    onChangeTime.call(self, dp_inst);
                }
            });

            $(idTimepicker).pprtimepicker({
                'step': stepMinutes,
                'appendTo': $(id),
                'useSelect': true,
                //'noneOption': true,
                'timeFormat': timeFormatNew,
                'disableTimeRanges': disabledTimeRanges,
                'forceRoundTime': true
            });
            if (isFrom && fromDate) {
                $(idTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(fromDate, timeFormat));
            } else if (toDate) {
                $(idTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(toDate, timeFormat));
            }
        }
    };

    /* overwrite addTimepicker*/
    /*
     * Magento default timepicker does not work like it should for our needs.
     * We need a single dropdown so we can exclude times. So I have included a better dropdown for our needs
     * We will still use the default timepicker because it has more functionality and is better integrated
     * but we hide it , And use our timepicker only for basic selections and exclusions.
     *
     */
    var addTimePickerOriginal = timePickerPrototype._addTimePicker;

    timePickerPrototype._addTimePicker = function (dp_inst) {
        addTimePickerOriginal.call(this, dp_inst);
        dp_inst.dpDiv.find('.ui-timepicker-div').hide();
        this._injectPprTimepicker(dp_inst, '');
    };

    timePickerPrototype._onTimeChange = function () {
        if (!this._defaults.showTimepicker) {
            return;
        }
        var hour = (this.hour_slider) ? this.control.value(this, this.hour_slider, 'hour') : false,
            minute = (this.minute_slider) ? this.control.value(this, this.minute_slider, 'minute') : false,
            second = (this.second_slider) ? this.control.value(this, this.second_slider, 'second') : false,
            millisec = (this.millisec_slider) ? this.control.value(this, this.millisec_slider, 'millisec') : false,
            microsec = (this.microsec_slider) ? this.control.value(this, this.microsec_slider, 'microsec') : false,
            timezone = (this.timezone_select) ? this.timezone_select.val() : false,
            o = this._defaults,
            pickerTimeFormat = o.pickerTimeFormat || o.timeFormat,
            pickerTimeSuffix = o.pickerTimeSuffix || o.timeSuffix;

        if (typeof(hour) === 'object') {
            hour = false;
        }
        if (typeof(minute) === 'object') {
            minute = false;
        }
        if (typeof(second) === 'object') {
            second = false;
        }
        if (typeof(millisec) === 'object') {
            millisec = false;
        }
        if (typeof(microsec) === 'object') {
            microsec = false;
        }
        if (typeof(timezone) === 'object') {
            timezone = false;
        }

        if (hour !== false) {
            hour = parseInt(hour, 10);
        }
        if (minute !== false) {
            minute = parseInt(minute, 10);
        }
        if (second !== false) {
            second = parseInt(second, 10);
        }
        if (millisec !== false) {
            millisec = parseInt(millisec, 10);
        }
        if (microsec !== false) {
            microsec = parseInt(microsec, 10);
        }
        if (timezone !== false) {
            timezone = timezone.toString();
        }

        var ampm = o[hour < 12 ? 'amNames' : 'pmNames'][0];

        // If the update was done in the input field, the input field should not be updated.
        // If the update was done using the sliders, update the input field.
        var hasChanged = (
            hour !== parseInt(this.hour, 10) || // sliders should all be numeric
            minute !== parseInt(this.minute, 10) ||
            second !== parseInt(this.second, 10) ||
            millisec !== parseInt(this.millisec, 10) ||
            microsec !== parseInt(this.microsec, 10) ||
            (this.ampm.length > 0 && (hour < 12) !== ($.inArray(this.ampm.toUpperCase(), this.amNames) !== -1)) ||
            (this.timezone !== null && timezone !== this.timezone.toString()) // could be numeric or "EST" format, so use toString()
        );

        if (hasChanged) {

            if (hour !== false) {
                this.hour = hour;
            }
            if (minute !== false) {
                this.minute = minute;
            }
            if (second !== false) {
                this.second = second;
            }
            if (millisec !== false) {
                this.millisec = millisec;
            }
            if (microsec !== false) {
                this.microsec = microsec;
            }
            if (timezone !== false) {
                this.timezone = timezone;
            }

            if (!this.inst) {
                this.inst = $.datepicker._getInst(this.$input[0]);
            }

            this._limitMinMaxDateTime(this.inst, true);
        }
        if (this.support.ampm) {
            this.ampm = ampm;
        }

        // Updates the time within the timepicker
        this.formattedTime = $.datepicker.formatTime(o.timeFormat, this, o);
        if (this.$timeObj) {
            if (pickerTimeFormat === o.timeFormat) {
                this.$timeObj.text(this.formattedTime + pickerTimeSuffix);
            }
            else {
                this.$timeObj.text($.datepicker.formatTime(pickerTimeFormat, this, o) + pickerTimeSuffix);
            }
        }

        this.timeDefined = true;
        if (hasChanged) {
            this._updateDateTime();
            //this.$input.focus();//this is changed because some infinite loop on admin
        }
    };

    /***
     * This rewrite was needed because of a bug in timepicker-addon when changing monthyear
     * @param $input
     * @param opts
     * @private
     */
    timePickerPrototype._newInst = function ($input, opts) {
        var tp_inst = new this.constructor,
            inlineSettings = {},
            fns = {},
            overrides, i;

        for (var attrName in this._defaults) {
            if (this._defaults.hasOwnProperty(attrName)) {
                var attrValue = $input.attr('time:' + attrName);
                if (attrValue) {
                    try {
                        inlineSettings[attrName] = eval(attrValue);
                    } catch (err) {
                        inlineSettings[attrName] = attrValue;
                    }
                }
            }
        }

        overrides = {
            beforeShow: function (input, dp_inst) {
                if ($.isFunction(tp_inst._defaults.evnts.beforeShow)) {
                    return tp_inst._defaults.evnts.beforeShow.call($input[0], input, dp_inst, tp_inst);
                }
            },
            onChangeMonthYear: function (year, month, dp_inst) {
                // Update the time as well : this prevents the time from disappearing from the $input field.
                /*Modified because changing the month year updated wrong the input with the current date*/
                var inpVal = tp_inst.$input.val();
                tp_inst._updateDateTime(dp_inst);
                tp_inst.$input.val(inpVal);
                dp_inst.lastVal = inpVal;
                if ($.isFunction(tp_inst._defaults.evnts.onChangeMonthYear)) {
                    tp_inst._defaults.evnts.onChangeMonthYear.call($input[0], year, month, dp_inst, tp_inst);
                }
            },
            onClose: function (dateText, dp_inst) {
                if (tp_inst.timeDefined === true && $input.val() !== '') {
                    tp_inst._updateDateTime(dp_inst);
                }
                if ($.isFunction(tp_inst._defaults.evnts.onClose)) {
                    tp_inst._defaults.evnts.onClose.call($input[0], dateText, dp_inst, tp_inst);
                }
            }
        };
        for (i in overrides) {
            if (overrides.hasOwnProperty(i)) {
                fns[i] = opts[i] || null;
            }
        }

        tp_inst._defaults = $.extend({}, this._defaults, inlineSettings, opts, overrides, {
            evnts: fns,
            timepicker: tp_inst // add timepicker as a property of datepicker: $.datepicker._get(dp_inst, 'timepicker');
        });
        tp_inst.amNames = $.map(tp_inst._defaults.amNames, function (val) {
            return val.toUpperCase();
        });
        tp_inst.pmNames = $.map(tp_inst._defaults.pmNames, function (val) {
            return val.toUpperCase();
        });

        // detect which units are supported
        tp_inst.support = $.timepicker._util._detectSupport(
            tp_inst._defaults.timeFormat +
            (tp_inst._defaults.pickerTimeFormat ? tp_inst._defaults.pickerTimeFormat : '') +
            (tp_inst._defaults.altTimeFormat ? tp_inst._defaults.altTimeFormat : ''));

        // controlType is string - key to our this._controls
        if (typeof(tp_inst._defaults.controlType) === 'string') {
            if (tp_inst._defaults.controlType === 'slider' && typeof($.ui.slider) === 'undefined') {
                tp_inst._defaults.controlType = 'select';
            }
            tp_inst.control = tp_inst._controls[tp_inst._defaults.controlType];
        }
        // controlType is an object and must implement create, options, value methods
        else {
            tp_inst.control = tp_inst._defaults.controlType;
        }

        // prep the timezone options
        var timezoneList = [-720, -660, -600, -570, -540, -480, -420, -360, -300, -270, -240, -210, -180, -120, -60,
            0, 60, 120, 180, 210, 240, 270, 300, 330, 345, 360, 390, 420, 480, 525, 540, 570, 600, 630, 660, 690, 720, 765, 780, 840];
        if (tp_inst._defaults.timezoneList !== null) {
            timezoneList = tp_inst._defaults.timezoneList;
        }
        var tzl = timezoneList.length, tzi = 0, tzv = null;
        if (tzl > 0 && typeof timezoneList[0] !== 'object') {
            for (; tzi < tzl; tzi++) {
                tzv = timezoneList[tzi];
                timezoneList[tzi] = {
                    value: tzv,
                    label: $.timepicker.timezoneOffsetString(tzv, tp_inst.support.iso8601)
                };
            }
        }
        tp_inst._defaults.timezoneList = timezoneList;

        // set the default units
        tp_inst.timezone = tp_inst._defaults.timezone !== null ? $.timepicker.timezoneOffsetNumber(tp_inst._defaults.timezone) :
            ((new Date()).getTimezoneOffset() * -1);
        tp_inst.hour = tp_inst._defaults.hour < tp_inst._defaults.hourMin ? tp_inst._defaults.hourMin :
            tp_inst._defaults.hour > tp_inst._defaults.hourMax ? tp_inst._defaults.hourMax : tp_inst._defaults.hour;
        tp_inst.minute = tp_inst._defaults.minute < tp_inst._defaults.minuteMin ? tp_inst._defaults.minuteMin :
            tp_inst._defaults.minute > tp_inst._defaults.minuteMax ? tp_inst._defaults.minuteMax : tp_inst._defaults.minute;
        tp_inst.second = tp_inst._defaults.second < tp_inst._defaults.secondMin ? tp_inst._defaults.secondMin :
            tp_inst._defaults.second > tp_inst._defaults.secondMax ? tp_inst._defaults.secondMax : tp_inst._defaults.second;
        tp_inst.millisec = tp_inst._defaults.millisec < tp_inst._defaults.millisecMin ? tp_inst._defaults.millisecMin :
            tp_inst._defaults.millisec > tp_inst._defaults.millisecMax ? tp_inst._defaults.millisecMax : tp_inst._defaults.millisec;
        tp_inst.microsec = tp_inst._defaults.microsec < tp_inst._defaults.microsecMin ? tp_inst._defaults.microsecMin :
            tp_inst._defaults.microsec > tp_inst._defaults.microsecMax ? tp_inst._defaults.microsecMax : tp_inst._defaults.microsec;
        tp_inst.ampm = '';
        tp_inst.$input = $input;

        if (tp_inst._defaults.altField) {
            tp_inst.$altInput = $(tp_inst._defaults.altField).css({
                cursor: 'pointer'
            }).focus(function () {
                $input.trigger("focus");
            });
        }

        if (tp_inst._defaults.minDate === 0 || tp_inst._defaults.minDateTime === 0) {
            tp_inst._defaults.minDate = new Date();
        }
        if (tp_inst._defaults.maxDate === 0 || tp_inst._defaults.maxDateTime === 0) {
            tp_inst._defaults.maxDate = new Date();
        }

        // datepicker needs minDate/maxDate, timepicker needs minDateTime/maxDateTime..
        if (tp_inst._defaults.minDate !== undefined && tp_inst._defaults.minDate instanceof Date) {
            tp_inst._defaults.minDateTime = new Date(tp_inst._defaults.minDate.getTime());
        }
        if (tp_inst._defaults.minDateTime !== undefined && tp_inst._defaults.minDateTime instanceof Date) {
            tp_inst._defaults.minDate = new Date(tp_inst._defaults.minDateTime.getTime());
        }
        if (tp_inst._defaults.maxDate !== undefined && tp_inst._defaults.maxDate instanceof Date) {
            tp_inst._defaults.maxDateTime = new Date(tp_inst._defaults.maxDate.getTime());
        }
        if (tp_inst._defaults.maxDateTime !== undefined && tp_inst._defaults.maxDateTime instanceof Date) {
            tp_inst._defaults.maxDate = new Date(tp_inst._defaults.maxDateTime.getTime());
        }
        tp_inst.$input.bind('focus', function () {
            tp_inst._onFocus();
        });

        return tp_inst;
    };


    /**
     * Widget Pprdatepicker
     * @extends $.mage.calendar
     */
    $.widget('salesigniter.pprdatepicker', $.mage.calendar, {

        /**
         * Wrapper for overwrite jQuery UI datepicker function.
         */
        _overwriteGenerateHtml: function () {

            /**
             * We overwrite _generateHTML so we can add turnovers and also flicker style and make inline pickers work to show the selected interval
             *
             * @param {Object} inst - instance datepicker.
             * @return {String} html template
             */
            $.datepicker.constructor.prototype._generateHTML = function (inst) {
                var idTimepicker = inst.id.replace(/\\\\/g, "\\") + '-timepicker-selector',
                    today = this._getTimezoneDate(),
                    isRTL = this._get(inst, 'isRTL'),
                    showTimeSelect = this._get(inst, 'showTime'),
                    showButtonPanel = this._get(inst, 'showButtonPanel'),
                    hideIfNoPrevNext = this._get(inst, 'hideIfNoPrevNext'),
                    navigationAsDateFormat = this._get(inst, 'navigationAsDateFormat'),
                    numMonths = this._getNumberOfMonths(inst),
                    showCurrentAtPos = this._get(inst, 'showCurrentAtPos'),
                    stepMonths = this._get(inst, 'stepMonths'),
                    isMultiMonth = parseInt(numMonths[0], 10) !== 1 || parseInt(numMonths[1], 10) !== 1,
                    currentDate = this._daylightSavingAdjust(!inst.currentDay ? new Date(9999, 9, 9) :
                        new Date(inst.currentYear, inst.currentMonth, inst.currentDay)),
                    minDate = this._getMinMaxDate(inst, 'min'),
                    maxDate = this._getMinMaxDate(inst, 'max'),
                    drawMonth = inst.drawMonth - showCurrentAtPos,
                    drawYear = inst.drawYear,
                    maxDraw,
                    prevText = this._get(inst, 'prevText'),
                    prev,
                    nextText = this._get(inst, 'nextText'),
                    next,
                    currentText = this._get(inst, 'currentText'),
                    gotoDate,
                    controls,
                    buttonPanel,
                    firstDay,
                    showWeek = this._get(inst, 'showWeek'),
                    dayNames = this._get(inst, 'dayNames'),
                    dayNamesMin = this._get(inst, 'dayNamesMin'),
                    monthNames = this._get(inst, 'monthNames'),
                    monthNamesShort = this._get(inst, 'monthNamesShort'),
                    beforeShowDay = this._get(inst, 'beforeShowDay'),
                    showOtherMonths = this._get(inst, 'showOtherMonths'),
                    selectOtherMonths = this._get(inst, 'selectOtherMonths'),
                    defaultDate = this._getDefaultDate(inst),
                    html = '',
                    row = 0,
                    col = 0,
                    selectedDate,
                    cornerClass = ' ui-corner-all',
                    group = '',
                    calender = '',
                    dow = 0,
                    thead,
                    day,
                    daysInMonth,
                    leadDays,
                    curRows,
                    numRows,
                    printDate,
                    dRow = 0,
                    tbody,
                    daySettings,
                    otherMonth,
                    unselectable;

                if (drawMonth < 0) {
                    drawMonth += 12;
                    drawYear--;
                }

                if (maxDate) {
                    maxDraw = this._daylightSavingAdjust(new Date(maxDate.getFullYear(),
                        maxDate.getMonth() - numMonths[0] * numMonths[1] + 1, maxDate.getDate()));
                    maxDraw = minDate && maxDraw < minDate ? minDate : maxDraw;

                    while (this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1)) > maxDraw) {
                        drawMonth--;

                        if (drawMonth < 0) {
                            drawMonth = 11;
                            drawYear--;

                        }
                    }
                }
                inst.drawMonth = drawMonth;
                inst.drawYear = drawYear;
                prevText = !navigationAsDateFormat ? prevText : this.formatDate(prevText,
                    this._daylightSavingAdjust(new Date(drawYear, drawMonth - stepMonths, 1)),
                    this._getFormatConfig(inst));
                prev = this._canAdjustMonth(inst, -1, drawYear, drawMonth) ?
                    '<a class="ui-datepicker-prev ui-corner-all" data-handler="prev" data-event="click"' +
                    ' title="' + prevText + '">' +
                    '<span class="ui-icon ui-icon-circle-triangle-' + (isRTL ? 'e' : 'w') + '">' +
                    '' + prevText + '</span></a>'
                    : hideIfNoPrevNext ? ''
                        : '<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="' +
                        '' + prevText + '"><span class="ui-icon ui-icon-circle-triangle-' +
                        '' + (isRTL ? 'e' : 'w') + '">' + prevText + '</span></a>';
                nextText = !navigationAsDateFormat ?
                    nextText
                    : this.formatDate(nextText,
                        this._daylightSavingAdjust(new Date(drawYear, drawMonth + stepMonths, 1)),
                        this._getFormatConfig(inst));
                next = this._canAdjustMonth(inst, +1, drawYear, drawMonth) ?
                    '<a class="ui-datepicker-next ui-corner-all" data-handler="next" data-event="click"' +
                    'title="' + nextText + '"><span class="ui-icon ui-icon-circle-triangle-' +
                    '' + (isRTL ? 'w' : 'e') + '">' + nextText + '</span></a>'
                    : hideIfNoPrevNext ? ''
                        : '<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="' + nextText + '">' +
                        '<span class="ui-icon ui-icon-circle-triangle-' + (isRTL ? 'w' : 'e') + '">' + nextText +
                        '</span></a>';
                gotoDate = this._get(inst, 'gotoCurrent') && inst.currentDay ? currentDate : today;
                currentText = !navigationAsDateFormat ? currentText :
                    this.formatDate(currentText, gotoDate, this._getFormatConfig(inst));
                controls = !inst.inline ?
                    '<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ' +
                    'ui-corner-all" data-handler="hide" data-event="click">' +
                    this._get(inst, 'closeText') + '</button>'
                    : '';
                buttonPanel = showButtonPanel ?
                    '<div class="ui-datepicker-buttonpane ui-widget-content">' + (isRTL ? controls : '') +
                    (showTimeSelect ? '<div data-handler="selectTime" data-event="click" id="' + idTimepicker + '">Select Time</div>' : '') + (isRTL ? '' : controls) + '</div>' : '';
                firstDay = parseInt(this._get(inst, 'firstDay'), 10);
                firstDay = isNaN(firstDay) ? 0 : firstDay;

                for (row; row < numMonths[0]; row++) {
                    this.maxRows = 4;

                    for (col; col < numMonths[1]; col++) {
                        selectedDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, inst.selectedDay));

                        if (isMultiMonth) {
                            calender += '<div class="ui-datepicker-group';

                            if (numMonths[1] > 1) {
                                switch (col) {
                                    case 0:
                                        calender += ' ui-datepicker-group-first';
                                        cornerClass = ' ui-corner-' + (isRTL ? 'right' : 'left');
                                        break;

                                    case numMonths[1] - 1:
                                        calender += ' ui-datepicker-group-last';
                                        cornerClass = ' ui-corner-' + (isRTL ? 'left' : 'right');
                                        break;

                                    default:
                                        calender += ' ui-datepicker-group-middle';
                                        cornerClass = '';
                                }
                            }
                            calender += '">';
                        }
                        calender += '<div class="ui-datepicker-header ' +
                            'ui-widget-header ui-helper-clearfix' + cornerClass + '">' +
                            (/all|left/.test(cornerClass) && parseInt(row, 10) === 0 ? isRTL ? next : prev : '') +
                            (/all|right/.test(cornerClass) && parseInt(row, 10) === 0 ? isRTL ? prev : next : '') +
                            this._generateMonthYearHeader(inst, drawMonth, drawYear, minDate, maxDate,
                                row > 0 || col > 0, monthNames, monthNamesShort) + // draw month headers
                            '</div><table class="ui-datepicker-calendar"><thead>' +
                            '<tr>';
                        thead = showWeek ?
                            '<th class="ui-datepicker-week-col">' + this._get(inst, 'weekHeader') + '</th>' : '';

                        for (dow; dow < 7; dow++) { // days of the week
                            day = (dow + firstDay) % 7;
                            thead += '<th' + ((dow + firstDay + 6) % 7 >= 5 ?
                                ' class="ui-datepicker-week-end"' : '') + '>' +
                                '<span title="' + dayNames[day] + '">' + dayNamesMin[day] + '</span></th>';
                        }
                        calender += thead + '</tr></thead><tbody>';
                        daysInMonth = this._getDaysInMonth(drawYear, drawMonth);

                        if (drawYear === inst.selectedYear && drawMonth === inst.selectedMonth) {
                            inst.selectedDay = Math.min(inst.selectedDay, daysInMonth);
                        }
                        leadDays = (this._getFirstDayOfMonth(drawYear, drawMonth) - firstDay + 7) % 7;
                        curRows = Math.ceil((leadDays + daysInMonth) / 7); // calculate the number of rows to generate
                        numRows = isMultiMonth ? this.maxRows > curRows ? this.maxRows : curRows : curRows;
                        this.maxRows = numRows;
                        printDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1 - leadDays));

                        for (dRow; dRow < numRows; dRow++) { // create date picker rows
                            calender += '<tr>';
                            tbody = !showWeek ? '' : '<td class="ui-datepicker-week-col">' +
                                this._get(inst, 'calculateWeek')(printDate) + '</td>';

                            for (dow = 0; dow < 7; dow++) { // create date picker days
                                daySettings = beforeShowDay ?
                                    beforeShowDay.apply(inst.input ? inst.input[0] : null, [printDate]) : [true, ''];
                                otherMonth = printDate.getMonth() !== drawMonth;
                                unselectable = otherMonth && !selectOtherMonths || !daySettings[0] ||
                                    minDate && printDate < minDate || maxDate && printDate > maxDate;
                                tbody += '<td class="itimetd ' +
                                    ((dow + firstDay + 6) % 7 >= 5 ? ' ui-datepicker-week-end' : '') + // highlight weekends
                                    (otherMonth ? ' ui-datepicker-other-month' : '') + // highlight days from other months
                                    (printDate.getTime() === selectedDate.getTime() &&
                                    drawMonth === inst.selectedMonth && inst._keyEvent || // user pressed key
                                    defaultDate.getTime() === printDate.getTime() &&
                                    defaultDate.getTime() === selectedDate.getTime() ?
                                        // or defaultDate is current printedDate and defaultDate is selectedDate
                                        ' ' + this._dayOverClass : '') + // highlight selected day
                                    (unselectable ? ' ' + this._unselectableClass + ' ui-state-disabled' : '') +
                                    (otherMonth && !showOtherMonths ? '' : ' ' + daySettings[1] + // highlight custom dates
                                        (printDate.getTime() === currentDate.getTime() ? ' ' + this._currentClass : '') +
                                        (printDate.getDate() === today.getDate() && printDate.getMonth() === today.getMonth() &&
                                        printDate.getFullYear() === today.getFullYear() ? ' ui-datepicker-today' : '')) + '"' +
                                    ((!otherMonth || showOtherMonths) && daySettings[2] ?
                                        ' title="' + daySettings[2] + '"' : '') + // cell title
                                    (unselectable ? '' : 'itimetd="' + printDate.getTime() + '" data-handler="selectDay" data-event="click" data-month="' +
                                        '' + printDate.getMonth() + '" data-year="' + printDate.getFullYear() + '"') + '>' +
                                    (otherMonth && !showOtherMonths ? '&#xa0;' : // display for other months
                                        unselectable ? '<span class="ui-state-default">' + printDate.getDate() + '</span>'
                                            : '<a class="ui-state-default' +
                                            (printDate.getTime() === today.getTime() ? ' ' : '') +
                                            (printDate.getTime() === currentDate.getTime() ? ' ui-state-active' : '') +
                                            (otherMonth ? ' ui-priority-secondary' : '') +
                                            '" href="#">' + printDate.getDate() + '</a>') + '</td>';
                                printDate.setDate(printDate.getDate() + 1);
                                printDate = this._daylightSavingAdjust(printDate);
                            }
                            calender += tbody + '</tr>';
                        }
                        drawMonth++;

                        if (drawMonth > 11) {
                            drawMonth = 0;
                            drawYear++;
                        }
                        calender += '</tbody></table>' + (isMultiMonth ? '</div>' +
                            (numMonths[0] > 0 && col === numMonths[1] - 1 ? '<div class="ui-datepicker-row-break"></div>'
                                : '') : '');
                        group += calender;
                    }
                    html += group;
                }
                html += buttonPanel + ($.ui.ie6 && !inst.inline ?
                    '<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>' : '');
                inst._keyEvent = false;

                return html;
            };
        },
        _updatePriceBox: function (price) {
            var $product = $(this.options.selectorProduct).first(),
                priceBox = $product.find(this.options.selectorProductPrice),
                priceTemplate = mageTemplate(_t('Price: ') + '<span class="price"><%- data.formatted %></span>');

            var priceFormat = '',
                priceCode = 'finalPrice',
                finalPrice = {},
                currency = '';
            if (typeof order !== 'undefined') {
                currency = order.currencySymbol;
            }
            finalPrice.formatted = currency + utils.formatPrice(price, priceFormat);
            if (typeof order !== 'undefined') {
                order.priceReservation = finalPrice.formatted;
                order.hasGlobalDatesSetup = true;

            }
            if (price > 0) {
                priceBox.css('display', '');
                priceBox.html(priceTemplate({data: finalPrice}));
            } else {
                priceBox.html(_t('No Price'));
            }

            if (typeof productConfigure !== 'undefined' && productConfigure.notShow) {
                var startDate = this.element.find('[name="calendar_selector[from]"]').val();
                var endDate = this.element.find('[name="calendar_selector[to]"]').val();
                if (startDate !== '' && endDate !== '' && price > 0) {
                    //Update dates after clicking Ok button from configure popup
                    //console.log('confirmed' + order.priceReservation);
                    productConfigure.onConfirmBtn();
                }
                //console.log('pricebox' + startDate);
            }

        },
        /*Check if intervval between start and end date is minimumPeriod/maximumPeriod, has no disableddate from calendar for start date and end date and there are no booked dates in between*/
        _checkIntervalValid: function () {
            if (this.options.isAdmin) {
                return true;
            }
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate');
            if (!fromDate || !toDate) {
                return false;
            }
            this._getSentDate();
            this._getReturnDate();
            var diff = $.datepicker.dateDifference(fromDate, toDate, 1);
            var self = this;
            if (this.options.minimumPeriod > 0 && diff.minutes < this.options.minimumPeriod) {
                alert({
                    content: _t('Selected Dates are not in the miniumum period allowed')
                });
                return false;
            }

            if (this.options.maximumPeriod > 0 && diff.minutes > this.options.maximumPeriod
            ) {
                alert({
                    content: _t('Selected Dates are not in the maximum period allowed')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.bookedDates, function (dateElem) {
                    var startDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.s);
                    var endDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.e);

                    if (self.options.currentQuantity > -1 && $.datepicker.checkDatesOverlap(startDate, endDate, self.options.sendDate, self.options.returnDate)) {
                        if (self.options.currentQuantity > self.options.availableQuantity - dateElem.q) {

                            return true;
                        }
                    }

                }))) {
                alert({
                    content: _t('You have booked Dates in Between Selected Dates')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.disabledDates, function (date) {
                    var newDateStart = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                    var newDateEnd = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.e);
                    return $.datepicker.isRecurringDateBetween(newDateStart, newDateEnd, fromDate, date.r);
                }))) {

                alert({
                    content: _t('Start Date is disabled for the selected Dates')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.disabledDates, function (date) {
                    var newDateStart = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                    var newDateEnd = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.e);
                    return $.datepicker.isRecurringDateBetween(newDateStart, newDateEnd, toDate, date.r);
                }))) {

                alert({
                    content: _t('End Date is disabled for the selected Dates')
                });
                return false;
            }


            if (!_.isUndefined(_.find(this.options.disabledDatesFull, function (date) {
                    var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                    return $.datepicker.isRecurringDate(newDate, fromDate, date.r);
                }))) {
                alert({
                    content: _t('Start Date is disabled for the selected Dates')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.disabledDatesFull, function (date) {
                    var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                    return $.datepicker.isRecurringDate(newDate, toDate, date.r);
                }))) {
                alert({
                    content: _t('End Date is disabled for the selected Dates')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.disabledDaysWeekStart, function (day) {
                    return (day - 1) == fromDate.getDay()
                }))) {
                alert({
                    content: _t('Start Date is disabled for the selected Dates')
                });
                return false;
            }
            if (!_.isUndefined(_.find(this.options.disabledDaysWeekEnd, function (day) {
                    return (day - 1) == toDate.getDay()
                }))) {
                alert({
                    content: _t('End Date is disabled for the selected Dates')
                });
                return false;
            }
            return true;
        },
        /*get send - return dates. Send - Return dates can be a disabled date from calendar, but not a disabled day from turnover*/
        _getSentDate: function () {
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate');
            var dateTime = $.datepicker._getDateNoTime(fromDate);
            var turnoverBefore = this.options.turnoverBefore;
            var turnoverBeforeTemp = -1;
            var timeFormat = this.options.timeFormat,
                dateFormat = this.options.dateFormat,
                timeIncrement = this.options.stepMinute,
                arrayWithTurnoversBefore = [];
            if (turnoverBefore >= 1440) {
                turnoverBeforeTemp = parseInt(turnoverBefore / 1440);

                var initVal = 0;
                while (initVal < turnoverBeforeTemp) {
                    if (!_.isUndefined(_.find(this.options.disabledDatesFullTurnover, function (date) {
                            var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                            return $.datepicker.isRecurringDate(newDate, dateTime, date.r);
                        }))) {
                        initVal--;

                    }

                    if (!_.isUndefined(_.find(this.options.disabledDaysWeekTurnover, function (day) {
                            return (day - 1) == dateTime.getDay()
                        }))) {
                        initVal--;
                    }
                    dateTime.setDate(dateTime.getDate() - 1);
                    initVal++;
                }

            } else {
                dateTime = fromDate;
                turnoverBeforeTemp = parseInt(turnoverBefore);

                var initVal = 0;
                while (initVal < turnoverBeforeTemp) {
                    if (!_.isUndefined(_.find(this.options.disabledDatesTurnover, function (date) {
                            var newDateStart = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                            var newDateEnd = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.e);
                            return $.datepicker.isRecurringDateBetween(newDateStart, newDateEnd, dateTime, date.r);
                        }))) {
                        initVal = initVal - timeIncrement;
                    }

                    dateTime.setMinutes(dateTime.getMinutes() - timeIncrement);
                    arrayWithTurnoversBefore.push(new Date(dateTime.getTime()));
                    initVal = initVal + timeIncrement;
                }


            }
            this.options.sendDate = dateTime;
            this.options.turnoverBeforeTimes = arrayWithTurnoversBefore;
            $('[name="calendar_selector[turnover_from]"]').val($.datepicker.formatDateTime(dateFormat, timeFormat, dateTime));

        },
        _getReturnDate: function () {
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate');
            var dateTime = $.datepicker._getDateNoTime(toDate);
            var turnoverAfter = this.options.turnoverAfter;
            var turnoverAfterTemp = -1;
            var timeFormat = this.options.timeFormat,
                dateFormat = this.options.dateFormat,
                timeIncrement = this.options.stepMinute,
                arrayWithTurnoversAfter = [];
            if (turnoverAfter >= 1440) {
                turnoverAfterTemp = parseInt(turnoverAfter / 1440);

                var initVal = 0;
                while (initVal < turnoverAfterTemp) {
                    if (!_.isUndefined(_.find(this.options.disabledDatesFullTurnover, function (date) {
                            var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                            return $.datepicker.isRecurringDate(newDate, dateTime, date.r);
                        }))) {
                        initVal--;

                    }

                    if (!_.isUndefined(_.find(this.options.disabledDaysWeekTurnover, function (day) {
                            return (day - 1) == dateTime.getDay()
                        }))) {
                        initVal--;

                    }
                    dateTime.setDate(dateTime.getDate() + 1);
                    initVal++;
                }

            } else {
                dateTime = toDate;
                turnoverAfterTemp = parseInt(turnoverAfter);

                var initVal = 0;
                while (initVal < turnoverAfterTemp) {
                    if (!_.isUndefined(_.find(this.options.disabledDatesTurnover, function (date) {
                            var newDateStart = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.s);
                            var newDateEnd = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", date.e);
                            return $.datepicker.isRecurringDateBetween(newDateStart, newDateEnd, dateTime, date.r);
                        }))) {
                        initVal = initVal - timeIncrement;

                    }

                    dateTime.setMinutes(dateTime.getMinutes() + timeIncrement);
                    arrayWithTurnoversAfter.push(new Date(dateTime.getTime()));
                    initVal = initVal + timeIncrement;
                }

            }
            this.options.returnDate = dateTime;
            this.options.turnoverAfterTimes = arrayWithTurnoversAfter;
            $('[name="calendar_selector[turnover_to]"]').val($.datepicker.formatDateTime(dateFormat, timeFormat, dateTime));

        },
        _updateRentButtonState: function (price, priceWithTax) {
            var $productForm = this.element.closest(this.options.selectorForms);
            var rentButton = $(this.options.buttonsSelector, $productForm).not('.rental-buyout');
            if (price <= 0) {
                rentButton.prop('disabled', true);
            } else {
                rentButton.prop('disabled', false);
            }
            if (this.options.fixedRentalLength > 0) {
                this.element.find('.ui-datepicker-trigger').last().hide();
            }
            this._updatePriceBox(parseFloat(price), parseFloat(priceWithTax));
        },
        _updateStock: function (availableStock, remainingStock, productName) {
            $(this.options.availableStockSelector).html(availableStock);
            $(this.options.stockTableProductName).html(productName);
            $(this.options.remainingStockSelector).html(remainingStock);
            $(this.options.stockTable).removeClass('hidden');
        },
        _getCssAdditions: function (dateObj) {
            if (!_.isUndefined(_.find(this.options.disabledDatesFullTurnover, function (dateStr) {
                    var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateStr.s);
                    return $.datepicker.isRecurringDate(newDate, dateObj, dateStr.r);
                }))) {
                return ' disabled-day-turnover';
            }

            if (!_.isUndefined(_.find(this.options.disabledDaysWeekTurnover, function (day) {
                    return (day - 1) == dateObj.getDay()
                }))) {
                return ' disabled-day-turnover';
            }
            return '';
        },
        _beforeShowDaysStart: function (dateObj) {
            var cssAdditions = this._getCssAdditions(dateObj);
            if (!_.isUndefined(_.find(this.options.disabledDaysWeekStart, function (day) {
                    return (day - 1) == dateObj.getDay()
                }))) {
                return [this.options.isAdmin ? true : false, 'ui-state-disabled disabled-day-start disabled-day' + cssAdditions, _t('Disabled Day')]
            }
            return this._beforeShowDay(dateObj, cssAdditions);
        },
        _beforeShowDaysEnd: function (dateObj) {
            var cssAdditions = this._getCssAdditions(dateObj);
            if (!_.isUndefined(_.find(this.options.disabledDaysWeekEnd, function (day) {
                    return (day - 1) == dateObj.getDay()
                }))) {
                return [this.options.isAdmin ? true : false, 'ui-state-disabled disabled-day-start disabled-day' + cssAdditions, _t('Disabled Day')]
            }
            return this._beforeShowDay(dateObj, cssAdditions);
        },
        _beforeShowDay: function (dateObj, cssAdditions) {
            var self = this;
            if (this.options.isDisabled) {
                return [this.options.isAdmin ? true : false, 'ui-state-disabled', _t('Disabled Because No quantity Available')]
            }
            if (!_.isUndefined(_.find(this.options.bookedDatesFull, function (dateElem) {
                    var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateElem.s);

                    if (self.options.currentQuantity > -1 && $.datepicker.isRecurringDate(newDate, dateObj, 'none')) {
                        if (self.options.currentQuantity > self.options.availableQuantity - dateElem.q) {
                            return true;
                        }
                    }

                }))) {
                return [this.options.isAdmin ? true : false, 'ui-state-disabled booked-date' + cssAdditions, _t('Booked Date')];
            }

            if (!_.isUndefined(_.find(this.options.disabledDatesFull, function (dateStr) {
                    var newDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm", dateStr.s);
                    return $.datepicker.isRecurringDate(newDate, dateObj, dateStr.r);
                }))) {
                return [this.options.isAdmin ? true : false, 'ui-state-disabled disabled-date' + cssAdditions, _t('Disabled Date')]
            }

            return [true, 'available-date' + cssAdditions, _t('Available Date')];
        },
        _mapOptionsToDatePickers: function (self) {
            this.options.fromObj[this._picker()]('option', 'bookedDates', self.options.bookedDates);
            this.options.toObj[this._picker()]('option', 'bookedDates', self.options.bookedDates);
            this.options.fromObj[this._picker()]('option', 'disabledDates', self.options.disabledDates);
            this.options.toObj[this._picker()]('option', 'disabledDates', self.options.disabledDates);
            this.options.fromObj[this._picker()]('option', 'firstTimeAvailable', self.options.firstTimeAvailable);
            this.options.toObj[this._picker()]('option', 'firstTimeAvailable', self.options.firstTimeAvailable);
            this.options.fromObj[this._picker()]('option', 'availableQuantity', self.options.availableQuantity);
            this.options.toObj[this._picker()]('option', 'availableQuantity', self.options.availableQuantity);
            this.options.fromObj[this._picker()]('option', 'minimumPeriod', self.options.minimumPeriod);
            this.options.toObj[this._picker()]('option', 'minimumPeriod', self.options.minimumPeriod);
            this.options.fromObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);
            this.options.toObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);

        },
        /**
         * Sets up fixed length rentals.
         * The template is setup from Helper/Calendar.php getFixedTemplate function
         * which is called from Block/Widget/CalendarWidget.php $this->_calendarHelper->getFixedOptions();
         *
         * @param self
         * @private
         */
        _setupFixedLength: function (self) {
            if (this.options.fixedRentalLength > 0) {
                //this.element.find('.ui-datepicker-trigger').last().hide();
                this._focusDatepicker();
                this.element.find('.fixed_length select').on('change', function () {
                    self.options.fixedRentalLength = parseInt($(this).val());
                    self.options.fromObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);
                    self.options.toObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);
                    self._selectStartDateFieldset(true);
                });
                this.element.find('.fixed_length input').on('change', function () {
                    if ($(this).is(':checked')) {
                        self.options.fixedRentalLength = parseInt($(this).val());
                        self.options.fromObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);
                        self.options.toObj[self._picker()]('option', 'fixedRentalLength', self.options.fixedRentalLength);
                        self._selectStartDateFieldset(true);

                    }
                });
                this._initFixedTriggers();
            }
        },
        _initFixedTriggers: function () {
            this.element.find('.fixed_length select').trigger('change');
            this.element.find('.fixed_length input').trigger('change');
        },
        _initCalendar: function () {
            var today = $.datepicker._getTimezoneDate();
            var self = this;
            var isConfigurable = $(this.options.isConfigurableSelector).length > 0;
            this._mapOptionsToDatePickers(self);
            if (this.options.firstDateAvailable === 0) {
                if (isConfigurable) {
                    $('.configurable_textred').fadeIn();
                }
                return;
            } else if (isConfigurable) {
                $('.configurable_textred').fadeOut();
            }

            /**
             * To Object initialization
             */
            if (!this.options.isAdmin) {
                this.options.fromObj[this._picker()]('option', 'minDate', $.datepicker.parseDateTime("yy-mm-dd", "HH:mm:ss", this.options.firstDateAvailable));
            }
            if (this.options.fromDateInitial != '') {
                var newFromDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm:ss", this.options.fromDateInitial);
                this.options.fromDateInitial = newFromDate;
                this.options.fromObj[this._picker()]('setDate', newFromDate);
            }
            if (this.options.toDateInitial != '') {
                var newToDate = $.datepicker.parseDateTime("yy-mm-dd", "HH:mm:ss", this.options.toDateInitial);
                this.options.toDateInitial = newToDate;
                this.options.toObj[this._picker()]('setDate', newToDate);
                this.options.toObj[this._picker()]('option', 'minDate', newFromDate);
                this._selectStartDateFieldset();
            }

            this.element.unmask();
            this._updateRentButtonState(self.options.currentPrice, self.options.currentPriceWithTax);
            this.options.fromObj[this._picker()]('option', 'beforeShow', $.proxy(function (input, inst) {
                var instFrom = $.datepicker._getInst(this.options.fromObj[0]);
                var fromDate = this.options.fromObj[this._picker()]('getDate');
                var idFromTimepicker = "#" + instFrom.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var timeFormatNew = this.options.timeFormat;

                if (fromDate && $(idFromTimepicker).length > 0 && $(idFromTimepicker).val() !== undefined && $(idFromTimepicker).val() !== "" || fromDate && $(idFromTimepicker).length === 0) {
                    $(idFromTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(fromDate, timeFormatNew));
                } else if (fromDate) {
                    //this._resetPickers();
                }

                return true;

            }, this));

            this.options.fromObj[this._picker()]('option', 'onClose', $.proxy(function (input, inst) {
                var instFrom = $.datepicker._getInst(this.options.fromObj[0]);
                var fromDate = this.options.fromObj[this._picker()]('getDate');
                var idFromTimepicker = "#" + instFrom.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var timeFormatNew = this.options.timeFormat;
                if (fromDate && $(idFromTimepicker).length > 0 && $(idFromTimepicker).val() !== undefined && $(idFromTimepicker).val() !== "" || fromDate && $(idFromTimepicker).length === 0) {
                    $(idFromTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(fromDate, timeFormatNew));
                } else if (fromDate) {
                    alert({
                        content: _t('No Times Available for the date')
                    });
                    this._resetPickers();

                }

                if (this.options.fixedRentalLength <= 0) {
                    this._selectStartDateFieldset();
                } else {
                    this._selectStartDateFieldset(true);
                }

                return true;
            }, this));
            this.options.toObj[this._picker()]('option', 'beforeShow', $.proxy(function (input, inst) {
                if (this.options.fixedRentalLength > 0) {
                    return false;
                }

                var instFrom = $.datepicker._getInst(this.options.fromObj[0]);
                var instTo = $.datepicker._getInst(this.options.toObj[0]);
                var toDate = this.options.toObj[this._picker()]('getDate');
                var idFromTimepicker = "#" + instFrom.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var idToTimepicker = "#" + instTo.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var timeFormatNew = this.options.timeFormat;
                if (toDate && $(idToTimepicker).length > 0 && $(idToTimepicker).val() !== undefined && $(idToTimepicker).val() !== "" || toDate && $(idToTimepicker).length === 0) {
                    $(idToTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(toDate, timeFormatNew));
                } else if (toDate) {
                    //this._resetPickers();
                }
                return true;

            }, this));

            this.options.toObj[this._picker()]('option', 'onClose', $.proxy(function (input, inst) {
                var instFrom = $.datepicker._getInst(this.options.fromObj[0]);
                var instTo = $.datepicker._getInst(this.options.toObj[0]);
                var toDate = this.options.toObj[this._picker()]('getDate');
                var idFromTimepicker = "#" + instFrom.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var idToTimepicker = "#" + instTo.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                var timeFormatNew = this.options.timeFormat;
                if (toDate && $(idToTimepicker).length > 0 && $(idToTimepicker).val() !== undefined && $(idToTimepicker).val() !== "" || toDate && $(idToTimepicker).length === 0) {
                    $(idToTimepicker).pprtimepicker('setTime', $.datepicker._getTimeFromDate(toDate, timeFormatNew));
                } else if (toDate) {
                    alert({
                        content: _t('No Times Available for the date')
                    });
                    this._resetPickers();
                }

                if (this.options.fixedRentalLength <= 0) {
                    this._selectStartDateFieldset();
                }
                return true;
            }, this));
            this.options.fromObj[this._picker()]('option', 'beforeShowDay', $.proxy(this._beforeShowDaysStart, this));
            this.options.toObj[this._picker()]('option', 'beforeShowDay', $.proxy(this._beforeShowDaysEnd, this));

            this._setupFixedLength(self);

        },
        /**
         * Updates booked dates via ajax is used for when
         * Bundle, Configurable, or Rental Shipping options change
         *
         * @private
         */
        _updateTimePickerInventory: function () {
            var self = this,
                dataFormSerialized;
            var $productForm = this.element.closest(this.options.selectorForms);
            if ($productForm.length > 0) {
                dataFormSerialized = $productForm.serialize();
            } else {
                dataFormSerialized = this.element.find(':input').serialize();
            }
            self.options.currentPrice = 0;
            if (this.options.isAdmin && this.element.find(this.options.selectorProductPrice).length == 0) {
                this.element.append('<div class="price-box" style="margin-top:15px; font-size: 15px; font-weight: bold;" data-role="priceBox"></div>');
            }
            /*
            * Updated booking is not needed.
            * Maybe show an information after dates are selected or in bundle a table with qtys available.
            *

            if ($.ajaxq.isRunning('booked')) {
                $('body').trigger('processStop');
                $.ajaxq.abort('booked');
            }
            $.ajaxq('booked', {
                url: this.options.updateBookedUrl,
                data: dataFormSerialized,
                type: 'post',
                global: false,
                dataType: 'json',
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    $('body').trigger('processStop');

                    _.extend(self.options, res);

                    self._initCalendar();
                    //self._createFullDayDisabled();

                }
            });*/
            self._initCalendar();
        },
        _updatePricing: function () {
            if (this.options.sirentProductId == 0) {
                this.element.find('.sirent-go').trigger('click');
                return;
            }
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate');
            if (!fromDate || !toDate) {
                return;
            }
            var self = this,
                dataFormSerialized;
            var $productForm = this.element.closest(this.options.selectorForms);
            if ($productForm.length > 0) {
                dataFormSerialized = $productForm.serialize();
            } else {
                dataFormSerialized = this.element.find(':input').serialize();
            }
            self.options.currentPrice = 0;
            /*if ($.ajaxq.isRunning('updatePricing')) {
                $('body').trigger('processStop');
                $.ajaxq.abort('updatePricing');
            }*/
            $.ajax({
                url: self.options.priceUpdateUrl,
                data: dataFormSerialized,
                type: 'post',
                global: false,
                dataType: 'json',
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    $('body').trigger('processStop');
                    self.options.currentPrice = parseFloat(res.finalPrice.amount);
                    self.options.currentPriceWithTax = parseFloat(res.finalPrice.amountTax);
                    if (res.finalPrice.amountSpecial != '') {
                        self.options.currentPrice = parseFloat(res.finalPrice.amountSpecial);
                        self.options.currentPriceWithTax = parseFloat(res.finalPrice.amountSpecialTax);
                    }
                    self._updateRentButtonState(self.options.currentPrice, self.options.currentPriceWithTax);
                    self._updateStock(res.availableQty, res.remainingQty, res.productName);
                }
            });
        },
        _generateGridSelection: function () {
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate'),
                el = this.element,
                timeFormat = this.options.timeFormat,
                timeIncrement = this.options.stepMinute;
            if (fromDate && toDate && $.datepicker._compareDateTimeObj(fromDate, toDate, false) === 0) {

                var timeStringStart = '',
                    startDate = fromDate,
                    endDate = toDate;

                el.find('td[time_start]').not('.busy').removeClass('selected-time').removeClass('turnoverBefore').removeClass('turnoverAfter').addClass('available');

                while (startDate.getTime() < endDate.getTime()) {
                    timeStringStart = $.datepicker.formatTime(timeFormat, {
                        hour: startDate.getHours(),
                        minute: startDate.getMinutes(),
                        second: startDate.getSeconds(),
                        milliseconds: startDate.getMilliseconds(),
                        microseconds: startDate.getMicroseconds()
                    });

                    el.find('td[time_start="' + timeStringStart + '"]').removeClass('available').addClass('selected-time');
                    startDate.setMinutes(startDate.getMinutes() + timeIncrement);
                }
                if (this.options.turnoverAfterTimes) {
                    _.each(this.options.turnoverAfterTimes, function (dateElem) {
                        timeStringStart = $.datepicker.formatTime(timeFormat, {
                            hour: dateElem.getHours(),
                            minute: dateElem.getMinutes(),
                            second: dateElem.getSeconds(),
                            milliseconds: dateElem.getMilliseconds(),
                            microseconds: dateElem.getMicroseconds()
                        });

                        el.find('td[time_start="' + timeStringStart + '"]').removeClass('available').addClass('turnoverAfter');

                    });
                }

                if (this.options.turnoverBeforeTimes) {
                    _.each(this.options.turnoverBeforeTimes, function (dateElem) {
                        timeStringStart = $.datepicker.formatTime(timeFormat, {
                            hour: dateElem.getHours(),
                            minute: dateElem.getMinutes(),
                            second: dateElem.getSeconds(),
                            milliseconds: dateElem.getMilliseconds(),
                            microseconds: dateElem.getMicroseconds()
                        });

                        el.find('td[time_start="' + timeStringStart + '"]').removeClass('available').addClass('turnoverBefore');

                    });
                }

                if (fromDate && toDate && this.options.bookedDates && datePickerPrototype._compareDateTimeObj(fromDate, toDate, false) === 0) {
                    var
                        storeHours = this.options.storeHours,
                        ampm = this.options.ampm,
                        bookedDates = this.options.bookedDates,
                        disableDates = this.options.disabledDates,
                        currentQuantity = parseInt(this.options.currentQuantity),
                        availableQuantity = parseInt(this.options.availableQuantity),
                        disabledTimeRanges = $.datepicker._disableTimeRangesPerDate(fromDate, storeHours, ampm, timeFormat, bookedDates, disableDates, 'none', currentQuantity, availableQuantity);

                    var iStart = 0;
                    _.each(disabledTimeRanges, function (dateElem) {
                        if (iStart >= 2) {
                            var startDate = $.datepicker._getDateFromDateAndTime(fromDate, dateElem[0], timeFormat);
                            var endDate = $.datepicker._getDateFromDateAndTime(fromDate, dateElem[1], timeFormat);
                            while (startDate.getTime() < endDate.getTime()) {
                                timeStringStart = $.datepicker.formatTime(timeFormat, {
                                    hour: startDate.getHours(),
                                    minute: startDate.getMinutes(),
                                    second: startDate.getSeconds(),
                                    milliseconds: startDate.getMilliseconds(),
                                    microseconds: startDate.getMicroseconds()
                                });

                                el.find('td[time_start="' + timeStringStart + '"]').removeClass('available').removeClass('selected-time').addClass('busy');
                                startDate.setMinutes(startDate.getMinutes() + timeIncrement);
                            }
                        }
                        iStart++;
                    });
                }
            }
        },
        _focusDatepicker: function () {
            var instTo = $.datepicker._getInst(this.options.toObj[0]);
            var idTo = "#" + instTo.id.replace(/\\\\/g, "\\");
            $(idTo).parent().find('.ui-datepicker-trigger').last().hide();

            $(idTo).focusin(function () {
                $('.ui-datepicker-calendar').css("display", "none");
            });
        },
        _selectStartDateFieldset: function (changeTime) {
            var fromDate = this.options.fromObj[this._picker()]('getDate'),
                toDate = this.options.toObj[this._picker()]('getDate');
            if (changeTime && this.options.fixedRentalLength > 0 && fromDate) {
                var instTo = $.datepicker._getInst(this.options.toObj[0]);
                var idTo = "#" + instTo.id.replace(/\\\\/g, "\\");
                var fromDate = this.options.fromObj[this._picker()]('getDate');
                $(idTo).datepicker('setDate', new Date(fromDate.getTime() + this.options.fixedRentalLength * 60 * 1000));
                toDate = this.options.toObj[this._picker()]('getDate');
                $(idTo).parent().find('.ui-datepicker-trigger').last().hide();
                this._generateGridSelection();
            } else if (changeTime && this.options.fixedRentalLength > 0 && !fromDate) {
                var instTo = $.datepicker._getInst(this.options.toObj[0]);
                var idTo = "#" + instTo.id.replace(/\\\\/g, "\\");
                $(idTo).parent().find('.ui-datepicker-trigger').last().hide();
            }

            if (fromDate && toDate && (this.options.oldDates === '') ||
                fromDate && toDate && (this.options.oldDates !== '') &&
                ($.datepicker._compareDateTimeObj(fromDate, this.options.oldDates[0], true) != 0 ||
                    $.datepicker._compareDateTimeObj(toDate, this.options.oldDates[1], true) != 0)
            ) {
                this.options.oldDates = [fromDate, toDate];
                if (this._checkIntervalValid()) {
                    this._updatePricing();
                } else {
                    this._resetPickers();
                }
            }
        },
        _constructCalendar: function (dateStr) {
            var el = this.element,
                self = this;
            /*never show legend in admin*/
            if (!this.options.isAdmin && $('#legend_details_template').length > 0 && !this.options.hasLegend) {
                var legendDetails = $($('#legend_details_template').html()),
                    el = this.element;
                this.options.hasLegend = true;
                el.append(legendDetails);
            }
            if (this.options.sirentProductId == 0 && !this.options.hasGoButton) {
                var buttonTemplate = $($('#button_template').html());
                el.append(buttonTemplate);
                this.options.hasGoButton = true;
                el.find('.sirent-go').click(function () {
                    if (!el.parent().hasClass('.sirent-input')) {
                        el.wrapAll("<div class='sirent-input'></div>");
                    }
                    var dataFormSerialized = el.parent().find(':input').serialize();
                    $.ajax({
                        url: self.options.changeGlobalsUrl,
                        data: dataFormSerialized,
                        type: 'post',
                        dataType: 'json',
                        beforeSend: function () {
                            $('body').trigger('processStart');

                        },
                        success: function (res) {
                            $('body').trigger('processStop');
                            order.hasGlobalDatesSetup = true;
                        }
                    });
                });
                el.find('.sirent-changeall').click(function () {
                    if (!el.parent().hasClass('.sirent-inputchange')) {
                        el.wrapAll("<div class='sirent-inputchange'></div>");
                    }
                    var dataFormSerialized = el.parent().find(':input').serialize();
                    $.ajax({
                        url: self.options.changeDatesOnProductsUrl,
                        data: dataFormSerialized,
                        type: 'post',
                        dataType: 'json',
                        beforeSend: function () {
                            $('body').trigger('processStart');

                        },
                        success: function (res) {
                            $('body').trigger('processStop');

                            if (res.itemId) {
                                $.each(res.itemId, function (key, item) {
                                    order.productConfigureAddFields['item[' + item + '][configured]'] = 1;
                                    order.orderItemChanged = true;
                                    $.each(res.itemConfigs[item], function (keyConfig, itemConfig) {
                                        if (Object.prototype.toString.call(itemConfig) === '[object Object]') {
                                            for (var key in itemConfig) {
                                                if (Object.prototype.toString.call(itemConfig[key]) === '[object Object]') {
                                                    for (var key2 in itemConfig[key]) {
                                                        if (Object.prototype.toString.call(itemConfig[key][key2]) === '[object Object]') {
                                                            for (var key3 in itemConfig[key][key2]) {
                                                                order.productConfigureAddFields['item[' + item + '][' + keyConfig + '][' + key + '][' + key2 + '][' + key3 + ']'] = itemConfig[key][key2][key3];
                                                            }
                                                        } else {
                                                            order.productConfigureAddFields['item[' + item + '][' + keyConfig + '][' + key + '][' + key2 + ']'] = itemConfig[key][key2];
                                                        }
                                                    }
                                                } else {
                                                    order.productConfigureAddFields['item[' + item + '][' + keyConfig + '][' + key + ']'] = itemConfig[key];
                                                }
                                            }
                                        } else {
                                            order.productConfigureAddFields['item[' + item + '][' + keyConfig + ']'] = itemConfig;
                                        }
                                    });
                                });

                                order.itemsUpdate();
                            }
                        }
                    });
                });
            }
            if (this.options.showTime && this.options.sirentProductId > 0 && !this.options.timeNoGrid) {
                var gridDiv = $($('#day_details_template').html());
                var day;
                var dayNamesEn = $.datepicker._getDayNames();

                if (dateStr === '') {
                    day = 'monday';
                } else {
                    day = dayNamesEn[dateStr.getDay()];
                }

                if (el.find('.day-detail-container').length > 0) {
                    el.find('.day-detail-container').remove();
                }

                var tableHeader = '',
                    tableBody = '',
                    timeIncrement = this.options.stepMinute,
                    timeStringStart = '',
                    startTime = $.datepicker.parseTime(this.options.timeFormat, this.options.storeHours.start[day]),
                    endTime = $.datepicker.parseTime(this.options.timeFormat, $.datepicker._addSubtractTime(this.options.storeHours.end[day], this.options.timeFormat, 1, 1)),
                    startDate = new Date(2016, 7, 2, startTime.hour, startTime.minute, startTime.second),
                    endDate = new Date(2016, 7, 2, endTime.hour, endTime.minute + 1, endTime.second);

                while (startDate.getTime() < endDate.getTime()) {
                    timeStringStart = $.datepicker.formatTime(this.options.timeFormat, {
                        hour: startDate.getHours(),
                        minute: startDate.getMinutes(),
                        second: startDate.getSeconds(),
                        milliseconds: startDate.getMilliseconds(),
                        microseconds: startDate.getMicroseconds()
                    });
                    tableHeader += '<td>' + timeStringStart + '</td>';
                    tableBody += '<td time_start="' + timeStringStart + '" class="available">&nbsp;</td>';
                    startDate.setMinutes(startDate.getMinutes() + timeIncrement);
                }

                gridDiv.find('.table_head').html(tableHeader);
                gridDiv.find('.table_body').html(tableBody);
                gridDiv.attr('time_increment', timeIncrement);
                el.append(gridDiv);
            }
        },
        _updatesTimePicker: function (type) {
            if (this.options.showTime) {
                var idTimepicker, id, selectedTimeAsDate, inst;
                if (type == 'from') {
                    inst = $.datepicker._getInst(this.options.fromObj[0]);
                } else {
                    inst = $.datepicker._getInst(this.options.toObj[0]);
                }
                idTimepicker = "#" + inst.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                id = "#" + inst.id.replace(/\\\\/g, "\\");
                selectedTimeAsDate = $(idTimepicker).pprtimepicker('getTime'/*, currentDate*/);
                /*the call to setdateortime updates the datepicker and injects the timepicker again*/
                //$.datepicker._setTimeDatepicker($(id)[0], selectedTimeAsDate);
                $(id).datepicker('setTime', selectedTimeAsDate);
            }

        },
        _updateTimeGrid: function () {
            if (this.options.showTime && !this.options.timeNoGrid) {
                var fromDate = this.options.fromObj[this._picker()]('getDate'),
                    toDate = this.options.toObj[this._picker()]('getDate');
                if (fromDate && toDate && $.datepicker._compareDateTimeObj(fromDate, toDate, false) === 0) {
                    this._constructCalendar(fromDate);
                    this._generateGridSelection();
                }
            }
        },
        _resetPickers: function (isDisabled) {
            if (typeof isDisabled === 'undefined') {
                isDisabled = false;
            }
            //if (isDisabled && !this.options.isDisabled) {
            this.options.fromObj[this._picker()]('setDate', null);
            this.options.toObj[this._picker()]('setDate', null);
            this.options.sendDate = null;
            this.options.returnDate = null;
            this.options.turnoverAfterTimes = [];
            this.options.turnoverBeforeTimes = [];
            this.options.oldDates = '';
            this._updateRentButtonState(0, 0);
            //}
            this.options.isDisabled = isDisabled;
        },
        /**
         * creates two instances of datetimepicker for date range selection
         * @protected
         */
        _initPicker: function () {
            var
                self = this;
            this.options.isAdmin = true;
            this.options.attributesSelector = 'input.bundle.option, select.bundle.option, textarea.bundle.option, .super-attribute-select, #product-options-wrapper input.qty';
            this.options.optionsSelector = '.product-custom-option';
            this.options.isBundleSelector = '[name^="bundle_option"]';
            this.options.isConfigurableSelector = '[name^="super_attribute"]';
            this.options.qtyFieldSelector = '[name="qty"]';


            // selector of parental block of prices and swatches (need to know where to seek for price block)
            this.options.selectorProduct = '#product_composite_configure_form';
            // selector of price wrapper (need to know where set price)
            this.options.selectorProductPrice = '[data-role=priceBox]';
            this.options.okButtonSelector = '[data-role=action]';

            this.options.buttonsSelector = '.tocart';
            this.options.pricePprSelector = '.pricing-ppr';
            // selector of price wrapper (need to know where set price)

            //stock selector
            this.options.availableStockSelector = '.stock-available-qty .available-stock';
            this.options.remainingStockSelector = '.stock-available-qty .remaining-stock';
            this.options.stockTable = '.stock-available-qty';
            this.options.stockTableProductName = '.stock-available-qty .product-name-stock';

            this.options.selectorForms = '#product_addtocart_form, #product_composite_configure_form, [data-role=tocart-form], .form.map.checkout';
            this.options.currentQuantity = -1;
            this.options.oldDates = '';
            var $productForm = this.element.closest(this.options.selectorForms);
            if ($productForm.length > 0) {
                var optionsInput = $(this.options.attributesSelector, $productForm),
                    customOptionsInput = $(this.options.optionsSelector, $productForm),
                    isBundle = $(this.options.isBundleSelector, $productForm).length > 0,
                    qtyInput = $(this.options.qtyFieldSelector, $productForm);
                this.options.currentQuantity = parseInt(qtyInput.val());
                var self = this;
                var buyoutButton = $(this.options.buttonsSelector, $productForm).filter('.rental-buyout').first();
                var rentButton = $(this.options.buttonsSelector, $productForm).not('.rental-buyout').first();
                buyoutButton.on('mousedown', function () {
                    $productForm.append('<input type="hidden" class="is_buyout" name="is_buyout" value="1"/>');
                });

                rentButton.on('mousedown', function () {
                    $('.is_buyout', $productForm).remove();
                });

                customOptionsInput.on('change', $.proxy(function (event) {
                    //if (event.originalEvent !== undefined) {
                    //this._updateTimePickerInventory();
                    if (this._checkIntervalValid()) {
                        this._updatePricing();
                    } else {
                        this._resetPickers();
                    }
                    //}

                }, self));

                optionsInput.on('change', $.proxy(function (event) {
                    /**
                     * Checks if the event was done by the user.
                     * For some reason magento generates a lot of events
                     * which implies multiple calls to onchange event
                     */
                    //if (event.originalEvent !== undefined) {
                    this._updateTimePickerInventory();
                    if (this._checkIntervalValid()) {
                        this._updatePricing();
                    } else {
                        this._resetPickers();
                    }
                    //}

                }, self));

                qtyInput.on('change', $.proxy(function (event) {
                    //if (event.originalEvent !== undefined) {
                    this.options.currentQuantity = parseInt(qtyInput.val());
                    this.options.fromObj[this._picker()]('option', 'currentQuantity', this.options.currentQuantity);
                    this.options.toObj[this._picker()]('option', 'currentQuantity', this.options.currentQuantity);
                    if (this.options.currentQuantity > this.options.availableQuantity) {
                        if (this.options.currentQuantity === 1 && isBundle) {
                            this._resetPickers();
                        } else {
                            this._resetPickers(true);
                        }
                    } else {
                        if (this._checkIntervalValid()) {
                            this._updatePricing();
                        } else {
                            this._resetPickers(false);
                        }
                    }
                    //}

                }, self));

            }


            if (this.options.from && this.options.to) {
                if (this.options.fromDateInitial == '' && this.options.toDateInitial == '') {
                    this._updateRentButtonState(0, 0);
                }
                this.options.fromObj = this.element.find('#' + this.options.from.id);
                this.options.toObj = this.element.find('#' + this.options.to.id);
                this._constructCalendar('');
                this.element.mask({
                    spinner: {lines: 10, length: 5, width: 3, radius: 8},
                    delay: 300,
                    overlayOpacity: 0.5
                });

                /**
                 * From Object Initialization
                 */
                this.options.onChangeTime = $.proxy(function () {
                    this.options.sendDate = null;
                    this.options.returnDate = null;
                    this.options.turnoverAfterTimes = [];
                    this.options.turnoverBeforeTimes = [];
                    var instFrom = $.datepicker._getInst(this.options.fromObj[0]);
                    var instTo = $.datepicker._getInst(this.options.toObj[0]);
                    var fromDate = this.options.fromObj[this._picker()]('getDate');
                    var toDate = this.options.toObj[this._picker()]('getDate');
                    if (instFrom) {
                        var idFromTimepicker = "#" + instFrom.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                    }
                    if (instFrom) {
                        var idFrom = "#" + instFrom.id.replace(/\\\\/g, "\\");
                    }
                    if (instTo) {
                        var idTo = "#" + instTo.id.replace(/\\\\/g, "\\");
                    }
                    if (idFromTimepicker) {
                        var selectedTimeFromAsDate = $(idFromTimepicker).pprtimepicker('getTime');
                    }

                    if (idFrom && fromDate && $(idFromTimepicker).length > 0 && $(idFromTimepicker).val() !== undefined && $(idFromTimepicker).val() !== "" || fromDate && $(idFromTimepicker).length === 0) {
                        $(idFrom).datepicker('setTime', selectedTimeFromAsDate);
                        // $.datepicker._setTimeDatepicker($(idFrom)[0], selectedTimeAsDate);
                    }

                    if (fromDate && toDate && idTo) {

                        var newFromDate = $.datepicker._getDateFromDateAndTime(fromDate, selectedTimeFromAsDate);
                        if ($.datepicker._compareDateTimeObj(newFromDate, toDate, true) === 1) {
                            //$.datepicker._setDateDatepicker($(idTo)[0], null);
                            $(idTo).datepicker('setDate', null);
                        }
                    }

                    //this._updatesTimePicker('from');

                }, this);
                this.options.onSelect = $.proxy(function (selectedDate) {
                    this.options.toObj[this._picker()]('option', 'minDate', selectedDate);
                    this.options.toObj[this._picker()]('setDate', null);
                    this.options.sendDate = null;
                    this.options.returnDate = null;
                    this.options.turnoverAfterTimes = [];
                    this.options.turnoverBeforeTimes = [];
                    this._updatesTimePicker('from');
                    this._updateTimeGrid();
                }, this);

                this.options.beforeShow = $.proxy(function (input, inst) {
                    return false;
                }, this);

                // calls parent function so we don't lose its functionality
                $.mage.calendar.prototype._initPicker.call(this, this.options.fromObj);

                this.options.onChangeTime = $.proxy(function () {
                    var instTo = $.datepicker._getInst(this.options.toObj[0]);
                    var toDate = this.options.toObj[this._picker()]('getDate');
                    if (instTo) {
                        var idToTimepicker = "#" + instTo.id.replace(/\\\\/g, "\\") + '-timepicker-selector';
                        var idTo = "#" + instTo.id.replace(/\\\\/g, "\\");
                    }


                    if (idTo && toDate && $(idToTimepicker).length > 0 && $(idToTimepicker).val() !== undefined && $(idToTimepicker).val() !== "" || toDate && $(idToTimepicker).length === 0) {
                        var selectedTimeAsDate = $(idToTimepicker).pprtimepicker('getTime');
                        $(idTo).datepicker('setTime', selectedTimeAsDate);
                        //$.datepicker._setTimeDatepicker($(idTo)[0], selectedTimeAsDate);
                    }

                    this._generateGridSelection();
                }, this);

                this.options.onSelect = $.proxy(function (selectedDate) {
                    this._updatesTimePicker('TO');
                    this._updateTimeGrid();
                }, this);

                this.options.beforeShow = $.proxy(function (input, inst) {
                    return false;
                }, this);

                // calls parent function so we don't lose its functionality
                $.mage.calendar.prototype._initPicker.call(this, this.options.toObj);
                this.options.fromObj[this._picker()]('option', 'currentQuantity', this.options.currentQuantity);
                this.options.toObj[this._picker()]('option', 'currentQuantity', this.options.currentQuantity);
                this._updateTimePickerInventory();
                if (typeof order !== 'undefined') {
                    order.datePickersActive = this;
                    order.hasGlobalDatesSetup = false;
                }
            }
        },

        /**
         * destroy two instances of datetimepicker
         */
        _destroy: function () {
            if (this.options.from) {
                this.options.fromObj[this._picker()]('destroy');
            }

            if (this.options.to) {
                this.options.toObj[this._picker()]('destroy');
            }
            this._super();
        }
    });

    return {
        pprdatepicker: $.salesigniter.pprdatepicker
    };
}));
