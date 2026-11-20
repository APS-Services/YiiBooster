<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbChosen - REMOVED in YiiBooster 5.0.
 *
 * Harvest's Chosen was archived in 2022 and its styling never targeted Bootstrap. The library
 * already ships TbSelect2, which covers the same ground.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the line that used the widget, instead of Yii's opaque
 * "include(TbChosen.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbChosen extends CInputWidget {

	public function init() {

		throw new CException('TbChosen was removed in YiiBooster 5.0: Chosen is archived upstream. '
			. 'Use TbSelect2 (booster.widgets.TbSelect2) instead. See UPGRADE-5.0.md.');
	}
}
