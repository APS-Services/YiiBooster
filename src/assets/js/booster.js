/**
 * YiiBooster runtime helpers for Bootstrap 5.
 *
 * Bootstrap 5 replaced the jQuery plugin API with ES6 classes, and - unlike Bootstrap 3 - it does
 * NOT auto-initialise tooltips or popovers from data attributes. They must be constructed
 * explicitly, and disposed explicitly, or the Popper instance and its listeners outlive the
 * element they were attached to.
 *
 * That matters most around Yii's AJAX grid updates. The Bootstrap 3 code did:
 *
 *     jQuery('.popover').remove();
 *     jQuery(selector).popover();
 *
 * after every update. Under Bootstrap 5 that is a leak: it deletes the rendered popover element
 * but never disposes the instance, so each refresh strands another Popper. It is also global -
 * one grid refreshing tore down every tooltip on the page.
 *
 * So disposal happens in beforeAjaxUpdate, while the old nodes are still in the DOM and still
 * reachable, and initialisation happens in afterAjaxUpdate - both scoped to the widget's own
 * container.
 *
 * Written in ES5 on purpose: this ships to whatever browsers the host application supports.
 *
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 * @since 5.0.0
 */
(function (window, document) {
	'use strict';

	var Booster = window.Booster || (window.Booster = {});

	/**
	 * Bootstrap may not have loaded yet (or at all, if enableJS is false).
	 */
	function components() {
		return window.bootstrap || null;
	}

	function toArray(nodeList) {
		return Array.prototype.slice.call(nodeList || []);
	}

	/**
	 * @param {Element|Document|null} root
	 * @param {string} selector
	 * @returns {Array<Element>} matches inside root, plus root itself when it matches.
	 */
	function find(root, selector) {
		if (!root || !selector) {
			return [];
		}

		var found = toArray(root.querySelectorAll(selector));

		if (root.nodeType === 1 && typeof root.matches === 'function' && root.matches(selector)) {
			found.push(root);
		}

		return found;
	}

	/**
	 * Disposes any tooltip/popover instances attached inside `root`.
	 *
	 * Call this while the elements are still in the document - once Yii has replaced the grid's
	 * innerHTML the old nodes are unreachable and their instances cannot be disposed at all.
	 *
	 * @param {Element|Document} root
	 */
	Booster.disposeIn = function (root) {
		var bs = components();
		if (!bs || !root) {
			return;
		}

		['Tooltip', 'Popover'].forEach(function (name) {
			var Component = bs[name];
			if (!Component || typeof Component.getInstance !== 'function') {
				return;
			}

			// Bootstrap keys instances by element, so ask about every element that could hold one
			// rather than guessing from the configured selector, which may have changed.
			find(root, '[data-bs-toggle], [data-bs-original-title], [title]').forEach(function (el) {
				var instance = Component.getInstance(el);
				if (instance) {
					instance.dispose();
				}
			});
		});
	};

	/**
	 * Initialises tooltips and popovers inside `root`.
	 *
	 * getOrCreateInstance is deliberate: calling the constructor twice on the same element
	 * silently abandons the first instance.
	 *
	 * @param {Element|Document} root
	 * @param {string} tooltipSelector
	 * @param {string} popoverSelector
	 */
	Booster.initIn = function (root, tooltipSelector, popoverSelector) {
		var bs = components();
		if (!bs || !root) {
			return;
		}

		if (bs.Tooltip && tooltipSelector) {
			find(root, tooltipSelector).forEach(function (el) {
				bs.Tooltip.getOrCreateInstance(el);
			});
		}

		if (bs.Popover && popoverSelector) {
			find(root, popoverSelector).forEach(function (el) {
				bs.Popover.getOrCreateInstance(el);
			});
		}
	};

	/**
	 * Convenience wrapper: tear down, then rebuild, inside one container.
	 *
	 * @param {Element|Document} root
	 * @param {string} tooltipSelector
	 * @param {string} popoverSelector
	 */
	Booster.refreshIn = function (root, tooltipSelector, popoverSelector) {
		Booster.disposeIn(root);
		Booster.initIn(root, tooltipSelector, popoverSelector);
	};

	/**
	 * Resolves a widget container by id, falling back to the document.
	 *
	 * @param {string} id
	 * @returns {Element|Document}
	 */
	Booster.container = function (id) {
		return (id && document.getElementById(id)) || document;
	};
})(window, document);
