/**
 * ACF admin helpers (`assets/js/admin.js`, enqueued in functions.php as `starterkit_acf_empty_state`).
 *
 * 1. Empty state: toggles `is-empty` on `.acf-field` so CSS can style fields with no content.
 * 2. Main layouts popup: tags the shared flexible-content picker tooltip when opened from the
 *    top-level `layouts` field only (see `main-layout-popup` in admin.scss).
 */
(function ($) {
	// -------------------------------------------------------------------------
	// Empty-state: detect whether a field has meaningful content (by ACF type)
	// -------------------------------------------------------------------------

	function hasTextValue($field) {
		var val = $field.find("input[type='text'], input[type='email'], input[type='url'], input[type='number'], textarea").first().val();
		return String(val || "").trim() !== "";
	}

	function hasWysiwygValue($field) {
		var val = $field.find("textarea.wp-editor-area, textarea").first().val();
		// Strip tags so empty HTML like "<p><br></p>" counts as empty.
		var text = String(val || "").replace(/<[^>]*>/g, "").trim();
		return text !== "";
	}

	function hasLinkValue($field) {
		var val = $field.find(".acf-link .input-url").first().val();
		return String(val || "").trim() !== "";
	}

	function hasRepeaterRows($field) {
		return $field.find(".acf-repeater .acf-row:not(.acf-clone)").length > 0;
	}

	function hasFlexibleRows($field) {
		return $field.find(".acf-flexible-content .values .layout").length > 0;
	}

	function hasSelectedOption($field) {
		if ($field.find("input[type='checkbox']:checked, input[type='radio']:checked").length) {
			return true;
		}
		var selectVal = $field.find("select").first().val();
		if (Array.isArray(selectVal)) {
			return selectVal.length > 0;
		}
		return String(selectVal || "").trim() !== "";
	}

	/** @returns {boolean} True if the field should be treated as having no editor-facing content. */
	function isFieldEmpty($field) {
		var type = String($field.data("type") || "");

		if (type === "repeater") return !hasRepeaterRows($field);
		if (type === "flexible_content") return !hasFlexibleRows($field);
		if (type === "wysiwyg") return !hasWysiwygValue($field);
		if (type === "link") return !hasLinkValue($field);
		if (type === "checkbox" || type === "radio" || type === "select" || type === "true_false") {
			return !hasSelectedOption($field);
		}

		// Text, textarea, oembed, and any other type: use generic text extraction.
		return !hasTextValue($field);
	}

	function refreshFieldState($field) {
		if (!$field || !$field.length || !$field.hasClass("acf-field")) return;
		$field.toggleClass("is-empty", isFieldEmpty($field));
	}

	function refreshAllFields($scope) {
		var $root = $scope && $scope.length ? $scope : $(document);
		$root.find(".acf-field").each(function () {
			refreshFieldState($(this));
		});
	}

	// Initial load and whenever ACF injects new fields (repeaters, layouts, ajax tabs, …).
	acf.addAction("ready", function ($el) {
		refreshAllFields($el);
	});

	acf.addAction("append", function ($el) {
		refreshAllFields($el);
	});

	$(document).on("input change keyup", ".acf-field input, .acf-field textarea, .acf-field select", function () {
		refreshFieldState($(this).closest(".acf-field"));
	});

	// Add/remove rows or layouts: DOM updates after click, so re-check shortly after.
	$(document).on("click", ".acf-actions a, .acf-fc-layout-controls a, .acf-repeater .acf-icon", function () {
		window.setTimeout(function () {
			refreshAllFields($(document));
		}, 30);
	});

	// -------------------------------------------------------------------------
	// Chronicle: sync accordion title "Jahr" with field "titel_jahreszahl"
	// -------------------------------------------------------------------------
	function updateChronicleYearAccordionLabels($scope) {
		var $root = $scope && $scope.length ? $scope : $(document);

		$root
			.find(".year-repeater-content .acf-field[data-name='titel_jahreszahl'] input[type='text']")
			.each(function () {
				var $input = $(this);
				var value = String($input.val() || "").trim();
				var $row = $input.closest(".acf-row");
				if (!$row.length || $row.hasClass("acf-clone")) return;

				var $titleLabel = $row.find("> td .acf-accordion-title > label").first();
				if (!$titleLabel.length) return;

				$titleLabel.text(value !== "" ? value : "Jahr");
			});
	}

	acf.addAction("ready", function ($el) {
		updateChronicleYearAccordionLabels($el);
	});

	acf.addAction("append", function ($el) {
		updateChronicleYearAccordionLabels($el);
	});

	$(document).on(
		"input change keyup",
		".year-repeater-content .acf-field[data-name='titel_jahreszahl'] input[type='text']",
		function () {
			updateChronicleYearAccordionLabels($(this).closest(".acf-row"));
		}
	);

	$(document).on("click", ".year-repeater-content .acf-actions a, .year-repeater-content .acf-repeater .acf-icon", function () {
		window.setTimeout(function () {
			updateChronicleYearAccordionLabels($(document));
		}, 40);
	});

	// -------------------------------------------------------------------------
	// Top-level `layouts` field: sync layout handle title from header `titel`
	// -------------------------------------------------------------------------
	var mainLayoutsSelector =
		"#layouts > .acf-input > .acf-flexible-content > .values > .layout";
	var mainLayoutTitelSelector =
		mainLayoutsSelector +
		" .acf-field[data-name='header'] .acf-field[data-name='titel'] input[type='text']";

	function escapeHtml(text) {
		return $("<div>").text(String(text || "")).html();
	}

	function getTitelLabelFromLayout($layout) {
		var parts = [];

		$layout
			.find(".acf-field[data-name='header'] .acf-field[data-name='titel'] .acf-flexible-content > .values > .layout")
			.each(function () {
				var $row = $(this);
				if ($row.hasClass("acf-clone") || $row.data("layout") === "line_break") {
					return;
				}

				var text = String($row.find("input[type='text']").first().val() || "").trim();
				if (text !== "") {
					parts.push(text);
				}
			});

		return parts.join(" ");
	}

	function buildMainLayoutTitleHtml(label, layoutLabel) {
		if (label === "") {
			return "";
		}

		var html = escapeHtml(label);
		if (layoutLabel !== "") {
			html +=
				' <span style="opacity: .7;font-weight:normal;">(' +
				escapeHtml(layoutLabel) +
				")</span>";
		}

		return html;
	}

	function updateMainLayoutTitles($scope) {
		var $root = $scope && $scope.length ? $scope : $(document);

		$root.find(mainLayoutsSelector).each(function () {
			var $layout = $(this);
			if ($layout.closest(".clones").length) {
				return;
			}

			var label = getTitelLabelFromLayout($layout);
			if (label === "") {
				label = String($layout.find(".acf-field[data-name='headline'] input[type='text']").first().val() || "").trim();
			}
			if (label === "") {
				return;
			}

			var layoutLabel = String($layout.data("label") || "");
			var titleHtml = buildMainLayoutTitleHtml(label, layoutLabel);
			var $handle = $layout.children(".acf-fc-layout-actions-wrap").first();

			$handle.find(".acf-fc-layout-title").first().html(titleHtml);
			$handle.find(".acf-fc-layout-original-title").first().html("(" + titleHtml + ")");

			// Headline is the source of truth for page layouts; drop stale manual renames.
			$layout.attr("data-renamed", "0");
			$layout.children("input.acf-fc-layout-custom-label").val("");
		});
	}

	acf.addAction("ready", function ($el) {
		updateMainLayoutTitles($el);
	});

	acf.addAction("append", function ($el) {
		updateMainLayoutTitles($el);
	});

	$(document).on("input change keyup", mainLayoutTitelSelector, function () {
		updateMainLayoutTitles($(this).closest(mainLayoutsSelector));
	});

	$(document).on(
		"click",
		mainLayoutsSelector +
			" .acf-field[data-name='header'] .acf-field[data-name='titel'] .acf-fc-layout-controls a, " +
			mainLayoutsSelector +
			" .acf-field[data-name='header'] .acf-field[data-name='titel'] .acf-actions a",
		function () {
			window.setTimeout(function () {
				updateMainLayoutTitles($(document));
			}, 40);
		}
	);

	// -------------------------------------------------------------------------
	// Main `layouts` field: mark the global `.acf-fc-popup` tooltip for custom CSS
	// -------------------------------------------------------------------------
	// ACF reuses one tooltip in the DOM for all flexible-content “add layout” pickers.
	// We only add `main-layout-popup` when the user opens it from the page-level layouts
	// control; nested flexible fields must not get the full-screen styled picker.

	var mainLayoutsAddBtn =
		"#layouts > div.acf-input > div > div:nth-child(6) > a[data-name='add-layout']";
	// Top-level layout rows in `#layouts` only (`>` chain stops nested flexible-content pickers).
	var mainLayoutsRowControlBtn =
		"#layouts > .acf-input > .acf-flexible-content > .values.ui-sortable > .layout > .acf-fc-layout-actions-wrap > .acf-fc-layout-controls > .acf-js-tooltip:nth-child(1)";
	var mainLayoutsPopupTriggers = mainLayoutsAddBtn + ", " + mainLayoutsRowControlBtn;

	function tagMainLayoutPopup() {
		$(".acf-tooltip.acf-fc-popup").removeClass("main-layout-popup");
		// Let ACF create/position the tooltip first, then tag it.
		window.setTimeout(function () {
			$(".acf-tooltip.acf-fc-popup").addClass("main-layout-popup");
		}, 50);
	}

	$(document).on("click", mainLayoutsPopupTriggers, tagMainLayoutPopup);

	$(document).on(
		"click",
		"a[data-name='add-layout'], .acf-fc-layout-controls > .acf-js-tooltip:nth-child(1)",
		function () {
			if ($(this).is(mainLayoutsPopupTriggers)) {
				return;
			}
			$(".acf-tooltip.acf-fc-popup").removeClass("main-layout-popup");
		}
	);
})(jQuery);


