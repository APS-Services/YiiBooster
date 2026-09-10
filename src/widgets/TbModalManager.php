<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbModalManager - REMOVED in YiiBooster 5.0.
 *
 * The bundled modal manager is a 2012 plugin written for Bootstrap 2 that drove stacking by
 * passing a `manager` option into $.modal() and building backdrops by hand. Bootstrap 5 stacks
 * modals natively, so the widget has nothing left to do.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the line that used the widget, instead of Yii's opaque
 * "include(TbModalManager.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.modals
 */
class TbModalManager extends CWidget {

	public function init() {

		throw new CException('TbModalManager was removed in YiiBooster 5.0: Bootstrap 5 stacks modals natively, so the '
			. 'bundled Bootstrap 2 modal manager is redundant. Open modals normally with TbModal. '
			. 'See UPGRADE-5.0.md.');
	}
}
