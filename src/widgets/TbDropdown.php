<?php
/**
 *## TbDropdown class file.
 *
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2012-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

Yii::import('booster.widgets.TbBaseMenu');
Yii::import('booster.helpers.TbIcon');

/**
 *## Bootstrap dropdown menu.
 *
 * @see http://twitter.github.com/bootstrap/javascript.html#dropdowns
 *
 * @package booster.widgets.navigation
 */
class TbDropdown extends TbBaseMenu {
	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		parent::init();

		if (isset($this->htmlOptions['class'])) {
			$this->htmlOptions['class'] .= ' dropdown-menu';
		} else {
			$this->htmlOptions['class'] = 'dropdown-menu';
		}
	}

	/**
	 *### .renderMenuItem()
	 *
	 * Renders the content of a menu item.
	 * Note that the container and the sub-menus are not rendered here.
	 *
	 * @param array $item the menu item to be rendered. Please see {@link items} on what data might be in the item.
	 *
	 * @return string the rendered item
	 */
	protected function renderMenuItem($item) {
		
		if (isset($item['icon'])) {
			$icon = TbIcon::render($item['icon']);
			if ($icon !== '') {
				$item['label'] = $icon . ' ' . $item['label'];
			}
		}

		if (!isset($item['linkOptions'])) {
			$item['linkOptions'] = array();
		}

		// TODO: Bootstrap 3 does not support submenu 
		// http://stackoverflow.com/questions/18023493/bootstrap-3-dropdown-sub-menu-missing
		// we may use this to support it 
		/* if (isset($item['items']) && !empty($item['items']) && empty($item['url'])) {
			$item['url'] = '#';
		} */

		// Bootstrap 3's dropdown markup put tabindex="-1" on every item; Bootstrap 5 does not,
		// and keeping it would take the whole menu out of the keyboard tab order.

		// This class overrides renderMenuItem() wholesale, so the anchor styling the parent
		// applies has to be repeated here.
		self::addCssClass($item['linkOptions'], $this->getLinkCssClass());

		if (!empty($item['active']) && $this->activeCssClass != '') {
			self::addCssClass($item['linkOptions'], $this->activeCssClass);
			$item['linkOptions']['aria-current'] = 'true';
		}

		if (isset($item['disabled'])) {
			self::addCssClass($item['linkOptions'], 'disabled');
			$item['linkOptions']['tabindex'] = '-1';
			$item['linkOptions']['aria-disabled'] = 'true';
		}

		if (isset($item['url'])) {
			return CHtml::link($item['label'], $item['url'], $item['linkOptions']);
		} else {
			return CHtml::link($item['label'], '#', $item['linkOptions']);
		}
	}

	/**
	 *### .getDividerCssClass()
	 *
	 * Returns the divider CSS class.
	 * @return string the class name
	 */
	public function getDividerCssClass()
	{
		return 'dropdown-divider';
	}

	/**
	 * Dropdown items take no class of their own; Bootstrap styles the anchor.
	 *
	 * @return string
	 * @since 5.0.0
	 */
	public function getItemCssClass()
	{
		return '';
	}

	/**
	 * @return string
	 * @since 5.0.0
	 */
	public function getLinkCssClass()
	{
		return 'dropdown-item';
	}

	/**
	 *### .getDropdownCssClass()
	 *
	 * Returns the dropdown css class.
	 * @return string the class name
	 */
	public function getDropdownCssClass()
	{
		return 'dropdown-submenu';
	}

	/**
	 *### .isVertical()
	 *
	 * Returns whether this is a vertical menu.
	 * @return boolean the result
	 */
	public function isVertical()
	{
		return true;
	}
}
