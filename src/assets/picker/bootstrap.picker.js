/**
 * Picker - a manually-triggered panel anchored to a link, used by TbJsonPickerColumn.
 *
 * Until 5.0 this was built by copying Bootstrap 3's tooltip prototype:
 *
 *     Picker.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, { ... });
 *
 * Bootstrap 5 registers no jQuery plugins, so `$.fn.tooltip.Constructor` is either undefined or
 * - once YiiBooster stopped restoring Bootstrap's plugins over jQuery UI's - jQuery UI's tooltip.
 * Either way the picker inherited from the wrong thing, and did so silently.
 *
 * It is now a thin wrapper over bootstrap.Popover, which already provides manual triggering,
 * placement, a custom template and title/content handling. The jQuery-facing API is unchanged:
 *
 *     $(el).picker(options)     initialise
 *     $(el).picker('toggle')    toggle
 *     $(el).picker('hide')      hide
 *     $(el).picker('destroy')   dispose
 *
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */
(function ($) {
	'use strict';

	if (!$) {
		return;
	}

	var DATA_KEY = 'booster.picker';

	/**
	 * Bootstrap fills .popover-header from the title and .popover-body from the content, so those
	 * class names have to be present. The picker-* classes are kept alongside them so the existing
	 * bootstrap.picker.css still applies.
	 */
	var TEMPLATE = '<div class="popover picker dropdown-menu" role="tooltip">'
		+ '<div class="popover-arrow"></div>'
		+ '<div class="popover-header picker-title"></div>'
		+ '<div class="popover-body picker-content"></div>'
		+ '</div>';

	function bootstrapPopover() {
		return (window.bootstrap && window.bootstrap.Popover) || null;
	}

	/**
	 * @param {Element} element
	 * @param {Object} options
	 * @returns {Object|null} the underlying Bootstrap popover instance.
	 */
	function create(element, options) {
		var Popover = bootstrapPopover();
		if (!Popover) {
			return null;
		}

		var settings = $.extend({}, $.fn.picker.defaults, options || {});
		var width = settings.width;
		delete settings.width;

		var instance = Popover.getOrCreateInstance(element, settings);

		if (width) {
			// Bootstrap caps popover width in CSS; the picker has always allowed overriding it.
			element.addEventListener('shown.bs.popover', function () {
				var tip = instance.tip;
				if (tip) {
					tip.style.width = typeof width === 'number' ? width + 'px' : width;
					tip.style.maxWidth = 'none';
				}
			});
		}

		return instance;
	}

	$.fn.picker = function (option) {
		return this.each(function () {
			var $this = $(this);
			var instance = $this.data(DATA_KEY);

			if (!instance) {
				instance = create(this, typeof option === 'object' ? option : {});
				if (!instance) {
					return;
				}
				$this.data(DATA_KEY, instance);
			}

			if (typeof option === 'string' && typeof instance[option] === 'function') {
				instance[option]();

				if (option === 'dispose' || option === 'destroy') {
					$this.removeData(DATA_KEY);
				}
			}
		});
	};

	// Bootstrap 5 renamed destroy() to dispose(); keep the old name working for callers.
	$.fn.picker.Constructor = null;

	$.fn.picker.defaults = {
		placement: 'bottom',
		trigger: 'manual',
		content: '',
		html: true,
		template: TEMPLATE
	};

	/**
	 * Clicking anywhere closes any open picker. TbJsonPickerColumn marks the open one with
	 * `pickeron` and calls preventDefault on its own handler, so this only sees clicks elsewhere.
	 */
	$(document).on('click', function () {
		$('a.pickeron').removeClass('pickeron').picker('hide');
	});

	/**
	 * Close open pickers when a request starts - a grid update is about to replace the rows the
	 * picker is anchored to. This used to be an $.ajaxPrefilter registered from inside
	 * setContent(), which added another prefilter every time a picker rendered.
	 */
	$(document).on('ajaxSend', function () {
		$('a.pickeron').removeClass('pickeron').picker('hide');
	});
})(window.jQuery);
