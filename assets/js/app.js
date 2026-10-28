// Header nav: show matching children/summary on click of parent
var DESKTOP_NAV_MQ = '(min-width: 1300px)';

function initHeaderNavHover() {
	var wrapper = document.querySelector('.header-content');
	var list = document.querySelector('.header-nav-list');
	var childrenContainer = document.querySelector('.header-nav-children');
	var summariesContainer = document.querySelector('.header-nav-summaries');
	var overlay = document.querySelector('.header-nav-overlay');
	var openPageItemClass = null;
	var openParentLi = null;
	var desktopNavMq = window.matchMedia(DESKTOP_NAV_MQ);

	if (!wrapper || !list) return;

	var summaries = summariesContainer ? Array.prototype.slice.call(summariesContainer.querySelectorAll('.header-nav-summary')) : [];
	var parentItems = Array.prototype.slice.call(list.querySelectorAll('li.page_item_has_children'));

	function getPageItemClass(li) {
		var match = li.className.match(/\bpage-item-\d+/);
		return match ? match[0] : null;
	}

	function setVisibility(el, isVisible) {
		if (!el) return;
		el.style.display = isVisible ? '' : 'none';
		el.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
	}

	function setDesktopMenuLock(isLocked) {
		document.documentElement.classList.toggle('desktop-menu-open', isLocked);
		document.body.classList.toggle('desktop-menu-open', isLocked);
	}

	function hasSummaryMatch(pageItemClass) {
		return summaries.some(function (summary) {
			return summary.classList.contains(pageItemClass);
		});
	}

	function showSummariesFor(pageItemClass) {
		summaries.forEach(function (summary) {
			setVisibility(summary, summary.classList.contains(pageItemClass));
		});
	}

	function setActiveParent(li) {
		parentItems.forEach(function (item) {
			item.classList.remove('nav-parent-active');
			var trigger = item.querySelector(':scope > a[href], :scope > button');
			if (trigger) {
				trigger.setAttribute('aria-expanded', item === li ? 'true' : 'false');
			}
		});
		if (li) {
			li.classList.add('nav-parent-active');
		}
	}

	function showForParent(li) {
		var pageItemClass = getPageItemClass(li);
		if (!pageItemClass) return;
		if (!hasSummaryMatch(pageItemClass)) {
			hideAll();
			return;
		}

		if (childrenContainer) {
			childrenContainer.style.display = 'block';
		}
		if (overlay) {
			overlay.classList.add('is-visible');
			overlay.setAttribute('aria-hidden', 'false');
		}
		setDesktopMenuLock(true);

		showSummariesFor(pageItemClass);
		setActiveParent(li);
		openPageItemClass = pageItemClass;
		openParentLi = li;
	}

	function hideAll() {
		if (childrenContainer) childrenContainer.style.display = 'none';
		if (overlay) {
			overlay.classList.remove('is-visible');
			overlay.setAttribute('aria-hidden', 'true');
		}
		setDesktopMenuLock(false);
		summaries.forEach(function (summary) {
			setVisibility(summary, false);
		});
		setActiveParent(null);
		openPageItemClass = null;
		openParentLi = null;
	}

	/**
	 * First real submenu link in the open mega panel (DOM places panel after top-level links,
	 * so we move focus here after keyboard open).
	 */
	function focusFirstSubmenuLink(pageItemClass) {
		if (!summariesContainer || !pageItemClass) return;
		var summary = summariesContainer.querySelector('.header-nav-summary.' + pageItemClass);
		if (!summary) return;
		var entries = summary.querySelector('.header-nav-entries-content');
		var link = entries ? entries.querySelector('a[href]') : null;
		if (!link) {
			link = summary.querySelector('a[href]');
		}
		if (!link) {
			var toggle = summary.querySelector('.header-nav-summary-toggle');
			if (toggle) toggle.focus();
			return;
		}
		link.focus();
	}

	function getOpenSummary() {
		if (!openPageItemClass || !summariesContainer) return null;
		return summariesContainer.querySelector('.header-nav-summary.' + openPageItemClass);
	}

	function isFocusableVisible(el) {
		if (!el || el.getAttribute('tabindex') === '-1') return false;
		if (el.closest('[aria-hidden="true"]')) return false;
		if (el.disabled) return false;
		return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
	}

	function getPanelFocusables(summary) {
		if (!summary) return [];
		var sel = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
		return Array.prototype.filter.call(summary.querySelectorAll(sel), isFocusableVisible);
	}

	/**
	 * After the last focusable in the mega panel, Tab should continue the top bar (next item / burger),
	 * not skip to main content (panel is after top links in DOM order).
	 */
	function focusNextTopLevelAfter(currentOpenLi) {
		var lis = Array.prototype.slice.call(list.querySelectorAll(':scope > li'));
		var idx = lis.indexOf(currentOpenLi);
		hideAll();
		for (var i = idx + 1; i < lis.length; i++) {
			var trigger = lis[i].querySelector(':scope > a[href], :scope > button:not([disabled])');
			if (trigger && trigger.getAttribute('tabindex') !== '-1') {
				trigger.focus();
				return;
			}
		}
		var burger = document.querySelector('.header-burger');
		if (burger) {
			burger.focus();
		}
	}

	parentItems.forEach(function (li) {
		var parentTrigger = li.querySelector(':scope > a[href], :scope > button');

		li.addEventListener('click', function (e) {
			var pageItemClass = getPageItemClass(li);
			if (!pageItemClass) return;
			if (!hasSummaryMatch(pageItemClass)) return;

			// Parent items are navigation links; use click to toggle submenu instead.
			if (e.target.closest('a, button')) {
				e.preventDefault();
			}

			var closing = openPageItemClass === pageItemClass;
			// detail === 0: click synthesized from keyboard (Enter) on the link
			var fromKeyboard = e.detail === 0;

			if (closing) {
				hideAll();
			} else {
				showForParent(li);
				if (desktopNavMq.matches && fromKeyboard) {
					focusFirstSubmenuLink(pageItemClass);
				}
			}

			e.stopPropagation();
		});

		if (parentTrigger && parentTrigger.tagName === 'A') {
			parentTrigger.addEventListener('keydown', function (e) {
				if (e.key !== ' ' && e.key !== 'Spacebar') return;
				if (!desktopNavMq.matches) return;
				var pageItemClass = getPageItemClass(li);
				if (!pageItemClass || !hasSummaryMatch(pageItemClass)) return;
				e.preventDefault();
				var closing = openPageItemClass === pageItemClass;
				if (closing) {
					hideAll();
				} else {
					showForParent(li);
					focusFirstSubmenuLink(pageItemClass);
				}
			});
		}
	});

	if (summariesContainer) {
		summariesContainer.querySelectorAll('.header-nav-summary-toggle').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				hideAll();
			});
		});
	}

	document.addEventListener('click', function (e) {
		if (!wrapper.contains(e.target)) {
			hideAll();
		}
	});
	if (overlay) overlay.addEventListener('click', function () {
		hideAll();
	});
	if (desktopNavMq.addEventListener) {
		desktopNavMq.addEventListener('change', function (e) {
			if (!e.matches) {
				hideAll();
			}
		});
	}

	document.addEventListener('keydown', function (e) {
		if (!desktopNavMq.matches) return;

		if (e.key === 'Escape' && openPageItemClass) {
			var toFocus = openParentLi ? openParentLi.querySelector(':scope > a[href], :scope > button') : null;
			hideAll();
			if (toFocus) {
				toFocus.focus();
			}
			e.preventDefault();
			return;
		}

		if (e.key === 'Tab' && !e.shiftKey && openPageItemClass && openParentLi) {
			var summary = getOpenSummary();
			if (!summary || !summary.contains(document.activeElement)) return;
			var focusables = getPanelFocusables(summary);
			if (focusables.length === 0) return;
			var last = focusables[focusables.length - 1];
			if (document.activeElement !== last) return;
			e.preventDefault();
			focusNextTopLevelAfter(openParentLi);
			return;
		}

		if (e.key === 'Tab' && e.shiftKey && openPageItemClass && openParentLi) {
			var summaryBack = getOpenSummary();
			if (!summaryBack || !summaryBack.contains(document.activeElement)) return;
			var focusablesBack = getPanelFocusables(summaryBack);
			if (focusablesBack.length === 0) return;
			var first = focusablesBack[0];
			if (document.activeElement !== first) return;
			e.preventDefault();
			var parentTrigger = openParentLi.querySelector(':scope > a[href], :scope > button');
			if (parentTrigger) {
				parentTrigger.focus();
			}
		}
	});
}

// Header: stick to top when scrolled
function initHeaderStick() {
	var header = document.querySelector('.header');
	if (!header) return;

	var stickThreshold = 20;

	function updateStuck() {
		if (window.scrollY > stickThreshold) {
			header.classList.add('is-stuck');
		} else {
			header.classList.remove('is-stuck');
		}
	}

	window.addEventListener('scroll', function () {
		requestAnimationFrame(updateStuck);
	}, { passive: true });
	updateStuck();
}

// Mobile menu: burger toggle and expand/collapse children with +
function initMobileMenu() {
	var burger = document.querySelector('.header-burger');
	var mobileNav = document.getElementById('header-nav-mobile');
	var overlay = document.querySelector('.header-nav-overlay');
	var desktopNavMq = window.matchMedia(DESKTOP_NAV_MQ);
	var accordionTriggers = mobileNav ? mobileNav.querySelectorAll('.header-nav-mobile-trigger[aria-controls]') : [];
	if (!burger || !mobileNav) return;

	function setAccordionOpen(trigger, open) {
		var panelId = trigger.getAttribute('aria-controls');
		var panel = panelId ? document.getElementById(panelId) : null;
		trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (panel) {
			panel.hidden = !open;
		}
	}

	function closeSiblingAccordions(trigger) {
		var parentList = trigger.closest('ul');
		if (!parentList) return;

		parentList.querySelectorAll(':scope > .has-children > .header-nav-mobile-trigger[aria-controls]').forEach(function (btn) {
			if (btn !== trigger) {
				setAccordionOpen(btn, false);
			}
		});
	}

	function closeAllAccordions() {
		accordionTriggers.forEach(function (trigger) {
			setAccordionOpen(trigger, false);
		});
	}

	function closeMenu() {
		document.body.classList.remove('mobile-menu-open');
		burger.setAttribute('aria-expanded', 'false');
		mobileNav.setAttribute('aria-hidden', 'true');
		closeAllAccordions();
		if (overlay) {
			overlay.classList.remove('is-visible');
			overlay.setAttribute('aria-hidden', 'true');
		}
	}

	function setOverlayVisible(visible) {
		if (overlay) {
			overlay.classList.toggle('is-visible', visible);
			overlay.setAttribute('aria-hidden', !visible);
		}
	}

	burger.addEventListener('click', function () {
		var open = document.body.classList.toggle('mobile-menu-open');
		burger.setAttribute('aria-expanded', open);
		mobileNav.setAttribute('aria-hidden', !open);
		setOverlayVisible(open);
	});

	if (overlay) overlay.addEventListener('click', closeMenu);

	accordionTriggers.forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			var willOpen = trigger.getAttribute('aria-expanded') !== 'true';
			closeSiblingAccordions(trigger);
			setAccordionOpen(trigger, willOpen);
		});
	});

	if (desktopNavMq.addEventListener) {
		desktopNavMq.addEventListener('change', function (e) {
			if (e.matches) {
				closeMenu();
			}
		});
	} else {
		window.addEventListener('resize', function () {
			if (window.matchMedia(DESKTOP_NAV_MQ).matches) {
				closeMenu();
			}
		});
	}
}

// CF7 file fields: add .has-file on wrapper when a file is selected (see _forms.scss).
function initCf7FileFieldState() {
	function updateFileWrapState(input) {
		if (!input || input.type !== 'file') return;
		var wrap = input.closest('.files .wpcf7-form-control-wrap');
		if (!wrap) return;
		wrap.classList.toggle('has-file', !!(input.files && input.files.length > 0));
	}

	function refreshAll(root) {
		var scope = root && root.querySelectorAll ? root : document;
		scope.querySelectorAll('.files input[type="file"]').forEach(updateFileWrapState);
	}

	refreshAll();

	document.addEventListener('change', function (event) {
		if (event.target && event.target.matches && event.target.matches('.files input[type="file"]')) {
			updateFileWrapState(event.target);
		}
	});

	// CF7 can reset fields after submit; refresh checkmark state.
	['wpcf7submit', 'wpcf7mailsent', 'wpcf7invalid', 'wpcf7mailfailed', 'wpcf7spam'].forEach(function (name) {
		document.addEventListener(name, function (event) {
			refreshAll(event.target);
		});
	});
}

// Smooth scroll for in-page anchor links
// https://css-tricks.com/snippets/jquery/smooth-scrolling/
jQuery(document).ready(function ($) {
	$('a[href*="#"]')
	.not('[href="#"]')
	.not('[href="#0"]')
	.on('click', function (event) {
		if (
			location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') &&
			location.hostname == this.hostname
		) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
			if (target.length) {
				event.preventDefault();
				$('html, body').animate(
					{ scrollTop: target.offset().top },
					200,
					function () {
						var $target = $(target);
						$target.focus();
						if (!$target.is(':focus')) {
							$target.attr('tabindex', '-1');
							$target.focus();
						}
					}
				);
			}
		}
	});
});

document.addEventListener('DOMContentLoaded', function () {
	initHeaderNavHover();
	initHeaderStick();
	initMobileMenu();
	initCf7FileFieldState();
});
