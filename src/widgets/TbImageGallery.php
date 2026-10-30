<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbImageGallery - REMOVED in YiiBooster 5.0.
 *
 * The bundled blueimp Bootstrap Image Gallery reads $.fn.modal.Constructor.prototype and
 * $.fn.modal.defaults at script-execution time. Under Bootstrap 5 neither exists at that point
 * - the jQuery bridge is only installed on DOMContentLoaded, and `defaults` was renamed - so the
 * plugin throws on load and takes the rest of the page's JavaScript with it.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the line that used the widget, instead of Yii's opaque
 * "include(TbImageGallery.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.grouping
 */
class TbImageGallery extends CWidget {

	public function init() {

		throw new CException('TbImageGallery was removed in YiiBooster 5.0: the bundled blueimp gallery plugin patches '
			. 'Bootstrap 3 modal internals that no longer exist. Use a framework-neutral lightbox '
			. '(blueimp Gallery, GLightbox, PhotoSwipe). See UPGRADE-5.0.md.');
	}
}
