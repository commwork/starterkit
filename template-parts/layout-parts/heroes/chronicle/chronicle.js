/**
 * Chronicle timeline: grey line fills red on scroll; circles turn red when fill reaches them.
 * Supports multiple .chronicle-content blocks per page (layout + module).
 */
(function () {
	function createChronicleBlockUpdater(content) {
		var fillEl = content.querySelector('.chronicle-line-fill');
		if (!fillEl) return null;

		var years = content.querySelectorAll('.chronicle-year');
		var moduleEl = content.closest('.chronicle-module');
		var nav = moduleEl ? moduleEl.querySelector('.chronicle-navigation') : null;
		var wrapper = nav ? nav.querySelector('.chronicle-navigation-list-wrapper') : null;
		var lastCenteredActiveId = null;

		function centerActiveItem(activeId) {
			if (!wrapper || activeId == null) return;
			var activeItem = wrapper.querySelector('.chronicle-navigation-item.active');
			if (!activeItem) return;
			var itemRect = activeItem.getBoundingClientRect();
			var wrapperRect = wrapper.getBoundingClientRect();
			var targetScrollLeft = wrapper.scrollLeft + (itemRect.left - wrapperRect.left) + activeItem.offsetWidth / 2 - wrapper.clientWidth / 2;
			targetScrollLeft = Math.max(0, Math.min(targetScrollLeft, wrapper.scrollWidth - wrapper.clientWidth));
			wrapper.scrollLeft = targetScrollLeft;
		}

		return function update() {
			var rect = content.getBoundingClientRect();
			var scrollY = window.scrollY;
			var contentTop = rect.top + scrollY;
			var contentHeight = content.offsetHeight;
			var progress = (scrollY + window.innerHeight / 2 - contentTop) / contentHeight;
			progress = Math.max(0, Math.min(1, progress));

			fillEl.style.setProperty('--chronicle-fill-height', (progress * 100) + '%');

			var activeIndex = -1;
			for (var i = 0; i < years.length; i++) {
				var marker = years[i].querySelector('.chronicle-year-marker');
				if (!marker) continue;
				var markerRect = marker.getBoundingClientRect();
				var markerCenter = markerRect.top + markerRect.height / 2 + scrollY;
				if (progress >= (markerCenter - contentTop) / contentHeight) activeIndex = i;
			}
			for (var j = 0; j < years.length; j++) {
				years[j].classList.toggle('is-reached', j === activeIndex);
			}

			if (nav) {
				var showNav = activeIndex >= 0 && activeIndex < years.length - 1;
				nav.classList.toggle('is-visible', showNav);

				var activeId = activeIndex >= 0 ? (years[activeIndex].querySelector('.year-anchor[id]') || {}).id : null;
				var navLinks = nav.querySelectorAll('.chronicle-navigation-link');
				for (var k = 0; k < navLinks.length; k++) {
					var href = navLinks[k].getAttribute('href') || '';
					var linkId = href.indexOf('#') === 0 ? href.slice(1) : '';
					var item = navLinks[k].closest('.chronicle-navigation-item');
					if (item) item.classList.toggle('active', linkId === activeId);
				}
				if (activeId !== lastCenteredActiveId) {
					lastCenteredActiveId = activeId;
					centerActiveItem(activeId);
				}
			}
		};
	}

	function init() {
		var updaters = [];
		document.querySelectorAll('.chronicle-content').forEach(function (content) {
			var fn = createChronicleBlockUpdater(content);
			if (fn) updaters.push(fn);
		});
		if (!updaters.length) return;

		var rafScheduled = false;
		function onScroll() {
			if (rafScheduled) return;
			rafScheduled = true;
			requestAnimationFrame(function () {
				rafScheduled = false;
				updaters.forEach(function (fn) {
					fn();
				});
			});
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', function () {
			updaters.forEach(function (fn) {
				fn();
			});
		});
		updaters.forEach(function (fn) {
			fn();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
