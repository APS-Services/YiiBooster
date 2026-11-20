<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbFileUpload - REMOVED in YiiBooster 5.0.
 *
 * Already broken before this release: it registered four asset files that do not exist in the
 * repository (fileupload/vendor/jquery.ui.widget.js, tmpl.min.js, jquery.fileupload-ip.js and
 * jquery.fileupload-fp.js), so it has not worked for years. Re-vendoring blueimp's
 * jQuery-File-Upload is a feature project rather than a migration task.
 *
 * This stub exists only so that applications upgrading from 4.x get a message naming the
 * replacement, at the line that used the widget, instead of Yii's opaque
 * "include(TbFileUpload.php): failed to open stream: No such file or directory" fatal.
 * It will be deleted in 5.1.
 *
 * @deprecated 5.0.0
 * @package booster.widgets.forms.inputs
 */
class TbFileUpload extends CInputWidget {

	public function init() {

		throw new CException('TbFileUpload was removed in YiiBooster 5.0. It had been broken for years - it registered '
			. 'four asset files that are not in the repository. Use a plain file input, or integrate '
			. 'blueimp jQuery-File-Upload directly. See UPGRADE-5.0.md.');
	}
}
