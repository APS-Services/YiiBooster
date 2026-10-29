<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbHtml - REMOVED in YiiBooster 5.0.
 *
 * 4,338 lines of Bootstrap 2 markup with no callers inside the library; it existed only for
 * yii-auth compatibility (#443). Every call throws, naming the replacement, instead of the
 * "include(TbHtml.php): failed to open stream" fatal a deleted file would give.
 *
 * It deliberately does not extend CHtml: TbHtml::link() falling back to CHtml would render the
 * link quietly without the Bootstrap classes the caller expected.
 *
 * This stub is scheduled for deletion in 5.1.
 *
 * @package booster.helpers
 */
class TbHtml
{
	/**
	 * @param string $name
	 * @param array $arguments
	 * @throws CException
	 */
	public static function __callStatic($name, $arguments)
	{
		throw new CException("TbHtml::{$name}() - TbHtml was removed in YiiBooster 5.0: it rendered Bootstrap 2 markup. "
			. 'Use CHtml with Bootstrap 5 classes, or the widgets (TbButton, TbActiveForm, ...). See UPGRADE-5.0.md.');
	}
}
