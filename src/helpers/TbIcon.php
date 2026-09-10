<?php
/**
 *## TbIcon class file.
 *
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 * @since 5.0.0
 */

/**
 *## Icon rendering for YiiBooster widgets.
 *
 * Before 5.0 eight widgets each carried their own copy of this logic, in two mutually inconsistent
 * flavours - some emitted `glyphicon glyphicon-x`, others the Bootstrap 2 `icon-x` - guarded by a
 * `strpos($icon, 'icon') === false && strpos($icon, 'fa') === false` test that misfires on any icon
 * name containing the letters "fa" or "icon". Glyphicons were dropped in Bootstrap 4, so all of it
 * had to change; centralising it here means each widget now has one call instead of a policy.
 *
 * Accepted input, all of which resolve to the same thing:
 *
 *     'trash'                        bare name (the common case)
 *     'glyphicon-trash'              Bootstrap 3 style
 *     'glyphicon glyphicon-trash'    Bootstrap 3 style, fully qualified
 *     'icon-trash'                   Bootstrap 2 style
 *     'bi bi-trash'                  already a Bootstrap Icons class
 *
 * Font Awesome - or any other family an application supplies fully qualified, e.g. `fa fa-trash`
 * or `fas fa-trash` - is passed through untouched, so applications already using it keep working.
 *
 * @package booster.helpers
 */
class TbIcon
{
	/**
	 * @var string CSS class prefix of the icon family to emit. Set from Booster::$iconPrefix.
	 * Defaults to Bootstrap Icons, the set maintained alongside Bootstrap itself.
	 */
	public static $family = 'bi';

	/**
	 * Bootstrap 2/3 icon names that Bootstrap Icons spells differently.
	 *
	 * Only names that actually changed are listed - anything absent is passed through unchanged,
	 * which covers the large number of names the two sets happen to share (`trash`, `search`,
	 * `calendar`, `lock`, `bell`, ...). Under YII_DEBUG an unmapped legacy name is logged, so a
	 * silently blank icon becomes a searchable warning.
	 *
	 * @return array
	 */
	public static function glyphiconMap()
	{
		return array(
			// status / actions
			'ok' => 'check-lg',
			'ok-sign' => 'check-circle',
			'ok-circle' => 'check-circle',
			'remove' => 'x-lg',
			'remove-sign' => 'x-circle',
			'remove-circle' => 'x-circle',
			'plus' => 'plus-lg',
			'plus-sign' => 'plus-circle',
			'minus' => 'dash-lg',
			'minus-sign' => 'dash-circle',
			'ban-circle' => 'slash-circle',
			'off' => 'power',
			'edit' => 'pencil-square',
			'refresh' => 'arrow-clockwise',
			'repeat' => 'arrow-repeat',
			'retweet' => 'arrow-left-right',
			'random' => 'shuffle',
			'export' => 'box-arrow-up-right',
			'import' => 'box-arrow-in-down',
			'new-window' => 'box-arrow-up-right',
			'log-in' => 'box-arrow-in-right',
			'log-out' => 'box-arrow-right',
			'screenshot' => 'bullseye',

			// glyphs whose noun changed
			'user' => 'person',
			'home' => 'house',
			'cog' => 'gear',
			'time' => 'clock',
			'picture' => 'image',
			'film' => 'film',
			'facetime-video' => 'camera-video',
			'comment' => 'chat',
			'signal' => 'bar-chart',
			'stats' => 'bar-chart',
			'dashboard' => 'speedometer2',
			'shopping-cart' => 'cart',
			'earphone' => 'telephone',
			'map-marker' => 'geo-alt',
			'road' => 'signpost',
			'plane' => 'airplane',
			'leaf' => 'tree',
			'music' => 'music-note',
			'qrcode' => 'qr-code',
			'barcode' => 'upc-scan',
			'certificate' => 'patch-check',
			'floppy-disk' => 'floppy',
			'floppy-save' => 'floppy',
			'link' => 'link-45deg',
			'globe' => 'globe',
			'briefcase' => 'briefcase',
			'th' => 'grid-3x3-gap',
			'th-large' => 'grid',
			'th-list' => 'list',
			'list' => 'list-ul',
			'list-alt' => 'card-list',
			'menu-hamburger' => 'list',
			'option-vertical' => 'three-dots-vertical',
			'option-horizontal' => 'three-dots',
			'filter' => 'funnel',
			'sort' => 'arrow-down-up',
			'transfer' => 'arrow-left-right',
			'resize-full' => 'arrows-fullscreen',
			'resize-small' => 'fullscreen-exit',
			'move' => 'arrows-move',
			'fullscreen' => 'arrows-fullscreen',

			// filled/outline pairs Bootstrap Icons splits differently
			'star' => 'star-fill',
			'star-empty' => 'star',
			'heart' => 'heart-fill',
			'heart-empty' => 'heart',
			'thumbs-up' => 'hand-thumbs-up',
			'thumbs-down' => 'hand-thumbs-down',
			'eye-open' => 'eye',
			'eye-close' => 'eye-slash',
			'volume-up' => 'volume-up',
			'volume-off' => 'volume-mute',
			'play' => 'play-fill',
			'pause' => 'pause-fill',
			'stop' => 'stop-fill',
			'forward' => 'fast-forward-fill',
			'backward' => 'rewind-fill',
			'step-forward' => 'skip-end-fill',
			'step-backward' => 'skip-start-fill',
			'record' => 'record-circle',

			// alerts
			'info-sign' => 'info-circle',
			'warning-sign' => 'exclamation-triangle',
			'exclamation-sign' => 'exclamation-circle',
			'question-sign' => 'question-circle',

			// files and folders
			'file' => 'file-earmark',
			'folder-open' => 'folder2-open',
			'folder-close' => 'folder',
			'save' => 'download',
			'open' => 'upload',
			'print' => 'printer',
			'paper-clip' => 'paperclip',
			'trash' => 'trash',

			// checkbox-ish
			'check' => 'check-square',
			'unchecked' => 'square',

			// text alignment
			'align-left' => 'text-left',
			'align-center' => 'text-center',
			'align-right' => 'text-right',
			'align-justify' => 'justify',
			'text-width' => 'text-paragraph',

			// Bootstrap 2 chevron/arrow spellings that survived unchanged are intentionally absent.
		);
	}

	/**
	 * Resolves any accepted icon spelling to a CSS class string.
	 *
	 * @param string $icon
	 * @return string e.g. "bi bi-trash", or '' when $icon is empty.
	 */
	public static function resolveCssClass($icon)
	{
		$icon = trim((string) $icon);
		if ($icon === '') {
			return '';
		}

		$legacyNames = array();
		$explicitNames = array();
		$foreignFamily = false;

		foreach (preg_split('/\s+/', $icon) as $token) {
			// Bootstrap 2 colour modifiers. No icon font has an equivalent; colour is CSS now.
			if ($token === 'glyphicon-white' || $token === 'icon-white') {
				continue;
			}

			// Bare family markers carry no name of their own.
			if ($token === 'glyphicon' || $token === 'icon' || $token === self::$family) {
				continue;
			}

			// Font Awesome and friends, fully qualified by the caller: hands off.
			if (preg_match('/^fa[srlbd]?$/', $token) || strpos($token, 'fa-') === 0) {
				$foreignFamily = true;
				continue;
			}

			if (strpos($token, self::$family . '-') === 0) {
				$explicitNames[] = substr($token, strlen(self::$family) + 1);
			} elseif (strpos($token, 'glyphicon-') === 0) {
				$legacyNames[] = substr($token, 10);
			} elseif (strpos($token, 'icon-') === 0) {
				$legacyNames[] = substr($token, 5);
			} else {
				$legacyNames[] = $token;
			}
		}

		if ($foreignFamily) {
			return $icon;
		}

		$names = $explicitNames;
		foreach ($legacyNames as $name) {
			$names[] = self::mapLegacyName($name);
		}

		if (empty($names)) {
			return '';
		}

		$classes = array(self::$family);
		foreach ($names as $name) {
			$classes[] = self::$family . '-' . $name;
		}

		return implode(' ', $classes);
	}

	/**
	 * Renders an icon element, or an empty string when there is no icon.
	 *
	 * @param string $icon
	 * @param array $htmlOptions additional attributes for the icon tag.
	 * @return string
	 */
	public static function render($icon, $htmlOptions = array())
	{
		$class = self::resolveCssClass($icon);
		if ($class === '') {
			return '';
		}

		if (isset($htmlOptions['class']) && $htmlOptions['class'] !== '') {
			$class .= ' ' . $htmlOptions['class'];
		}
		$htmlOptions['class'] = $class;

		// Decorative by default: the accessible name comes from the button or link label.
		if (!isset($htmlOptions['aria-hidden']) && !isset($htmlOptions['aria-label'])) {
			$htmlOptions['aria-hidden'] = 'true';
		}

		return CHtml::tag('i', $htmlOptions, '');
	}

	/**
	 * @param string $name a Bootstrap 2/3 icon name.
	 * @return string the Bootstrap Icons name.
	 */
	protected static function mapLegacyName($name)
	{
		$map = self::glyphiconMap();
		if (isset($map[$name])) {
			return $map[$name];
		}

		// Names the two sets share need no entry, so absence is not necessarily an error - but a
		// genuinely unknown name renders as nothing at all, which is worth surfacing while
		// developing rather than leaving as a blank space in the UI.
		if (defined('YII_DEBUG') && YII_DEBUG && class_exists('Yii', false)) {
			Yii::log(
				"TbIcon: icon name '{$name}' has no entry in the Glyphicons compatibility map. "
				. 'If it is not a valid Bootstrap Icons name it will render as blank. '
				. 'See https://icons.getbootstrap.com/ and src/helpers/TbIcon.php.',
				CLogger::LEVEL_WARNING,
				'booster.helpers.TbIcon'
			);
		}

		return $name;
	}
}
