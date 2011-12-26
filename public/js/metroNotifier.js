(function (w, d, $) {

	var MetroNotifier = w.MetroNotifier = $.extend({

		delay: 0.5 * 1000,

		info: metroNotifierInfo,
		notice: metroNotifierNotice,
		warn: metroNotifierWarn,
		error: metroNotifierError,
		fatal: metroNotifierFatal

	}, w.MetroNotifier || {});

	function metroNotifierInfo () {
		var args = $.makeArray(arguments);
		metroNotifierSendMessage.apply(this, ['info'].concat(args));
	}

	function metroNotifierNotice () {
		var args = $.makeArray(arguments);
		metroNotifierSendMessage.apply(this, ['notice'].concat(args));
	}

	function metroNotifierWarn () {
		var args = $.makeArray(arguments);
		metroNotifierSendMessage.apply(this, ['warn'].concat(args));
	}

	function metroNotifierError () {
		var args = $.makeArray(arguments);
		metroNotifierSendMessage.apply(this, ['error'].concat(args));
	}

	function metroNotifierFatal () {
		var args = $.makeArray(arguments);
		metroNotifierSendMessage.apply(this, ['fatal'].concat(args));
	}

	function metroNotifierSendMessage (type, message, show, hide) {
		var notifier = this;

		type = type.replace(/\W+/g, '');
		message = (message || '').toString().replace(/\n+/g, ' ');

		var dl = d.createElement('dl');
		var dt = d.createElement('dt');
		var dd = d.createElement('dd');

		dl.className = 'metro_notifier metro_notifier_' + type;
		dl.appendChild(dt.appendChild(d.createTextNode(type)));
		dl.appendChild(dd.appendChild(d.createTextNode(message)));

		if (typeof show === 'function') {
			show.apply(this, [dl]);
		} else {
			d.body.appendChild(dl);
		}

		var timer_id = setTimeout(function () {
			if (typeof hide === 'function') {
				hide.apply(notifier, [dl]);
			} else {
				d.body.removeChild(dl);
			}

			clearTimeout(timer_id);
		}, notifier.delay);
	}

})(this, document, jQuery);
