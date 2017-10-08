define([
	'ko',
	'jquery',
	'moment',
	'text!SalesIgniter_Rental/template/report/calendar/day.html',
	'salesigniter/report/calendar/renderer'
], function (ko, $, moment, TemplateHtml) {

	var _now = moment();

	function CalendarFormatDay() { }

	CalendarFormatDay.prototype = new RendererPlugin();
	CalendarFormatDay.prototype.constructor = CalendarFormatDay;
	CalendarFormatDay.prototype._super = RendererPlugin.prototype;

	CalendarFormatDay.prototype.getCode = function (){
		return 'day';
	};

	CalendarFormatDay.prototype.getDateFrom = function (){
		return this.getCalendar().getDate()
				.hour(0)
				.minute(0)
				.second(0);
	};

	CalendarFormatDay.prototype.getDateTo = function (){
		return this.getCalendar().getDate()
				.hour(23)
				.minute(59)
				.second(59);
	};

	CalendarFormatDay.prototype._getHeadings = function () {
		var _startTime = this.getDateFrom();
		var _endTime = this.getDateTo();

		var Headings = [];
		while(_startTime.unix() <= _endTime.unix()){
			Headings.push(parseInt(_startTime.unix()));
			_startTime.add(1, 'hours');
		}

		return Headings;
	};

	CalendarFormatDay.prototype._getContents = function (Item) {
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
			_startTime.add(1, 'hours');
		}

		return Results;
	};

	CalendarFormatDay.prototype._renderSwitcher = function () {
		var Switcher = $('<div></div>')
			.addClass('switcher-day');

		var Month = $('<select></select>')
			.attr('name', 'month')
			.addClass('admin__control-select');

		for(var i = 0; i < 12; i++){
			var _monthMoment = moment.utc().month(i);
			var _numDays = _monthMoment.clone().date(1).add(1, 'months').subtract(1, 'days').format('D');
			Month.append('<option value="' + i + '" data-num_days="' + _numDays + '">' + _monthMoment.format('MMMM') + '</option>');
		}

		var Day = $('<select></select>')
			.attr('name', 'day')
			.addClass('admin__control-select');

		for(var i = 1; i <= 31; i++){
			Day.append('<option value="' + i + '">' + i + '</option>');
		}

		var Year = $('<select></select>')
			.attr('name', 'year')
			.addClass('admin__control-select');

		var _now = moment();
		for(var i = (parseInt(_now.format('YYYY')) - 10); i < (parseInt(_now.format('YYYY')) + 10); i++){
			Year.append('<option value="' + i + '">' + i + '</option>');
		}

		Switcher.append(Month);
		Switcher.append(Day);
		Switcher.append(Year);

		Month.val(this.getCalendar().getDate().month());
		Day.val(this.getCalendar().getDate().date());
		Year.val(this.getCalendar().getDate().year());

		$('.calendar-tools-left').empty();
		$('.calendar-tools-center').empty().append(Switcher);
	};

	CalendarFormatDay.prototype.render = function () {
		var self = this;

		$('.calendar-tools-center')
			.off('change')
			.on('change', 'select', function () {
				var _month = parseInt($('.switcher-day select[name=month]').val());
				var _date = parseInt($('.switcher-day select[name=day]').val());
				var _year = parseInt($('.switcher-day select[name=year]').val());

				var Date = moment.utc();
				Date.year(_year).month(_month).date(_date).hour(0).minute(0).second(0);

				self.getCalendar().setDate(Date);
			});

		this._renderSwitcher();
		this._render(this._templateHtml || TemplateHtml);
	};

	CalendarFormatDay.prototype.update = function () {
		this._update(this._templateHtml || TemplateHtml);
	};

	return new CalendarFormatDay();
});