/**
 * News slider hero: Swiper initialisation
 */
(function () {
	function init() {
		if (typeof Swiper === "undefined") return;

		var sliders = document.querySelectorAll(".news-slider-swiper");
		if (!sliders.length) return;

		sliders.forEach(function (sliderEl) {
			var sectionEl = sliderEl.closest(".section-news-slider");
			if (!sectionEl) return;

			var footerEl = sectionEl.querySelector(".swiper-footer");
			var paginationEl = footerEl ? footerEl.querySelector(".swiper-pagination") : sectionEl.querySelector(".swiper-pagination");
			var nextEl = footerEl ? footerEl.querySelector(".swiper-button-next") : sectionEl.querySelector(".swiper-button-next");
			var prevEl = footerEl ? footerEl.querySelector(".swiper-button-prev") : sectionEl.querySelector(".swiper-button-prev");

			new Swiper(sliderEl, {
				loop: false,
				slidesPerView: 1.3,
				spaceBetween: 16,
				slidesOffsetAfter: 16,
				pagination: {
					el: paginationEl,
					clickable: true,
				},
				navigation: {
					nextEl: nextEl,
					prevEl: prevEl,
				},
				breakpoints: {
					768: {
						slidesPerView: 2.5,
					},
					992: {
						slidesPerView: 3.5,
					},
				},
			});
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
