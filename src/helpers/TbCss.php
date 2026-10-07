<?php
/**
 *## TbCss class file.
 *
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 * @since 5.0.0
 */

/**
 *## CSS class helpers for YiiBooster widgets.
 *
 * Appending a class to an htmlOptions array had grown five near-identical private copies across
 * TbWidget, TbActiveForm, TbBaseMenu, TbSwitch and TbTags - and they had already drifted: two
 * guarded with `empty($class)`, one with `$class === ''`, and two had no guard at all, so passing
 * an empty class left a stray space in the attribute. PHP 5.3 has no traits, so the shared
 * implementation lives here and the widgets' own `addCssClass()` methods delegate to it, which
 * keeps every existing call site working.
 *
 * @package booster.helpers
 */
class TbCss
{
	/**
	 * Appends a CSS class to an htmlOptions array, in place.
	 *
	 * @param array $htmlOptions
	 * @param string $class may be empty, in which case nothing happens.
	 */
	public static function add(&$htmlOptions, $class)
	{
		$class = trim((string) $class);
		if ($class === '') {
			return;
		}

		if (!isset($htmlOptions['class']) || trim($htmlOptions['class']) === '') {
			$htmlOptions['class'] = $class;
			return;
		}

		// Don't append a class that is already present - repeated init() calls and widgets that
		// layer defaults over caller options otherwise accumulate duplicates.
		$existing = preg_split('/\s+/', trim($htmlOptions['class']), -1, PREG_SPLIT_NO_EMPTY);
		foreach (preg_split('/\s+/', $class, -1, PREG_SPLIT_NO_EMPTY) as $candidate) {
			if (!in_array($candidate, $existing, true)) {
				$existing[] = $candidate;
			}
		}

		$htmlOptions['class'] = implode(' ', $existing);
	}
}
