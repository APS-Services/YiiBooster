<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbArray - REMOVED in YiiBooster 5.0.
 *
 * The array helper of TbHtml, removed with it. Every call throws, naming the replacement,
 * instead of the "include(TbArray.php): failed to open stream" fatal a deleted file would give.
 *
 * This stub is scheduled for deletion in 5.1.
 *
 * @package booster.helpers
 */
class TbArray
{
	/**
	 * @param string $name
	 * @param array $arguments
	 * @throws CException
	 */
	public static function __callStatic($name, $arguments)
	{
		throw new CException("TbArray::{$name}() - TbArray was removed in YiiBooster 5.0 together with TbHtml. "
			. 'Use CMap / plain PHP array functions. See UPGRADE-5.0.md.');
	}
}
