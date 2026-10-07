<?php
/**
 *## TbDetailView class file.
 *
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

Yii::import('zii.widgets.CDetailView');

/**
 *## Bootstrap Zii detail view.
 *
 * @package booster.widgets.grouping
 */
class TbDetailView extends CDetailView
{
	// Table types.
	const TYPE_STRIPED = 'striped';
	const TYPE_BORDERED = 'bordered';
	const TYPE_CONDENSED = 'condensed';
	const TYPE_SMALL = 'sm';

	/**
	 * @var string|array the table type.
	 * Valid values are 'striped', 'bordered', 'sm' and/or the legacy 'condensed', which
	 * Bootstrap 4 renamed to 'sm' and which is still accepted as an alias for it.
	 */
	public $type = array(self::TYPE_STRIPED, self::TYPE_CONDENSED);

	/**
	 * @var string the URL of the CSS file used by this detail view.
	 * Defaults to false, meaning that no CSS will be included.
	 */
	public $cssFile = false;

	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	/**
	 * Maps an accepted table type name onto its Bootstrap 5 class suffix.
	 *
	 * Bootstrap 4 renamed `table-condensed` to `table-sm`. The name `condensed` stays valid so
	 * existing configuration keeps working; it simply renders as `table-sm` now.
	 *
	 * @param string $type
	 * @return string
	 * @since 5.0.0
	 */
	protected function tableTypeCssClass($type)
	{
		return 'table-' . ($type === self::TYPE_CONDENSED ? self::TYPE_SMALL : $type);
	}

	public function init()
	{
		parent::init();

		$classes = array('table');

		if (isset($this->type)) {
			if (is_string($this->type)) {
				$this->type = explode(' ', $this->type);
			}

			$validTypes = array(self::TYPE_STRIPED, self::TYPE_BORDERED, self::TYPE_CONDENSED, self::TYPE_SMALL);

			if (!empty($this->type)) {
				foreach ($this->type as $type) {
					if (in_array($type, $validTypes)) {
						$classes[] = $this->tableTypeCssClass($type);
					}
				}
			}
		}

		if (!empty($classes)) {
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class'])) {
				$this->htmlOptions['class'] .= ' ' . $classes;
			} else {
				$this->htmlOptions['class'] = $classes;
			}
		}
	}
}
