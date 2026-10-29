<?php
/**
 *##  TbButtonColumn class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright  Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 * @since 0.9.8
 */

Yii::import('zii.widgets.grid.CButtonColumn');
Yii::import('booster.helpers.TbIcon');

/**
 *## Bootstrap button column widget.
 *
 * Used to set buttons to use Glyphicons instead of the defaults images.
 *
 * @package booster.widgets.grids.columns
 */
class TbButtonColumn extends CButtonColumn {
	
	/**
	 * @var string|null|false the view button icon; null: an eye in the configured icon family
	 */
	public $viewButtonIcon;

	/**
	 * @var string|null|false the update button icon; null: a pencil in the configured icon family
	 */
	public $updateButtonIcon;

	/**
	 * @var string|null|false the delete button icon; null: a trash can in the configured icon family
	 */
	public $deleteButtonIcon;

	/**
	 *### .initDefaultButtons()
	 *
	 * Initializes the default buttons (view, update and delete).
	 */
	protected function initDefaultButtons() {

		parent::initDefaultButtons();

		// the defaults in the family Booster::$iconPrefix chose (TbIcon::render would read them as
		// Glyphicons names, which only Bootstrap Icons translates)
		if ($this->viewButtonIcon === null) {
			$this->viewButtonIcon = TbIcon::familyIcon('eye');
		}
		if ($this->updateButtonIcon === null) {
			$this->updateButtonIcon = TbIcon::familyIcon('pencil');
		}
		if ($this->deleteButtonIcon === null) {
			$this->deleteButtonIcon = TbIcon::familyIcon('trash');
		}

		if ($this->viewButtonIcon !== false && !isset($this->buttons['view']['icon'])) {
			$this->buttons['view']['icon'] = $this->viewButtonIcon;
		}
		if ($this->updateButtonIcon !== false && !isset($this->buttons['update']['icon'])) {
			$this->buttons['update']['icon'] = $this->updateButtonIcon;
		}
		if ($this->deleteButtonIcon !== false && !isset($this->buttons['delete']['icon'])) {
			$this->buttons['delete']['icon'] = $this->deleteButtonIcon;
		}
	}

	/**
	 *### .renderButton()
	 *
	 * Renders a link button.
	 *
	 * @param string $id the ID of the button
	 * @param array $button the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data object associated with the row
	 */
	protected function renderButton($id, $button, $row, $data) {
		
		if (isset($button['visible']) && !$this->evaluateExpression(
			$button['visible'],
			array('row' => $row, 'data' => $data)
		)
		) {
			return;
		}

		$label = isset($button['label']) ? $button['label'] : $id;
		$url = isset($button['url']) ? $this->evaluateExpression($button['url'], array('data' => $data, 'row' => $row))
			: '#';
		$options = isset($button['options']) ? $button['options'] : array();

		if (!isset($options['title'])) {
			$options['title'] = $label;
		}

		if (!isset($options['data-bs-toggle'])) {
			$options['data-bs-toggle'] = 'tooltip';
		}

		if (isset($button['icon']) && $button['icon']) {
			echo CHtml::link(TbIcon::render($button['icon']), $url, $options);
		} else if (isset($button['imageUrl']) && is_string($button['imageUrl'])) {
			echo CHtml::link(CHtml::image($button['imageUrl'], $label), $url, $options);
		} else {
			echo CHtml::link($label, $url, $options);
		}
	}
}
