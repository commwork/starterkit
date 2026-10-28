/**
 * Jobs layout: Kategorie / Ort / Berufsbezeichnung filters via admin-ajax (no full page reload).
 */
(function () {
	'use strict';

	var FILTER_KEYS = ['kategorie', 'ort', 'beruf'];
	var FILTER_PARAMS = {
		kategorie: 'job_kategorie',
		ort: 'job_ort',
		beruf: 'job_beruf',
	};
	var CASCADE_CLEAR = {
		kategorie: ['ort', 'beruf'],
		ort: ['beruf'],
	};

	var sections = [];
	var popstateBound = false;

	function parseConfig(section) {
		var raw = section.getAttribute('data-jobs-filters-config');
		if (!raw) {
			return null;
		}
		try {
			return JSON.parse(raw);
		} catch (e) {
			return null;
		}
	}

	function scrollToFilters(filtersEl) {
		if (!filtersEl) {
			return;
		}
		var rect = filtersEl.getBoundingClientRect();
		var marginTop = parseFloat(window.getComputedStyle(filtersEl).scrollMarginTop) || 0;
		var targetY = Math.max(0, window.pageYOffset + rect.top - marginTop);
		var startY = window.pageYOffset;
		var change = targetY - startY;
		if (Math.abs(change) < 4) {
			return;
		}
		var duration = 220;
		var startTime = null;
		function easeOutCubic(t) {
			return 1 - Math.pow(1 - t, 3);
		}
		function step(timestamp) {
			if (startTime === null) {
				startTime = timestamp;
			}
			var elapsed = timestamp - startTime;
			var t = Math.min(1, elapsed / duration);
			window.scrollTo(0, startY + change * easeOutCubic(t));
			if (t < 1) {
				requestAnimationFrame(step);
			}
		}
		requestAnimationFrame(step);
	}

	function updateFilterSelect(select, options, emptyLabel, currentValue) {
		if (!select || !Array.isArray(options)) {
			return;
		}
		var selected = typeof currentValue === 'string' ? currentValue : select.value;
		var hasSelected = selected === '';
		var fragment = document.createDocumentFragment();
		var emptyOpt = document.createElement('option');
		emptyOpt.value = '';
		emptyOpt.textContent = emptyLabel || '';
		fragment.appendChild(emptyOpt);

		options.forEach(function (opt) {
			if (!opt || !opt.slug) {
				return;
			}
			if (opt.slug === selected) {
				hasSelected = true;
			}
			var option = document.createElement('option');
			option.value = opt.slug;
			option.textContent = opt.name || opt.slug;
			fragment.appendChild(option);
		});

		if (typeof select.replaceChildren === 'function') {
			select.replaceChildren(fragment);
		} else {
			while (select.firstChild) {
				select.removeChild(select.firstChild);
			}
			select.appendChild(fragment);
		}

		select.value = hasSelected ? selected : '';
	}

	function getEmptyLabel(select, labels, key) {
		if (labels && labels[key]) {
			return labels[key];
		}
		if (select && select.options.length) {
			return select.options[0].textContent;
		}
		return '';
	}

	function applyFilterOptions(filterOptions, labels, selects) {
		if (!filterOptions || !selects) {
			return;
		}
		FILTER_KEYS.forEach(function (key) {
			updateFilterSelect(
				selects[key],
				filterOptions[key] || [],
				getEmptyLabel(selects[key], labels, key),
				selects[key].value
			);
		});
	}

	function hasActiveFilters(selects, cfg) {
		var kategorieActive = !!(selects.kategorie && selects.kategorie.value);
		if (cfg && cfg.hideKategorieSelect) {
			kategorieActive = false;
		}
		return !!(
			kategorieActive ||
			(selects.ort && selects.ort.value) ||
			(selects.beruf && selects.beruf.value)
		);
	}

	function updateResetVisibility(ctx) {
		if (!ctx.actionsEl) {
			return;
		}
		ctx.actionsEl.hidden = !hasActiveFilters(ctx.selects, ctx.cfg);
	}

	function updateFiltersVisibility(ctx) {
		if (!ctx.filtersEl) {
			return;
		}
		ctx.filtersEl.hidden = !(ctx.cfg && ctx.cfg.showFilters);
	}

	function updateUrl(selects) {
		var params = new URLSearchParams(window.location.search);

		FILTER_KEYS.forEach(function (key) {
			var param = FILTER_PARAMS[key];
			var value = selects[key] ? selects[key].value : '';
			if (value) {
				params.set(param, value);
			} else {
				params.delete(param);
			}
		});

		var qs = params.toString();
		var url = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
		window.history.pushState({}, '', url);
	}

	function getDefaultKategorieValue(cfg) {
		return cfg && typeof cfg.defaultKategorieSlug === 'string' ? cfg.defaultKategorieSlug : '';
	}

	function syncSelectsFromUrl(selects, cfg) {
		var params = new URLSearchParams(window.location.search);
		var defaultKategorie = getDefaultKategorieValue(cfg);

		selects.kategorie.value = params.get(FILTER_PARAMS.kategorie) || defaultKategorie || '';
		selects.ort.value = params.get(FILTER_PARAMS.ort) || '';
		selects.beruf.value = params.get(FILTER_PARAMS.beruf) || '';
	}

	function clearDependentFilters(selects, filterKey) {
		var keys = CASCADE_CLEAR[filterKey];
		if (!keys) {
			return;
		}
		keys.forEach(function (key) {
			if (selects[key]) {
				selects[key].value = '';
			}
		});
	}

	function fetchResults(ctx, options) {
		options = options || {};
		var pushState = options.pushState !== false;
		var selects = ctx.selects;

		if (ctx.abortController) {
			ctx.abortController.abort();
		}
		var controller = new AbortController();
		ctx.abortController = controller;

		var body = new URLSearchParams();
		body.set('action', ctx.cfg.action);
		body.set('nonce', ctx.cfg.nonce);
		body.set('category_ids', ctx.cfg.categoryIds.join(','));
		body.set('job_teaser_headline_tag', ctx.cfg.jobTeaserHeadlineTag || 'h3');
		FILTER_KEYS.forEach(function (key) {
			body.set(FILTER_PARAMS[key], selects[key] ? selects[key].value || '' : '');
		});

		ctx.resultsEl.classList.add('is-loading');
		ctx.resultsEl.setAttribute('aria-busy', 'true');

		fetch(ctx.cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			signal: controller.signal,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: body.toString(),
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (!data || !data.success || !data.data || typeof data.data.html !== 'string') {
					throw new Error('Invalid response');
				}
				ctx.resultsEl.innerHTML = data.data.html;
				if (data.data.filterOptions) {
					applyFilterOptions(data.data.filterOptions, ctx.cfg.filterLabels, selects);
				}
				if (typeof data.data.showFilters === 'boolean') {
					ctx.cfg.showFilters = data.data.showFilters;
					updateFiltersVisibility(ctx);
				}
				if (pushState) {
					updateUrl(selects);
				}
				if (ctx.filtersEl && !ctx.filtersEl.hidden) {
					scrollToFilters(ctx.filtersEl);
				}
			})
			.catch(function (err) {
				if (err && err.name === 'AbortError') {
					return;
				}
				ctx.resultsEl.innerHTML =
					'<p class="not-found-text txt">' +
					'Filter konnte nicht geladen werden. Bitte Seite neu laden.' +
					'</p>';
			})
			.finally(function () {
				if (ctx.abortController !== controller) {
					return;
				}
				ctx.resultsEl.classList.remove('is-loading');
				ctx.resultsEl.removeAttribute('aria-busy');
				updateResetVisibility(ctx);
			});
	}

	function onPopstate() {
		sections.forEach(function (ctx) {
			syncSelectsFromUrl(ctx.selects, ctx.cfg);
			updateResetVisibility(ctx);
			fetchResults(ctx, { pushState: false });
		});
	}

	function bindPopstateOnce() {
		if (popstateBound) {
			return;
		}
		popstateBound = true;
		window.addEventListener('popstate', onPopstate);
	}

	function initSection(section) {
		var cfg = parseConfig(section);
		if (!cfg || !cfg.ajaxUrl || !cfg.nonce || !Array.isArray(cfg.categoryIds)) {
			return null;
		}

		var form = section.querySelector('.jobs-with-filters-module__form');
		var resultsEl = section.querySelector('.jobs-with-filters-module__results');
		var filtersEl = section.querySelector('.jobs-with-filters-module__filters');
		var selects = {
			kategorie: section.querySelector('[data-jobs-filter="kategorie"]'),
			ort: section.querySelector('[data-jobs-filter="ort"]'),
			beruf: section.querySelector('[data-jobs-filter="beruf"]'),
		};

		if (!resultsEl) {
			return null;
		}

		if (!cfg.showFilters) {
			return null;
		}

		if (!form || !selects.kategorie || !selects.ort || !selects.beruf) {
			return null;
		}

		var ctx = {
			section: section,
			cfg: cfg,
			form: form,
			resultsEl: resultsEl,
			filtersEl: filtersEl,
			selects: selects,
			actionsEl: section.querySelector('.jobs-with-filters-module__actions'),
			abortController: null,
		};

		syncSelectsFromUrl(selects, cfg);
		updateFiltersVisibility(ctx);
		updateResetVisibility(ctx);

		function onFilterChange(e) {
			if (e && e.target) {
				var filterKey = e.target.getAttribute('data-jobs-filter');
				if (filterKey === 'kategorie' && cfg.hideKategorieSelect) {
					filterKey = '';
				}
				if (filterKey) {
					clearDependentFilters(selects, filterKey);
				}
			}
			updateResetVisibility(ctx);
			fetchResults(ctx);
		}

		function onFilterReset() {
			var defaultKategorie = getDefaultKategorieValue(cfg);

			selects.kategorie.value = defaultKategorie;
			selects.ort.value = '';
			selects.beruf.value = '';
			applyFilterOptions(cfg.initialFilterOptions || {}, cfg.filterLabels, selects);
			selects.kategorie.value = defaultKategorie;
			updateResetVisibility(ctx);
			fetchResults(ctx);
		}

		form.addEventListener('change', function (e) {
			if (e.target && e.target.matches('[data-jobs-filter]')) {
				onFilterChange(e);
			}
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
		});

		var resetBtn = section.querySelector('[data-jobs-filter-reset]');
		if (resetBtn) {
			resetBtn.addEventListener('click', onFilterReset);
		}

		bindPopstateOnce();

		return ctx;
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-jobs-filters-config]').forEach(function (section) {
			var ctx = initSection(section);
			if (ctx) {
				sections.push(ctx);
			}
		});
	});
})();
