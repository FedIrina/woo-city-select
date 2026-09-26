(function ($) {
	'use strict';

	var $table = $('#woo-city-select-cities');
	if (!$table.length || typeof wooCitySelectSettings === 'undefined') {
		return;
	}

	var $tbody = $table.find('tbody');
	var debounceTimer = null;
	var debounceMs = 300;
	var requestId = 0;

	$tbody.sortable({
		handle: '.woo-city-select-cities__handle',
		axis: 'y',
		helper: function (e, ui) {
			ui.children().each(function () {
				$(this).width($(this).width());
			});
			return ui;
		}
	});

	$('#woo-city-select-add-city').on('click', function (event) {
		event.preventDefault();
		var html = $('#tmpl-woo-city-select-city-row').html();
		$tbody.append(html);
	});

	$table.on('click', '.woo-city-select-cities__remove', function (event) {
		event.preventDefault();
		var $rows = $tbody.find('.woo-city-select-cities__row');
		if ($rows.length < 2) {
			$(this).closest('tr').find('input').val('');
			hideSuggest($(this).closest('tr'));
			return;
		}
		$(this).closest('tr').remove();
	});

	function hideSuggest($row) {
		$row.find('.woo-city-select-cities__suggest').attr('hidden', true).empty();
	}

	function getToken() {
		var $token = $('#woo_city_select_dadata_token');
		return $token.length ? $.trim($token.val()) : '';
	}

	function getCountry() {
		var $country = $('#woo_city_select_dadata_country');
		return $country.length ? $country.val() : 'RU';
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function renderSuggest($row, items, message) {
		var $box = $row.find('.woo-city-select-cities__suggest');
		if (message) {
			$box.html('<p class="woo-city-select-cities__suggest-msg">' + escapeHtml(message) + '</p>').removeAttr('hidden');
			return;
		}
		if (!items.length) {
			$box.html(
				'<p class="woo-city-select-cities__suggest-msg">' +
					escapeHtml(wooCitySelectSettings.i18n.nothingFound) +
					'</p>'
			).removeAttr('hidden');
			return;
		}
		var html = items
			.map(function (item) {
				return (
					'<button type="button" class="woo-city-select-cities__suggest-item" data-city="' +
					escapeHtml(item.city) +
					'" data-region="' +
					escapeHtml(item.region) +
					'">' +
					escapeHtml(item.label || item.city + ', ' + item.region) +
					'</button>'
				);
			})
			.join('');
		$box.html(html).removeAttr('hidden');
	}

	function searchCity($row, query) {
		var currentRequest = ++requestId;
		var body = {
			action: 'woo_city_select_search',
			nonce: wooCitySelectSettings.nonce,
			query: query,
			token: getToken(),
			country: getCountry()
		};

		$.post(wooCitySelectSettings.ajaxUrl, body)
			.done(function (payload) {
				if (currentRequest !== requestId) {
					return;
				}
				if (!payload || !payload.success) {
					var code = payload && payload.data && payload.data.code;
					var message =
						payload && payload.data && payload.data.message
							? payload.data.message
							: wooCitySelectSettings.i18n.unavailable;
					if (code === 'not_configured') {
						message = wooCitySelectSettings.i18n.notConfigured;
					}
					renderSuggest($row, [], message);
					return;
				}
				renderSuggest($row, payload.data.items || []);
			})
			.fail(function () {
				if (currentRequest !== requestId) {
					return;
				}
				renderSuggest($row, [], wooCitySelectSettings.i18n.unavailable);
			});
	}

	$table.on('input', '.woo-city-select-cities__city', function () {
		var $input = $(this);
		var $row = $input.closest('tr');
		var value = $.trim($input.val());

		$row.find('.woo-city-select-cities__region').val('');
		clearTimeout(debounceTimer);

		if (value.length < 3) {
			hideSuggest($row);
			return;
		}

		debounceTimer = setTimeout(function () {
			searchCity($row, value);
		}, debounceMs);
	});

	$table.on('click', '.woo-city-select-cities__suggest-item', function (event) {
		event.preventDefault();
		var $item = $(this);
		var $row = $item.closest('tr');
		$row.find('.woo-city-select-cities__city').val($item.attr('data-city'));
		$row.find('.woo-city-select-cities__region').val($item.attr('data-region'));
		hideSuggest($row);
	});

	$(document).on('click', function (event) {
		if (!$(event.target).closest('.woo-city-select-cities__city-cell').length) {
			$table.find('.woo-city-select-cities__suggest').attr('hidden', true).empty();
		}
	});
})(jQuery);
