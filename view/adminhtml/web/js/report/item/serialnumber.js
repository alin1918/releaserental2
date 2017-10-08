define([
	'ko',
	'jquery'
], function (ko, $) {

	function SerialNumber(Data) {
		this._data = Data;
		this._report;

		this.getReservationOrderId = function (Timestamp){
			var Statuses = this.getStatus();
			if (typeof Statuses[Timestamp] !== 'undefined'){
				if (typeof Statuses[Timestamp].reservationorder_id !== 'undefined'){
					return Statuses[Timestamp].reservationorder_id;
				}
			}
			return null;
		};

		this.getCost = function () {
			return this.getData('cost');
		};

		this.getDateAquired = function () {
			return this.getData('date_acquired');
		};

		this.getNotes = function () {
			return this.getData('notes');
		};

		this.getNumber = function () {
			return this.getData('serial_number');
		};

		this.getStatus = function () {
			return this.getData('status');
		};

		this.getData = function (key) {
			return (typeof this._data[key] !== 'undefined' ? this._data[key] : null);
		};

		this.setReport = function (Report){
			this._report = Report;
			return this;
		};

		this.getReport = function (){
			return this._report;
		};
	}

	function SerialNumberFactory() {
		this.create = function (Data) {
			return new SerialNumber(Data);
		};
	}

	return new SerialNumberFactory();
});

