<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbPickerColumn - renamed in YiiBooster 4.0, removed in 5.0.
 *
 * Renamed to TbJsonPickerColumn in YiiBooster 4.0.
 *
 * This stub exists for applications upgrading straight from YiiBooster 3.x, where this class
 * still existed. Without it Yii reports only "include(TbPickerColumn.php): failed to open stream".
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.grids.columns
 */
class TbPickerColumn extends CGridColumn {

	public function init() {

		throw new CException('TbPickerColumn was renamed to TbJsonPickerColumn in YiiBooster 4.0. '
			. 'Use booster.widgets.TbJsonPickerColumn. See UPGRADE-5.0.md.');
	}
}
