define([
	'ko',
	'jquery',
	'moment',
	'text!SalesIgniter_Rental/template/report/calendar/week.html',
	'salesigniter/report/calendar/renderer'
], function (ko, $, moment, TemplateHtml) {

	var _now = moment.utc();

	function CalendarFormatWeek() {
		this.dateFrom = null;
		this.dateTo = null;
	}

	CalendarFormatWeek.prototype = new RendererPlugin();
	CalendarFormatWeek.prototype.constructor = CalendarFormatWeek;
	CalendarFormatWeek.prototype._super = RendererPlugin.prototype;

	CalendarFormatWeek.prototype.getCode = function () {
		return 'week';
	};

	CalendarFormatWeek.prototype.getDateFrom = function () {
		if (!this.dateFrom){
			this.dateFrom = this.getCalendar().getDate()
				.day(0)
				.hour(0)
				.minute(0)
				.second(0);
		}

		return this.dateFrom.clone();
	};

	CalendarFormatWeek.prototype.getDateTo = function () {
		if (!this.dateTo){
			this.dateTo = this.getCalendar().getDate()
				.day(6)
				.hour(23)
				.minute(59)
				.second(59);
		}

		return this.dateTo.clone();
	};

	CalendarFormatWeek.prototype._getHeadings = function () {
		var _startTime = this.getDateFrom();
		var _endTime = this.getDateTo();

		var Headings = [];
		while(_startTime.unix() <= _endTime.unix()){
			Headings.push(parseInt(_startTime.unix()));
			_startTime.add(1, 'days');
		}

		return Headings;
	};

	CalendarFormatWeek.prototype._getContents = function (Item) {
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

	CalendarFormatWeek.prototype._renderSwitcher = function () {
		var Switcher = $('<div></div>')
			.addClass('switcher-week');

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

		Switcher.append(this.getWeekSelectBox());

		Month.val(this.getCalendar().getDate().month());
		Year.val(this.getCalendar().getDate().year());

		$('.calendar-tools-left').empty().append(Month).append(Year);
		$('.calendar-tools-center').empty().append(Switcher);
	};

	CalendarFormatWeek.prototype.getWeekSelectBox = function (){
		var WeekSelect = $('<select></select>')
				.attr('name', 'week')
				.addClass('admin__control-select');

		var _dateFrom = this.getCalendar().getDate().clone().date(1);
		var _dateTo = _dateFrom.clone().add(1, 'months').subtract(1, 'days');
		while(_dateFrom.day() > 0){
			_dateFrom.subtract(1, 'days');
		}
		while(_dateTo.day() < 6){
			_dateTo.add(1, 'days');
		}

		var _checkDate = _dateFrom.clone();
		while(_checkDate.unix() <= _dateTo.unix()){
			var WeekStart = moment.utc(_checkDate.valueOf()).hour(0).minute(0).second(0);
			_checkDate.add(6, 'days');
			var WeekEnd = moment.utc(_checkDate.valueOf()).hour(23).minute(59).second(59);

			var Selected = '';
			if (this.dateIsBetween(this.getCalendar().getDate(), WeekStart, WeekEnd)){
				Selected = ' selected="selected"';
			}

			WeekSelect.append('<option ' +
					'value="use_data" ' +
					'data-date_from="' + WeekStart.format('YYYY-MM-DD HH:mm:ss') + '" ' +
					'data-date_to="' + WeekEnd.format('YYYY-MM-DD HH:mm:ss') + '"' +
					Selected +
					'>' + WeekStart.format('MMM Do') + ' - ' + WeekEnd.format('MMM Do') + '</option>');

			_checkDate.add(1, 'days');
		}

		return WeekSelect;
	};

	CalendarFormatWeek.prototype.render = function () {
		var self = this;

		$('.calendar-tools-left')
			.off('change')
			.on('change', 'select', function () {
				var _month = parseInt($('select[name=month]').val());
				var _year = parseInt($('select[name=year]').val());

				var Date = moment.utc();
				Date.year(_year).month(_month).date(1);

				self.getCalendar().setDate(Date, true);

				var WeekSelectBox = self.getWeekSelectBox();
				$('.calendar-tools-center')
					.find('select[name=week]')
					.replaceWith(WeekSelectBox);

				WeekSelectBox.change();
			});

		$('.calendar-tools-center')
			.off('change')
			.on('change', 'select', function () {
				/**
				 * Need to set from and to date here, so maybe direct call to updateData?
				 */
				self.dateFrom = moment.utc($(this).find('option:selected').data('date_from'), 'YYYY-MM-DD HH:mm:ss');
				self.dateTo = moment.utc($(this).find('option:selected').data('date_to'), 'YYYY-MM-DD HH:mm:ss');

				self.getCalendar().getReport().updateData(function (ReportData) {
					self.update();
				});
			});

		this._renderSwitcher();
		this._render(this._templateHtml || TemplateHtml);
	};

	CalendarFormatWeek.prototype.update = function () {
		this._update(this._templateHtml || TemplateHtml);
	};

	return new CalendarFormatWeek();
});