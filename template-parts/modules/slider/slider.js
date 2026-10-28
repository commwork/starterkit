/**
 * Slider module: Swiper initialisation
 */
(function () {
	function initSliderCaptionToggle() {
		document.querySelectorAll('.slider-meta-caption-toggle').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var meta = btn.closest('.slider-img-meta--collapsible-caption');
				if (!meta) return;
				var open = meta.classList.toggle('is-caption-open');
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				var label = btn.querySelector('.slider-meta-caption-toggle-label');
				var closedText = btn.getAttribute('data-slider-caption-label-closed') || 'Mehr Info';
				var openText = btn.getAttribute('data-slider-caption-label-open') || 'Weniger';
				if (label) {
					label.textContent = open ? openText : closedText;
				}
				var ariaClosed = btn.getAttribute('data-slider-aria-label-closed');
				var ariaOpen = btn.getAttribute('data-slider-aria-label-open');
				if (ariaClosed && ariaOpen) {
					btn.setAttribute('aria-label', open ? ariaOpen : ariaClosed);
				}
			});
		});
	}

	function init() {
		initSliderCaptionToggle();

		if (typeof Swiper === 'undefined') return;

		document.querySelectorAll('.slider-swiper').forEach(function (sliderEl) {
			var moduleEl = sliderEl.closest('.slider-module');
			if (!moduleEl) return;

			var slideCount = sliderEl.querySelectorAll('.swiper-slide').length;
			if (!slideCount) return;

			var footerEl = moduleEl.querySelector('.swiper-footer');
			var paginationEl = footerEl ? footerEl.querySelector('.swiper-pagination') : null;
			var nextEl = footerEl ? footerEl.querySelector('.swiper-button-next') : null;
			var prevEl = footerEl ? footerEl.querySelector('.swiper-button-prev') : null;

			new Swiper(sliderEl, {
				loop: slideCount > 1,
				slidesPerView: 1,
				spaceBetween: 0,
				watchOverflow: true,
				pagination: paginationEl
					? {
							el: paginationEl,
							clickable: true,
						}
					: undefined,
				navigation:
					nextEl && prevEl
						? {
								nextEl: nextEl,
								prevEl: prevEl,
							}
						: undefined,
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
