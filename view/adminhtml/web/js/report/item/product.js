define([
	'ko',
	'jquery'
], function (ko, $) {

	function Product(Data) {
		this._data = Data;
		this._report;

		this.getAvailability = function () {
			return this.getData('availability');
		};

		this.getId = function () {
			return this.getData('id');
		};

		this.getName = function () {
			return this.getData('name');
		};

		this.getRentalQuantity = function () {
			return this.getData('sirent_quantity');
		};

		this.getSku = function () {
			return this.getData('sku');
		};

		this.getSerialNumbers = function () {
			return this.getData('serial_numbers');
		};

		this.getData = function (key) {
			return (typeof this._data[key] !== 'undefined' ? this._data[key] : null);
		};

		this.setReport = function (Report) {
			this._report = Report;
			return this;
		};

		this.getReport = function () {
			return this._report;
		};

		this.showDateReport = function (data, e) {
			var _product, _date;

			var self = this;

			_product = $(e.currentTarget).parent().data('product_id');
			_date = moment.utc(parseInt(data.timestamp) * 1000);

			this.getWidget().showLoader();

			$.getJSON(
				this.getCalendar().getDateDataUrl(),
				{
					product: _product,
					dateFrom: _date.clone().hour(0).minute(0).second(0).format('YYYY-MM-DD HH:mm:ss'),
					dateTo: _date.clone().hour(23).minute(59).second(59).format('YYYY-MM-DD HH:mm:ss')
				},
				function (data) {
					$('<div style="margin-top: 10px;">' + data.data_table + '</div>').modal({
						title: data.title,
						autoOpen: true
					});

					self.getWidget().hideLoader();
				}
			);

			return true;
		};
	}

	function ProductFactory() {
		this.create = function (Data) {
			return new Product(Data);
		};
	}

	return new ProductFactory();

});

