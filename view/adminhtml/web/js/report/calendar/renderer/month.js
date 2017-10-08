define([
	'ko',
	'jquery',
	'moment',
	'text!SalesIgniter_Rental/template/report/calendar/month.html',
	'salesigniter/report/calendar/renderer'
], function (ko, $, moment, TemplateHtml) {

	var _now = moment();

	function CalendarFormatMonth() { }

	CalendarFormatMonth.prototype = new RendererPlugin();
	CalendarFormatMonth.prototype.constructor = CalendarFormatMonth;
	CalendarFormatMonth.prototype._super = RendererPlugin.prototype;

	CalendarFormatMonth.prototype.getCode = function (){
		return 'month';
	};

	CalendarFormatMonth.prototype.getDateFrom = function (){
		return this.getCalendar().getDate()
				.date(1)
				.hour(0)
				.minute(0)
				.second(0);
	};

	CalendarFormatMonth.prototype.getDateTo = function (){
		return this.getCalendar().getDate()
				.date(1)
				.add(1, 'months')
				.subtract(1, 'days')
				.hour(23)
				.minute(59)
				.second(59);
	};

	CalendarFormatMonth.prototype._getHeadings = function () {
		var _startTime = this.getDateFrom();
		var _endTime = this.getDateTo();

		var Headings = [];
		while(_startTime.unix() <= _endTime.unix()){
			Headings.push(parseInt(_startTime.unix()));
			_startTime.add(1, 'days');
		}

		return Headings;
	};

	CalendarFormatMonth.prototype._getContents = function (Item) {
		var _startTime = this.getDateFrom();
		var _endTime = this.getDateTo();

		var Results = [];
		while(_startTime.unix() <= _endTime.unix()){
			var Result = {
				html: '&nbsp;',
				timestamp: _startTime.unix()
			};

			this.getCalendar().getReport().getDateContent(Result, {
				item: Item,
				date: _startTime,
				renderer: this.getCode()
			});

			Results.push(Result);
			_startTime.add(1, 'days');
		}

		return Results;
	};

	CalendarFormatMonth.prototype._renderSwitcher = function () {
		var Switcher = $('<div></div>')
			.addClass('switcher-month');

		var Month = $('<select></select>')
			.attr('name', 'month')
			.addClass('admin__control-select');

		for(var i = 0; i < 12; i++){
			Month.append('<option value="' + i + '">' + moment.utc().month(i).format('MMMM') + '</option>');
		}

		var Year = $('<select></select>')
			.attr('name', 'year')
			.addClass('admin__control-select');

		for(var i = (parseInt(_now.format('YYYY')) - 10); i < (parseInt(_now.format('YYYY')) + 10); i++){
			Year.append('<option value="' + i + '">' + i + '</option>');
		}

		Switcher.append(Month);
		Switcher.append(Year);

		$('.calendar-tools-left').empty();
		$('.calendar-tools-center').empty().append(Switcher);

		Month.val(this.getCalendar().getDate().month());
		Year.val(this.getCalendar().getDate().year());
	};

	CalendarFormatMonth.prototype.render = function () {
		var self = this;

		$('.calendar-tools-center')
			.off('change')
			.on('change', 'select', function () {
				var _month = parseInt($('.switcher-month select[name=month]').val());
				var _year = parseInt($('.switcher-month select[name=year]').val());

				var Date = moment.utc();
				Date.year(_year).month(_month).date(1).hour(0).minute(0).second(0);

				self.getCalendar().setDate(Date);
			});

		this._renderSwitcher();
		this._render(this._templateHtml || TemplateHtml);
	};

	CalendarFormatMonth.prototype.update = function () {
		this._update(this._templateHtml || TemplateHtml);
	};

	return new CalendarFormatMonth();
});