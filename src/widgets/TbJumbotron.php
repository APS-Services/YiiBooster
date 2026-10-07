<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbJumbotron - REMOVED in YiiBooster 5.0.
 *
 * The Jumbotron component was removed in Bootstrap 5 in favour of utility classes.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the exact line that instantiated the widget, instead of Yii's opaque
 * "include(TbJumbotron.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.decoration
 */
class TbJumbotron extends CWidget {

	public function init() {

		throw new CException('TbJumbotron was removed in YiiBooster 5.0 because Bootstrap 5 dropped that component. '
			. 'Use utility classes instead, e.g. <div class="p-5 bg-body-tertiary rounded-3">. See UPGRADE-5.0.md.');
	}
}
