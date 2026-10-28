/**
 * Process module: Swiper initialisation
 */
(function () {
	function init() {
		if (typeof Swiper === 'undefined') return;

		var sliders = document.querySelectorAll('.process-swiper');
		if (!sliders.length) return;

		sliders.forEach(function (sliderEl) {
			var moduleEl = sliderEl.closest('.process-module');
			if (!moduleEl) return;
			var sectionEl = sliderEl.closest('.section-process');
			var sliderScope = sectionEl || moduleEl;
			var footerEl = sliderScope.querySelector('.swiper-footer');
			var paginationEl = footerEl ? footerEl.querySelector('.swiper-pagination') : sliderScope.querySelector('.swiper-pagination');
			var nextEl = footerEl ? footerEl.querySelector('.swiper-button-next') : sliderScope.querySelector('.swiper-button-next');
			var prevEl = footerEl ? footerEl.querySelector('.swiper-button-prev') : sliderScope.querySelector('.swiper-button-prev');

			new Swiper(sliderEl, {
				loop: false,
				slidesPerView: 1.3,
				spaceBetween: 16,
				pagination: {
					el: paginationEl,
					clickable: true
				},
				navigation: {
					nextEl: nextEl,
					prevEl: prevEl
				},
				breakpoints: {
					768: {
						slidesPerView: 2.5
					},
					992: {
						slidesPerView: 3.5
					}
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
