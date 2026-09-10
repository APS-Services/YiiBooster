<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbToggleButton - renamed in YiiBooster 4.0, removed in 5.0.
 *
 * Renamed to TbSwitch in YiiBooster 4.0. In 5.0 it renders Bootstrap 5's native switch and no
 * longer needs a plugin at all.
 *
 * This stub exists for applications upgrading straight from YiiBooster 3.x, where this class
 * still existed. Without it Yii reports only "include(TbToggleButton.php): failed to open stream".
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbToggleButton extends CInputWidget {

	public function init() {

		throw new CException('TbToggleButton was renamed to TbSwitch in YiiBooster 4.0. In 5.0 it renders Bootstrap 5\'s '
			. 'native switch (form-check form-switch), so the old plugin `options` no longer apply. '
			. 'Use booster.widgets.TbSwitch. See UPGRADE-5.0.md.');
	}
}
