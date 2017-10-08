define([
	'ko',
	'jquery',
	'uiComponent'
], function (ko, $, uiComponent) {

	$.widget('SalesIgniter.RentalReport', {
		_activeLoaders: 0,
		options: {
			code: null
		},
		_create: function (options) {
			options = options || {};

			this.options = $.extend({}, this.options, options);

			var self = this;
			if (this.options.code){
				self.showLoader();

				require([
					'salesigniter/report/' + this.options.code
				], function (Report) {
					Report.init(self, self.options);
					$.when(
						self.getPromise(Report, Report.isReady)
					).then(function () {
						self.hideLoader();
					});
				});
			}
		},
		getPromise: function (scope, callback) {
			var self = this;
			var dfd = new $.Deferred;

			var PromiseInterval = setInterval(function () {
				if (callback.apply(scope) == true){
					dfd.resolve();
					clearInterval(PromiseInterval);
				}
			}, 1000);

			return dfd.promise();
		},
		showLoader: function (){
			if (this._activeLoaders == 0){
				$(document.body).trigger('processStart');
			}
			this._activeLoaders++;
			return this;
		},
		hideLoader: function (){
			this._activeLoaders--;
			if (this._activeLoaders == 0){
				$(document.body).trigger('processStop');
			}
			return this;
		}
	});

	return $.SalesIgniter.RentalReport;
});
