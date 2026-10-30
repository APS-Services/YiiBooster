<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbBox - renamed in YiiBooster 4.0, removed in 5.0.
 *
 * Renamed to TbPanel in YiiBooster 4.0 when Bootstrap 2's box gave way to Bootstrap 3's panel.
 * In 5.0 TbPanel renders a Bootstrap 5 card.
 *
 * This stub exists for applications upgrading straight from YiiBooster 3.x, where this class
 * still existed. Without it Yii reports only "include(TbBox.php): failed to open stream".
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.grouping
 */
class TbBox extends CWidget {

	public function init() {

		throw new CException('TbBox was renamed to TbPanel in YiiBooster 4.0 and now renders a Bootstrap 5 card. '
			. 'Use booster.widgets.TbPanel. Note its `headerIcon`, `title` and `content` options '
			. 'carry over; `headerButtons` replaces the old header actions. See UPGRADE-5.0.md.');
	}
}
