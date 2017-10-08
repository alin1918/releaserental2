define([
    'ko',
    'jquery',
    'moment'
], function (ko, $, moment) {

    var _now = moment.utc();

    function ReportCalendar() {
        this._loaded = false;
        this._rendered = false;

        this._report;
        this._renderer;
        this._rendererCode;
        this._rendererHtml = {
            month: null,
            day: null,
            week: null,
            list: null
        };
        this.options = {
            rendererCode: null,
            dateDataUrl: null,
            dateDataUrlProduct: null,
            date: _now.clone().date(1).hour(0).minute(0).second(0).format('YYYY-MM-DD HH:mm:ss')
        };

        this.init = function (options) {
            options = options || {};
            this.options = $.extend({}, this.options, options);

            this._loaded = false;

            if (moment.isMoment(this.options.date) === false) {
                this.options.date = moment.utc(this.options.date, 'YYYY-MM-DD HH:mm:ss');
            }

            this._render();

            var self = this;

            $('.calendar-tools-container')
                .on('click', '.btn-calendar_tool', function () {
                    $('.btn-calendar_tool').removeClass('__active');
                    $(this).addClass('__active');

                    self.changeRenderer($(this).data('renderer_name'));
                });

            $('.report-main-content')
                .on('mouseover', '.date-column', function () {
                    if ($(this).parent().parent().hasClass('headings')) {
                        return;
                    }

                    $(this).addClass('__hover');
                })
                .on('mouseout', '.date-column', function () {
                    if ($(this).parent().parent().hasClass('headings')) {
                        return;
                    }

                    $(this).removeClass('__hover');
                });

            $.when(
                this.getReport().getWidget().getPromise(this, this._isRendered)
            ).then(function () {
                self._loaded = true;
            });
        };

        this.getDateDataUrl = function () {
            return this.getReportData().calendar.dateDataUrl;
        };
        this.getDateDataUrlProduct = function () {
            return this.getReportData().calendar.dateDataUrlProduct;
        };

        this.loadRenderer = function (RendererCode, Callback) {
            var self = this;
            require([
                'salesigniter/report/calendar/renderer/' + RendererCode
            ], function (CalendarRendererClass) {
                CalendarRendererClass.setCalendar(self);

                self.setRenderer(CalendarRendererClass);

                if (typeof self._rendererHtml[RendererCode] !== 'undefined') {
                    CalendarRendererClass.setTemplateHtml(self._rendererHtml[RendererCode]);
                }
                Callback.call(this);
            });
        };

        this.appendLoadDataParams = function (UrlParams) {
            if (this.getRenderer()) {
                UrlParams.rendererCode = this.getRenderer().getCode();
                this.getRenderer().appendLoadDataParams(UrlParams);
            }
            else {
                UrlParams.rendererCode = 'month';
            }
        };

        this._render = function () {
            this._rendered = false;

            var self = this;
            this.loadRenderer(this.getOption('rendererCode'), function () {
                self.getRenderer().init();

                $.when(
                    self.getReport().getWidget().getPromise(self.getRenderer(), self.getRenderer().isReady)
                ).then(function () {
                    self._rendered = true;
                });
            });
        };

        this.changeRenderer = function (RendererCode) {
            var self = this;
            this.loadRenderer(RendererCode, function () {
                var Report = self.getReport();
                Report.updateData(function (ReportData) {
                    self.getRenderer().init();
                });
            });
        };

        this.setRendererTemplateHtml = function (RendererCode, TemplateHtml) {
            this._rendererHtml[RendererCode] = TemplateHtml;
            return this;
        };

        this.getDate = function () {
            return this.getOption('date');
        };

        this.setDate = function (Date, SkipUpdate) {
            SkipUpdate = SkipUpdate || false;

            this.setOption('date', Date);

            if (SkipUpdate === false) {
                var self = this;
                var Report = this.getReport();
                Report.updateData(function (ReportData) {
                    self.getRenderer().update();
                });
            }
        };

        this.isReady = function () {
            return this._loaded == true;
        };

        this._isRendered = function () {
            return this._rendered == true;
        };

        this.setReport = function (Report) {
            this._report = Report;
            return this;
        };

        this.getReport = function () {
            return this._report;
        };

        this.setReportData = function (ReportData) {
            this.getReport().setReportData(ReportData);
            return this;
        };

        this.getReportData = function () {
            return this.getReport().getReportData();
        };

        this.setRenderer = function (Renderer) {
            this._renderer = Renderer;
            return this;
        };

        this.getRenderer = function () {
            return this._renderer;
        };

        this.setRendererCode = function (RendererCode) {
            this._rendererCode = RendererCode;
            return this;
        };

        this.getRendererCode = function () {
            return this._rendererCode;
        };

        this.getOption = function (Option) {
            var _option;

            if (Option == 'date') {
                _option = this.options[Option].clone();
            }
            else {
                _option = this.options[Option];
            }

            return _option;
        };

        this.setOption = function (Option, Value) {
            this.options[Option] = Value;
            return this;
        };

        this.showDateReport = function (e, data, Item) {
            this.getReport().showDateReport(e, data, Item);
            return true;
        };
    }

    return new ReportCalendar();
});
