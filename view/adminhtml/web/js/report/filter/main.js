define([
	'ko',
	'jquery'
], function (ko, $) {

	function ReportFilter() {
		this.loaded = false;

		this.options = {};
		this._widget;
		this._report;

		this.init = function (options) {
			options = options || {};
			this.options = $.extend({}, this.options, options);

			var self = this;

			this.getFilterBlock()
				.on('click', '.filter-toggle', function () {
					self.getFiltersContainer().toggleClass('hidden');
					if (self.getFiltersContainer().hasClass('hidden')){
						$(this).html('Show Filters');
					}
					else {
						$(this).html('Hide Filters');
					}
				})
				.on('click', '.btn-apply_filters', function () {
					self.getReport().updateData();
				})
				.on('click', '.btn-clear_filters', function () {
					$('[name^=filter]').each(function () {
						if (this.tagName.toLowerCase() == 'input'){
							if (this.type.toLowerCase() == 'checkbox' || this.type.toLowerCase() == 'radio'){
								this.checked = false;
							}
							else {
								$(this).val(null);
							}
						}
						else if (this.tagName.toLowerCase() == 'select'){
							$(this).val(null);
						}
					});
					self.getReport().updateData();
				});

			this.getFiltersContainer().find('.date-range input').calendar();

			this.loaded = true;
		};

		this.appendLoadDataParams = function (DataUrlParams) {
			$('[name^=filter]').each(function () {
				var El = $(this);
				if (this.tagName.toLowerCase() == 'input'){
					if (this.type.toLowerCase() == 'checkbox' || this.type.toLowerCase() == 'radio'){
						if (this.checked){
							DataUrlParams[El.attr('name')] = El.val();
						}
					}
					else if (El.val() != ''){
						DataUrlParams[El.attr('name')] = El.val();
					}
				}
				else if (this.tagName.toLowerCase() == 'select'){
					DataUrlParams[El.attr('name')] = El.val();
				}
			});
		};

		this.isReady = function () {
			return this.loaded === true;
		};

		this.renderHtml = function (FilterHtml) {
			this.getFilterBlock().html(FilterHtml);
		};

		this.getFilterBlock = function () {
			return $('.filter-block');
		};

		this.getFiltersContainer = function () {
			return this.getFilterBlock().find('.filter-bar');
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

	return new ReportFilter();
});
