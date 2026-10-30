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
			// rather than guessing from the configured selector, which may have changed. The links
			// of TbPopoverColumn and TbJsonPickerColumn get their options from script, so they carry
			// data-booster-popover instead of any data-bs-* attribute.
			find(root, '[data-bs-toggle], [data-bs-original-title], [title], [data-booster-popover]').forEach(function (el) {
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
	 * A grid is about to be updated (its beforeAjaxUpdate): disposes the tooltips and popovers of
	 * the rows that will be replaced. The grid calls afterAjaxUpdate only on success, so a failed
	 * request (a delete refused by the database, a timeout) would leave the old rows without them:
	 * the request's error handler builds them again.
	 *
	 * @param {string} id the grid's id
	 * @param {Object} [options] the $.ajax options of the update, as the grid passes them
	 * @param {string} tooltipSelector
	 * @param {string} popoverSelector
	 */
	Booster.beforeGridUpdate = function (id, options, tooltipSelector, popoverSelector) {
		var root = Booster.container(id);
		Booster.disposeIn(root);

		if (options) {
			var error = options.error;
			options.error = function () {
				Booster.initIn(root, tooltipSelector, popoverSelector);
				if (typeof error === 'function') {
					return error.apply(this, arguments);
				}
			};
		}
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

	/**
	 * daterangepicker on the matched elements (TbDateRangePicker, TbDateTimePicker).
	 *
	 * Unless the options set autoUpdateInput, an input is written only when a date is applied:
	 * daterangepicker's own autoUpdateInput writes today into an empty field as soon as it loads, so
	 * a form saved a date - or a filter filtered by one - that nobody had chosen. An existing value
	 * is still read as the selected date (daterangepicker does that from the input itself).
	 *
	 * @param {string} selector
	 * @param {Object} options daterangepicker options
	 * @param {Function} [callback]
	 * @return {jQuery}
	 */
	Booster.dateRangePicker = function (selector, options, callback) {
		var $ = window.jQuery;
		var writeOnApply = options.autoUpdateInput === undefined;
		if (writeOnApply) {
			options.autoUpdateInput = false;
		}

		return $(selector).each(function () {
			var $element = $(this);
			$element.daterangepicker(options, callback);
			if (!writeOnApply || !$element.is('input')) {
				return;
			}
			$element.on('apply.daterangepicker', function (event, picker) {
				var value = picker.startDate.format(picker.locale.format);
				if (!picker.singleDatePicker) {
					value += picker.locale.separator + picker.endDate.format(picker.locale.format);
				}
				$element.val(value).trigger('change');
			});
		});
	};

	/**
	 * Defaults of a date with time (TbDateTimePicker) from a Moment locale, under the given
	 * options: the format is the locale's date plus time (daterangepicker's own default is the date
	 * only, so the time was lost), and the clock is 24-hour unless the locale uses AM/PM.
	 *
	 * `options.momentLocale` (the widget's `language`) names the locale for this picker alone, with
	 * its day and month names and first weekday; without it the current Moment locale applies.
	 * Setting it globally (moment.locale) changed every other picker on the page.
	 *
	 * @param {Object} options daterangepicker options
	 * @return {Object}
	 */
	Booster.dateTimePickerOptions = function (options) {
		options = window.jQuery.extend(true, {}, options);
		var language = options.momentLocale;
		delete options.momentLocale;

		var data = window.moment ? window.moment.localeData(language) : null;
		if (!data) {
			return options;
		}
		var time = data.longDateFormat('LT');
		var locale = {format: data.longDateFormat('L') + ' ' + time};
		if (language) {
			locale.daysOfWeek = data.weekdaysMin();
			locale.monthNames = data.monthsShort();
			locale.firstDay = data.firstDayOfWeek();
		}

		return window.jQuery.extend(true, {
			timePicker24Hour: !/a/i.test(time),
			locale: locale
		}, options);
	};

	/**
	 * Mirrors Yii's validation result onto the control, for Bootstrap 5.
	 *
	 * Yii's jquery.yiiactiveform.js toggles its errorCssClass on the *container* element, which is
	 * how Bootstrap 3's `has-error` worked. Bootstrap 5 dropped that entirely: the invalid state
	 * lives on the control as `.is-invalid`, and `.invalid-feedback` is only revealed next to one.
	 * So rather than fight Yii's contract, we let it keep flagging the container and translate
	 * that into the class Bootstrap actually reads.
	 *
	 * Only the invalid state is applied. Marking every field green the moment it validates is
	 * noisier than most forms want, and `is-valid` can always be added by an application's own
	 * afterValidateAttribute. Called for every validated attribute by hookValidationState.
	 *
	 * @param {Object} attribute Yii's attribute descriptor; carries inputID.
	 * @param {boolean} hasError
	 */
	Booster.markValidationState = function (attribute, hasError) {
		if (!attribute || !attribute.inputID) {
			return;
		}

		var el = document.getElementById(attribute.inputID);
		if (!el) {
			return;
		}

		// checkbox and radio lists name their container (a span) as input; the state belongs on
		// each of its controls
		var controls = /^(INPUT|SELECT|TEXTAREA)$/.test(el.tagName)
			? [el]
			: Array.prototype.slice.call(el.querySelectorAll('input:not([type=hidden]), select, textarea'));

		controls.forEach(function (control) {
			if (hasError) {
				control.classList.add('is-invalid');
			} else {
				control.classList.remove('is-invalid');
			}
		});
	};

	/**
	 * Mirrors every validation result onto its control, by wrapping Yii's single update point.
	 *
	 * jquery.yiiactiveform.js runs each validated attribute through `updateInput` - on change,
	 * while typing and for the whole form on submit (where afterValidateAttribute is never
	 * called). Wrapping it once covers all three and leaves the application's own callbacks alone.
	 * Safe to call repeatedly (every TbActiveForm registers it, also in AJAX-loaded content).
	 *
	 * @param {jQuery} $ the jQuery that jquery.yiiactiveform.js extended
	 */
	Booster.hookValidationState = function ($) {
		var yiiactiveform = $ && $.fn ? $.fn.yiiactiveform : null;
		if (!yiiactiveform || !yiiactiveform.updateInput || yiiactiveform.updateInput.boosterHooked) {
			return;
		}

		var updateInput = yiiactiveform.updateInput;
		yiiactiveform.updateInput = function (attribute) {
			var hasError = updateInput.apply(this, arguments);
			Booster.markValidationState(attribute, hasError);
			return hasError;
		};
		yiiactiveform.updateInput.boosterHooked = true;

		// Yii clears its messages on a form reset without going through updateInput, so the
		// controls would stay marked invalid
		$(document).on('reset.booster', 'form', function () {
			Array.prototype.forEach.call(this.querySelectorAll('.is-invalid'), function (control) {
				control.classList.remove('is-invalid');
			});
		});
	};

	/**
	 * Creates - or returns the existing - Bootstrap component instance for an element id.
	 *
	 * Replaces the Bootstrap 3 jQuery plugin calls (`jQuery('#id').modal(opts)` and friends),
	 * which Bootstrap 5 removed in favour of ES6 classes.
	 *
	 * @param {string} name component name, e.g. 'Modal'
	 * @param {string} id element id
	 * @param {Object} [options]
	 * @returns {Object|null}
	 */
	Booster.component = function (name, id, options) {
		var bs = components();
		var el = document.getElementById(id);

		if (!bs || !bs[name] || !el) {
			return null;
		}

		return bs[name].getOrCreateInstance(el, options || {});
	};

	/**
	 * Bootstrap 3's `$el.modal(options)` both constructed the modal and, unless `show: false`
	 * was passed, opened it. Bootstrap 5 removed the `show` option entirely: constructing never
	 * opens, and `show()` is an explicit call. So the widget's autoOpen flag has to drive it
	 * here rather than being smuggled through the options array.
	 *
	 * @param {string} id
	 * @param {Object} options
	 * @param {boolean} autoOpen
	 * @returns {Object|null}
	 */
	Booster.modal = function (id, options, autoOpen) {
		var instance = Booster.component('Modal', id, options);

		if (instance && autoOpen) {
			instance.show();
		}

		return instance;
	};

	/**
	 * Bootstrap 3: `$el.tab('show')`.
	 *
	 * @param {string} id
	 * @returns {Object|null}
	 */
	Booster.showTab = function (id) {
		var instance = Booster.component('Tab', id);

		if (instance) {
			instance.show();
		}

		return instance;
	};

	/**
	 * Bootstrap 3 picked scrollspy up from a data attribute through its data-api, so the widget
	 * could simply set the attribute after load. Bootstrap 5 only reads it during its own
	 * initialisation, so an attribute written afterwards is never noticed and the component has
	 * to be constructed explicitly.
	 *
	 * @param {string} selector
	 * @param {Object} [options]
	 */
	Booster.scrollSpy = function (selector, options) {
		var bs = components();
		if (!bs || !bs.ScrollSpy) {
			return;
		}

		toArray(document.querySelectorAll(selector)).forEach(function (el) {
			bs.ScrollSpy.getOrCreateInstance(el, options || {});
		});
	};

	/**
	 * Manual-trigger popovers on a grid column: clicking one closes the others.
	 *
	 * The delegated listener is registered once per grid+class. The Bootstrap 3 version re-ran
	 * its whole init script after every AJAX update, including `$(document).on('click', ...)`,
	 * so handlers accumulated with each refresh.
	 *
	 * @param {string} gridId
	 * @param {string} linkClass space-free CSS class identifying the column's links
	 * @param {Object} [options]
	 */
	Booster.popoverColumn = function (gridId, linkClass, options) {
		var bs = components();
		if (!bs || !bs.Popover) {
			return;
		}

		// the column toggles its popovers itself (below): never by Bootstrap's own click trigger
		var settings = {};
		for (var key in (options || {})) {
			settings[key] = options[key];
		}
		settings.trigger = 'manual';

		var selector = '#' + gridId + ' a.' + linkClass;

		toArray(document.querySelectorAll(selector)).forEach(function (el) {
			bs.Popover.getOrCreateInstance(el, settings);
		});

		Booster._popoverColumns = Booster._popoverColumns || {};
		if (Booster._popoverColumns[selector]) {
			return;
		}
		Booster._popoverColumns[selector] = true;

		document.addEventListener('click', function (event) {
			var link = event.target.closest ? event.target.closest('a.' + linkClass) : null;
			var grid = document.getElementById(gridId);

			if (!link || !grid || !grid.contains(link)) {
				return;
			}

			event.preventDefault();

			toArray(document.querySelectorAll(selector)).forEach(function (el) {
				if (el !== link) {
					var other = bs.Popover.getInstance(el);
					if (other) {
						other.hide();
					}
				}
			});

			bs.Popover.getOrCreateInstance(link, settings).toggle();
		});
	};

	/**
	 * Installs Bootstrap's jQuery plugins ($.fn.modal, $.fn.popover, ...) now instead of at
	 * DOMContentLoaded.
	 *
	 * Bootstrap 5 defines them in its own DOMContentLoaded listener. jQuery runs every queued ready
	 * handler from *its* listener, which comes first whenever something queued a ready handler before
	 * the bundle loaded (jQuery UI does on load). Inline `jQuery(function () { $(el).modal(); })`
	 * code - which is how Yii emits CClientScript::POS_READY scripts - then finds no $.fn.modal.
	 * This file loads right after the bundle (the `booster` package depends on `bootstrap.js`), so
	 * running here closes that gap. Bootstrap's own listener later installs the same functions again.
	 *
	 * Like Bootstrap, an existing plugin of the same name is replaced and kept for noConflict() -
	 * jQuery UI's tooltip and button would otherwise stay in place until DOMContentLoaded, so the same
	 * ready handlers would get jQuery UI widgets. Honours Bootstrap's opt-out, `data-bs-no-jquery` on
	 * the body.
	 */
	Booster.installJqueryPlugins = function () {
		var $ = window.jQuery;
		var bs = components();
		if (!$ || !bs || (document.body && document.body.hasAttribute('data-bs-no-jquery'))) {
			return;
		}

		['Alert', 'Button', 'Carousel', 'Collapse', 'Dropdown', 'Modal', 'Offcanvas', 'Popover',
			'ScrollSpy', 'Tab', 'Toast', 'Tooltip'].forEach(function (name) {
			var Component = bs[name];
			var plugin = Component && Component.NAME;
			if (!plugin || $.fn[plugin] === Component.jQueryInterface) {
				return;
			}
			var previous = $.fn[plugin];
			$.fn[plugin] = Component.jQueryInterface;
			$.fn[plugin].Constructor = Component;
			$.fn[plugin].noConflict = function () {
				$.fn[plugin] = previous;
				return Component.jQueryInterface;
			};
		});
	};

	Booster.installJqueryPlugins();
})(window, document);
