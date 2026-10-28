/**
 * Bootstrap-style accordion collapse behavior.
 */
(function () {
	var DURATION = 280;

	function getTarget(button) {
		var selector = button.getAttribute('data-bs-target');
		if (!selector) return null;
		return document.querySelector(selector);
	}

	function setExpanded(button, expanded) {
		button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		button.classList.toggle('collapsed', !expanded);
	}

	function showCollapse(collapse, button) {
		if (!collapse || collapse.classList.contains('show') || collapse.classList.contains('collapsing')) return;
		collapse.classList.remove('collapse');
		collapse.classList.add('collapsing');
		collapse.style.height = '0px';

		window.requestAnimationFrame(function () {
			collapse.style.height = collapse.scrollHeight + 'px';
		});

		window.setTimeout(function () {
			collapse.classList.remove('collapsing');
			collapse.classList.add('collapse', 'show');
			collapse.style.height = '';
		}, DURATION);

		if (button) setExpanded(button, true);
	}

	function hideCollapse(collapse, button) {
		if (!collapse || !collapse.classList.contains('show') || collapse.classList.contains('collapsing')) return;
		collapse.style.height = collapse.scrollHeight + 'px';
		collapse.classList.remove('collapse', 'show');
		collapse.classList.add('collapsing');

		window.requestAnimationFrame(function () {
			collapse.style.height = '0px';
		});

		window.setTimeout(function () {
			collapse.classList.remove('collapsing');
			collapse.classList.add('collapse');
			collapse.style.height = '';
		}, DURATION);

		if (button) setExpanded(button, false);
	}

	function init() {
		document.querySelectorAll('.accordion-module .accordion-button[data-bs-toggle="collapse"]').forEach(function (button) {
			button.addEventListener('click', function (e) {
				e.preventDefault();
				var collapse = getTarget(button);
				if (!collapse) return;

				var parentSelector = collapse.getAttribute('data-bs-parent');
				if (parentSelector) {
					var parent = document.querySelector(parentSelector);
					if (parent) {
						parent.querySelectorAll('.accordion-collapse.show').forEach(function (openCollapse) {
							if (openCollapse !== collapse) {
								var openBtn = document.querySelector('.accordion-button[data-bs-target="#' + openCollapse.id + '"]');
								hideCollapse(openCollapse, openBtn);
							}
						});
					}
				}

				if (collapse.classList.contains('show')) {
					hideCollapse(collapse, button);
				} else {
					showCollapse(collapse, button);
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
