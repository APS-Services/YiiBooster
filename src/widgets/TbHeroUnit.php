<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbHeroUnit - REMOVED in YiiBooster 5.0.
 *
 * Emitted a Bootstrap 2 class that has not existed since Bootstrap 3, so this widget has been
 * rendering unstyled markup for years. Bootstrap 5 has no equivalent component.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the exact line that instantiated the widget, instead of Yii's opaque
 * "include(TbHeroUnit.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.decoration
 */
class TbHeroUnit extends CWidget {

	public function init() {

		throw new CException('TbHeroUnit was removed in YiiBooster 5.0. Bootstrap dropped that component after 2.x. '
			. 'Use a plain container with utility classes, e.g. <div class="p-5 bg-body-tertiary rounded-3">, '
			. 'or TbPanel. See UPGRADE-5.0.md.');
	}
}
