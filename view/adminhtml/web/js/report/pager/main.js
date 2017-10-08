define([
	'ko',
	'jquery'
], function (ko, $) {

	function ReportPager() {
		this.loaded = false;

		this.options = {
			pageVarName: 'p',
			page: 1,
			limitVarName: 'limit',
			limit: 10
		};
		this._widget;
		this._report;

		this.init = function (options) {
			options = options || {};
			this.options = $.extend({}, this.options, options);

			var self = this;

			this.getPagerBlock()
				.on('click', '.action-previous', function () {
					self.getWidget().showLoader();

					self.setOption('page', $(this).data('set_page'));

					self.getReport().updateData(function (){
						self.getWidget().hideLoader();
					});
				})
				.on('click', '.action-next', function () {
					self.getWidget().showLoader();

					self.setOption('page', $(this).data('set_page'));

					self.getReport().updateData(function (){
						self.getWidget().hideLoader();
					});
				})
				.on('keypress', '#page-current', function (e) {
					if (e.which == 13){
						self.getWidget().showLoader();

						self.setOption('page', $(this).val());

						self.getReport().updateData(function (){
							self.getWidget().hideLoader();
						});
					}
				})
				.on('change', '#page-limit', function () {
					self.getWidget().showLoader();

					self.setOption('limit', $(this).val());

					self.getReport().updateData(function (){
						self.getWidget().hideLoader();
					});
				});

			this.loaded = true;
		};

		this.isReady = function (){
			return this.loaded === true;
		};

		this.renderHtml = function (PagerHtml) {
			this.getPagerBlock().html(PagerHtml);
		};

		this.appendLoadDataParams = function (DataUrlParams) {
			DataUrlParams[this.getOption('pageVarName')] = this.getOption('page');
			DataUrlParams[this.getOption('limitVarName')] = this.getOption('limit');
		};

		this.getPagerBlock = function () {
			return $('.pager-block');
		};

		this.setWidget = function (Widget) {
			this._widget = Widget;
			return this;
		};

		this.getWidget = function () {
			return this._widget;
		};

		this.setReport = function (Report) {
			this._report = Report;
			return this;
		}

		this.getReport = function () {
			return this._report;
		}

		this.getOption = function (Option) {
			return this.options[Option];
		};

		this.setOption = function (Option, Value) {
			this.options[Option] = Value;
			return this;
		};
	}

	return new ReportPager();
});
