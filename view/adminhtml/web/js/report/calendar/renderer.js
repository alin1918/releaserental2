function RendererPlugin() {
	this._loaded = false;
	this._rendered = false;
	this._calendar;
	this._templateHtml = false;
	this.options = {};
};

define([
	'ko',
	'jquery',
	'moment'
], function (ko, $, moment) {
	RendererPlugin.prototype.getCode = function () {
		throw Error('getCode is required to be unique per plugin!');
	};

	RendererPlugin.prototype.getDateFrom = function () {
		throw Error('getDateFrom is required to be unique per plugin!');
	};

	RendererPlugin.prototype.getDateTo = function () {
		throw Error('getDateTo is required to be unique per plugin!');
	};

	RendererPlugin.prototype.render = function () {
		throw Error('Render is required to be unique per plugin!');
	};

	RendererPlugin.prototype.update = function () {
		throw Error('Update is required to be unique per plugin!');
	};

	RendererPlugin.prototype._getHeadings = function () {
		throw Error('_getHeadings must be implemented in plugin class!');
	};

	RendererPlugin.prototype._getContents = function (product) {
		throw Error('_getContents must be implemented in plugin class!');
	};

	RendererPlugin.prototype.init = function (options) {
		this.setOptions(options)
			.setLoaded(false)
			.setRendered(false);

		this.render();
	};

	RendererPlugin.prototype.getReportData = function () {
		var self = this;

		var ReportData = this.getCalendar().getReportData();

		ReportData.showDateReport = function (e, data, Item) {
			self.getCalendar().showDateReport(e, data, Item);
			return true;
		};

		ReportData.getCalendarHeadings = function () {
			return self._getHeadings();
		};

		ReportData.getCalendarContent = function (Item) {
			return self._getContents(Item);
		};

		ReportData.getCalendarWidth = function () {
			return (self._getHeadings().length * self.getOption('headingWidth')) + 'px';
		};

		ReportData.displayDate = function (Format, Timestamp) {
			return moment.utc(Timestamp * 1000).format(Format);
		};

		return ReportData;
	};

	RendererPlugin.prototype.appendLoadDataParams = function (UrlParams) {
		UrlParams.dateFrom = this.getDateFrom().format('YYYY-MM-DD HH:mm:ss');
		UrlParams.dateTo = this.getDateTo().format('YYYY-MM-DD HH:mm:ss');
	};

	RendererPlugin.prototype._render = function (TemplateHtml) {
		var CalendarContainer = $('.report-calendar');

		ko.cleanNode(CalendarContainer[0]);
		CalendarContainer.html(TemplateHtml);

		this.setOption('headingWidth', parseFloat(CalendarContainer.find('.headings .date-column').first().outerWidth()));

		ko.applyBindings(this.getReportData(), CalendarContainer[0]);

		this.setRendered(true);
		this.setLoaded(true);
	};

	RendererPlugin.prototype._update = function (TemplateHtml) {
		var CalendarContainer = $('.report-calendar');

		ko.cleanNode(CalendarContainer[0]);
		CalendarContainer.empty();
		CalendarContainer.append(TemplateHtml);

		ko.applyBindings(this.getReportData(), CalendarContainer[0]);
	};

	RendererPlugin.prototype.setTemplateHtml = function (TemplateHtml){
		this._templateHtml = TemplateHtml;
		return this;
	}

	RendererPlugin.prototype.isReady = function () {
		return this._loaded == true;
	};

	RendererPlugin.prototype.setRendered = function (Value) {
		this._rendered = Value;
		return this;
	};

	RendererPlugin.prototype.setLoaded = function (Value) {
		this._loaded = Value;
		return this;
	};

	RendererPlugin.prototype.isRendered = function () {
		return this._rendered == true;
	};

	RendererPlugin.prototype.getCalendar = function () {
		return this._calendar;
	};

	RendererPlugin.prototype.setCalendar = function (Calendar) {
		this._calendar = Calendar;
		return this;
	};

	RendererPlugin.prototype.setOptions = function (options) {
		options = options || {};
		this.options = $.extend(this.options, options);

		return this;
	};

	RendererPlugin.prototype.getOption = function (Option) {
		return this.options[Option];
	};

	RendererPlugin.prototype.setOption = function (Option, Value) {
		this.options[Option] = Value;
		return this;
	};

	RendererPlugin.prototype.dateIsBetween = function (MainDate, FromDate, ToDate) {
		var isBetween = false;
		if (MainDate.isSame(FromDate) || MainDate.isSame(ToDate)){
			isBetween = true;
		}
		else if (MainDate.isAfter(FromDate) && MainDate.isBefore(ToDate)){
			isBetween = true;
		}
		return isBetween;
	};
});