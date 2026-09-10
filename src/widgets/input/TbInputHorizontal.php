<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbInputHorizontal - REMOVED in YiiBooster 5.0.
 *
 * Part of the Bootstrap 2-era input hierarchy, whose wrapper and add-on classes were dropped
 * back in Bootstrap 3, so this emitted dead markup. It was also unreachable: TbForm sets
 * $inputElementClass to TbFormInputElement, which maps every input type straight onto a
 * TbActiveForm *Group() method, so the CForm path never went through this class.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the exact line that instantiated the widget, instead of Yii's opaque
 * "include(TbInputHorizontal.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbInputHorizontal extends CInputWidget {

	public function init() {

		throw new CException('TbInputHorizontal was removed in YiiBooster 5.0. It emitted Bootstrap 2 markup and was not used by '
			. 'the CForm path (TbForm -> TbFormInputElement -> TbActiveForm::*Group()). '
			. 'Use TbActiveForm and its *Group() methods instead. See UPGRADE-5.0.md.');
	}
}
