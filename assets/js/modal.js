(function () {
	'use strict';

	function boot() {
		var dialog = document.getElementById('woo-city-select-dialog');
		if (!dialog || typeof wooCitySelect === 'undefined') {
			return;
		}

		var search = dialog.querySelector('.woo-city-select-modal__search');
		var errorEl = dialog.querySelector('.woo-city-select-modal__error');
		var majorList = dialog.querySelector('[data-woo-city-select-major]');
		var suggestList = dialog.querySelector('[data-woo-city-select-suggest]');
		var debounceTimer = null;
		var debounceMs = 300;

		function showError(message) {
			errorEl.textContent = message;
			errorEl.hidden = false;
		}

		function clearError() {
			errorEl.textContent = '';
			errorEl.hidden = true;
		}

		function showMajor() {
			majorList.hidden = false;
			suggestList.hidden = true;
			suggestList.innerHTML = '';
		}

		function showSuggest(html) {
			majorList.hidden = true;
			suggestList.hidden = false;
			suggestList.innerHTML = html;
		}

		function resetModal() {
			search.value = '';
			clearError();
			showMajor();
		}

		function openModal() {
			resetModal();
			dialog.showModal();
			search.focus();
		}

		function closeModal() {
			if (dialog.open) {
				dialog.close();
			}
			resetModal();
		}

		function updateTriggers(city) {
			document.querySelectorAll('.woo-city-select__name').forEach(function (el) {
				el.textContent = city;
			});
		}

		function saveCity(city, region) {
			var body = new FormData();
			body.append('action', 'woo_city_select_save');
			body.append('nonce', wooCitySelect.nonce);
			body.append('city', city);
			body.append('region', region);

			fetch(wooCitySelect.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						return;
					}
					updateTriggers(payload.data.city);
					closeModal();
				});
		}

		function renderItems(items) {
			if (!items.length) {
				showSuggest(
					'<p class="woo-city-select-modal__empty">' +
						wooCitySelect.i18n.nothingFound +
						'</p>'
				);
				return;
			}

			var html = items
				.map(function (item) {
					return (
						'<button type="button" class="woo-city-select-modal__item" data-city="' +
						escapeAttr(item.city) +
						'" data-region="' +
						escapeAttr(item.region) +
						'">' +
						escapeHtml(item.label || item.city) +
						'</button>'
					);
				})
				.join('');
			showSuggest(html);
		}

		function escapeHtml(value) {
			return String(value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;');
		}

		function escapeAttr(value) {
			return escapeHtml(value).replace(/'/g, '&#39;');
		}

		function searchCities(query) {
			clearError();
			var body = new FormData();
			body.append('action', 'woo_city_select_search');
			body.append('nonce', wooCitySelect.nonce);
			body.append('query', query);

			fetch(wooCitySelect.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (response) {
					return response.json().then(function (payload) {
						return { ok: response.ok, payload: payload };
					});
				})
				.then(function (result) {
					var payload = result.payload;
					if (!payload || !payload.success) {
						var code = payload && payload.data && payload.data.code;
						var message =
							payload && payload.data && payload.data.message
								? payload.data.message
								: wooCitySelect.i18n.unavailable;
						if (code === 'not_configured') {
							message = wooCitySelect.i18n.notConfigured;
						}
						showError(message);
						showMajor();
						return;
					}
					renderItems(payload.data.items || []);
				})
				.catch(function () {
					showError(wooCitySelect.i18n.unavailable);
					showMajor();
				});
		}

		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('.woo-city-select');
			if (trigger) {
				event.preventDefault();
				openModal();
				return;
			}

			var close = event.target.closest('.woo-city-select-modal__close');
			if (close && dialog.contains(close)) {
				event.preventDefault();
				closeModal();
				return;
			}

			var item = event.target.closest('.woo-city-select-modal__item');
			if (item && dialog.contains(item)) {
				event.preventDefault();
				saveCity(item.getAttribute('data-city') || '', item.getAttribute('data-region') || '');
			}
		});

		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) {
				closeModal();
			}
		});

		dialog.addEventListener('close', function () {
			resetModal();
		});

		search.addEventListener('input', function () {
			var value = search.value.trim();
			clearTimeout(debounceTimer);

			if (value.length < 3) {
				clearError();
				showMajor();
				return;
			}

			debounceTimer = setTimeout(function () {
				searchCities(value);
			}, debounceMs);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
