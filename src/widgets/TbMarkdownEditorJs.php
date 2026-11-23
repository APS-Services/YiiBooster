<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbMarkdownEditorJs - REMOVED in YiiBooster 5.0.
 *
 * YiiBooster shipped two markdown editors on two different dead libraries: this one on PageDown
 * (unversioned, long unmaintained) and TbMarkdownEditor on bootstrap-markdown (last touched 2022,
 * Bootstrap 3 only). 5.0 consolidates onto a single maintained editor - EasyMDE - behind
 * TbMarkdownEditor.
 *
 * This stub exists only so that applications upgrading get a message naming the replacement, at
 * the line that used the widget, instead of Yii's opaque
 * "include(TbMarkdownEditorJs.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbMarkdownEditorJs extends CInputWidget {

	public function init() {

		throw new CException(
			'TbMarkdownEditorJs was removed in YiiBooster 5.0. It was built on PageDown, which is '
			. 'unmaintained, and YiiBooster no longer ships two markdown editors. Use '
			. 'booster.widgets.TbMarkdownEditor, which now uses EasyMDE. See UPGRADE-5.0.md.'
		);
	}
}
