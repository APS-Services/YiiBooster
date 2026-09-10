<?php
/**
 *## TbPopoverColumn class file
 *
 * @author: amr bedair <amr.bedair@gmail.com>
 * @copyright Copyright &copy; Clevertech 2014-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

Yii::import('booster.widgets.TbDataColumn');

/**
 *## Class TbPopoverColumn
 *
 * The TbPopoverColumn works with TbExtendedGridView and allows you to create a column that will display a popover element
 * The popover is a special plugin that renders a dropdown on click, which contents can be dynamically updated.
 *
 * @see <http://getbootstrap.com/javascript/#popovers>
 * 
 * @package booster.widgets.grids.columns
 */
class TbPopoverColumn extends TbDataColumn {
	
	
	/**
	 * @var string $class the class name to use to display picker
	 */
	public $class = 'bootstrap-popover';
	
	/**
	 * @var array $pickerOptions the javascript options for the picker bootstrap plugin. The picker bootstrap plugin
	 * extends from the tooltip plugin.
	 *
	 * Note that picker has also a 'width' just in case we display AJAX'ed content.
	 *
	 * @see http://getbootstrap.com/javascript/#popovers
	 */
	public $options = array();

	public function init() {
		
		$this->registerClientScript();
	}
	
	/**
	 * Renders a data cell content, wrapping the value with the link that will activate the picker
	 *
	 * @param int $row
	 * @param mixed $data
	 */
	public function renderDataCellContent($row, $data) {
		
		$value = '';
		if ($this->value !== null) {
			$value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
		} else if ($this->name !== null) {
			$value = CHtml::value($data, $this->name);
		}
		
		$class = preg_replace('/\s+/', '.', $this->class);
		$value = !isset($value) ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
		$value = CHtml::link($value, '#', array('class' => $class, 'data-trigger' => 'manual'));
		
		echo $value;
	}
	
	protected function registerClientScript() {
		
		$gridId = $this->grid->id;
		$options = CJavaScript::encode($this->options);
		$class = $this->class;

		Booster::getBooster()->registerPackage('booster');

		// Bootstrap 5 has no jQuery plugin, so hide/toggle need per-element instances. The helper
		// also registers its delegated click listener only once: the Bootstrap 3 version re-ran
		// this whole script on every AJAX update, stacking up another $(document).on handler each
		// time.
		$_script = "Booster.popoverColumn('{$gridId}', '{$class}', {$options});";
		Yii::app()->clientScript->registerScript(__CLASS__ . '#' . $this->id, "
			$_script;
		", CClientScript::POS_READY);
		
		if(get_class($this->grid) === 'TbExtendedGridView') {
			$this->grid->componentsAfterAjaxUpdate[] = "$_script";
		}
	}
}
