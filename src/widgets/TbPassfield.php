<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbPassfield - REMOVED in YiiBooster 5.0.
 *
 * Pass*Field was archived upstream, and only its minified build was ever vendored here - so it
 * could not even be patched.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the line that used the widget, instead of Yii's opaque
 * "include(TbPassfield.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbPassfield extends CInputWidget {

	public function init() {

		throw new CException('TbPassfield was removed in YiiBooster 5.0: the Pass*Field plugin is archived upstream and '
			. 'only its minified build was vendored. Use a password input with your own strength '
			. 'meter (zxcvbn or similar). See UPGRADE-5.0.md.');
	}
}
