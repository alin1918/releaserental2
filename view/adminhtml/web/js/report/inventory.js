define([
	'ko',
	'jquery',
	'moment',
	'salesigniter/report/calendar/main',
	'salesigniter/report/pager/main',
	'salesigniter/report/filter/main',
	'text!SalesIgniter_Rental/template/report/inventory/product.html',
	'text!SalesIgniter_Rental/template/report/inventory/calendar/day.html',
	'text!SalesIgniter_Rental/template/report/inventory/calendar/week.html',
	'text!SalesIgniter_Rental/template/report/inventory/calendar/month.html',
	'salesigniter/report/item/product'
], function (ko, $, moment, Calendar, Pager, Filter, ProductTemplate, DayTemplate, WeekTemplate, MonthTemplate, ProductFactory) {

	function RentalReport() {
		this._loaded = false;

		this._productListRendered = false;

		this._reportData = [];
		this._widget;
		this._calendar;
		this._pager;
		this._filter;

		this.options = {
			Report: {
				dataUrl: null
			},
			Calendar: {
				rendererCode: 'month'
			},
			Pager: {
				pageVarName: 'p',
				page: 1,
				limitVarName: 'limit',
				limit: 10
			}
		};

		this.init = function (Report, options) {
			options = options || {};
			this.options = $.extend(true, {}, this.options, options);

			this.setWidget(Report);
			this.setCalendar(Calendar);
			this.setFilter(Filter);
			this.setPager(Pager);

			//this.getCalendar().init(this.getOption('Calendar'));
			this.getFilter().init(this.getOption('Filter'));
			this.getPager().init(this.getOption('Pager'));

			this._initGlobalEvents();
			this.loadData();

			var self = this;
			$.when(
					this.getWidget().getPromise(this, this._isProductListRendered),
					this.getWidget().getPromise(this.getCalendar(), this.getCalendar().isReady),
					this.getWidget().getPromise(this.getPager(), this.getPager().isReady),
					this.getWidget().getPromise(this.getFilter(), this.getFilter().isReady)
			).then(function () {
				self._loaded = true;
			});
		};

		this.getDataRequestParams = function () {
			var DataUrlParams = {
				code: this.getOption('code')
			};

			this.getCalendar().appendLoadDataParams(DataUrlParams);
			this.getPager().appendLoadDataParams(DataUrlParams);
			this.getFilter().appendLoadDataParams(DataUrlParams);

			return DataUrlParams;
		};

		this.loadData = function () {
			var self = this;

			$.getJSON(
					this.getDataRequestUrl(),
					this.getDataRequestParams(),
					function (data) {
						self.setReportData(data.reportData);

						self.getPager().renderHtml(data.pagerBlock);
						self.getFilter().renderHtml(data.filterBlock);

						self._renderProductList();
						self._renderCalendar();
					});
		};

		this.updateData = function (callback) {
			this.getWidget().showLoader();

			var self = this;
			$.getJSON(
					this.getDataRequestUrl(),
					this.getDataRequestParams(),
					function (data) {
						self.setReportData(data.reportData);

						//self.getFilter().renderHtml(data.filterBlock);
						self.getPager().renderHtml(data.pagerBlock);
						self.getCalendar().getRenderer().update();
						self._renderProductList();

						if (callback){
							callback.apply(this, [data.reportData]);
						}

						self.getWidget().hideLoader();
					}
			);
		};

		this._initGlobalEvents = function () {
			var self = this;

			$('.report-main-content')
					.on('mouseover', '.row.product', function () {
						if ($(this).hasClass('__active')){
							return;
						}

						$(this).addClass('__hover');
						$('.report-calendar .row.body div[data-row_idx="' + $(this).data('row_idx') + '"]').addClass('__hover');
					})
					.on('mouseout', '.row.product', function () {
						if ($(this).hasClass('__active')){
							return;
						}

						$(this).removeClass('__hover');
						$('.report-calendar .row.body div[data-row_idx="' + $(this).data('row_idx') + '"]').removeClass('__hover');
					});
		};

		this._renderProductList = function () {
			this._productListRendered = false;

			if (typeof this._reportData.products == 'object'){
				var ProductListContainer = $('.report-products');

				ko.cleanNode(ProductListContainer[0]);

				ProductListContainer.empty();
				ProductListContainer.append(ProductTemplate);

				ko.applyBindings(this._reportData, ProductListContainer.find('.body')[0]);
			}

			this._productListRendered = true;
		};

		this._renderCalendar = function () {
			this
					.getCalendar()
					.setRendererTemplateHtml('day', DayTemplate)
					.setRendererTemplateHtml('week', WeekTemplate)
					.setRendererTemplateHtml('month', MonthTemplate)
					.init(this.getReportData().calendar);
		};

		this.getDateContent = function (Result, o) {
			this._getDateContentProduct(Result, o.item, o.date);
		};

		this._getDateContentProduct = function (Result, Product, Date) {
			if (Product.getAvailability()){
				var Availabilities = Product.getAvailability();
				if (typeof Availabilities[Date.unix()] !== 'undefined'){
					Result.html = parseInt(Availabilities[Date.unix()].result);
				}
			}
		};

		this.isReady = function () {
			return this._loaded == true;
		};

		this._isProductListRendered = function () {
			return this._productListRendered == true;
		};

		this.getOption = function (Option) {
			return this.options[Option];
		};

		this.setOption = function (Option, Value) {
			this.options[Option] = Value;
			return this;
		};

		this.getReportPanel = function () {
			return $('[data-role="report-panel"]');
		};

		this.getDataRequestUrl = function () {
			return this.getOption('Report').dataUrl;
		};

		this.getProductDashboardUrl = function () {
			return this.getOption('productDashboardUrl');
		};

		this.setReportData = function (ReportData) {
			for(var i = 0; i < ReportData.products.length; i++){
				ReportData.products[i] = ProductFactory.create(ReportData.products[i]);
				ReportData.products[i].setReport(this);
			}
			this._reportData = ReportData;
			return this;
		};

		this.showDateReport = function (e, data, Item) {
			var _date, self;

			self = this;

			this.getWidget().showLoader();

			_date = moment.utc(parseInt(data.timestamp) * 1000);

			var RequestParams = {
				dateFrom: _date.clone().hour(0).minute(0).second(0).format('YYYY-MM-DD HH:mm:ss'),
				dateTo: _date.clone().hour(23).minute(59).second(59).format('YYYY-MM-DD HH:mm:ss')
			};

			RequestParams.product = Item.getId();

			$.getJSON(
					self.getCalendar().getDateDataUrl(),
					RequestParams,
					function (data) {
						$('<div style="margin-top: 10px;">' + data.html + '</div>').modal({
							title: data.title,
							autoOpen: true
						});

						self.getWidget().hideLoader();
					}
			);

			return true;
		};

		this.getReportData = function () {
			return this._reportData;
		};

		this.setWidget = function (Widget) {
			this._widget = Widget;
			return this;
		};

		this.getWidget = function () {
			return this._widget;
		};

		this.setCalendar = function (Calendar) {
			this._calendar = Calendar;
			this._calendar.setReport(this);
			return this;
		};

		this.getCalendar = function () {
			return this._calendar;
		};

		this.setPager = function (Pager) {
			this._pager = Pager;
			this._pager.setWidget(this.getWidget());
			this._pager.setReport(this);
			return this;
		};

		this.getPager = function () {
			return this._pager;
		};

		this.setFilter = function (Filter) {
			this._filter = Filter;
			this._filter.setWidget(this.getWidget());
			this._filter.setReport(this);
			return this;
		};

		this.getFilter = function () {
			return this._filter;
		};
	}

	return new RentalReport();
});
