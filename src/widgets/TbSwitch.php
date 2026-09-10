<?php
/**
 *## TbToggleButton class file
 *
 * @author: amr bedair <amr.bedair@gmail.com>
 */

/**
 *## Class TbToggleButton
 * @see <http://www.bootstrap-switch.org/>
 * @package booster.widgets.forms.buttons
 *
 */
class TbSwitch extends CInputWidget {

	/**
	 * @var TbActiveForm when created via TbActiveForm, this attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array the javascript events
	 *
	 * Example:
	 * <pre>
	 *  'events'=>array(
	 * 		'switchChange'=>'js:function(event, state) {
	 *			console.log(this); // DOM element
	 *			console.log(event); // jQuery event
	 *			console.log(state); // true | false
 	 *		}'
	 *	)
	 * </pre>
	 */
	public $events = array();

	/**
	 * @var array retained for backwards compatibility; ignored.
	 *
	 * @deprecated 5.0.0 the bootstrap-switch plugin is gone, so its options no longer apply.
	 * Bootstrap 5 renders a switch from CSS alone. Size and colour are utility classes on the
	 * wrapper or the input; see $wrapperHtmlOptions.
	 */
	public $options = array();

	/**
	 * @var array HTML attributes for the form-check wrapper around the input and label.
	 * @since 5.0.0
	 */
	public $wrapperHtmlOptions = array();

	/**
	 * @var string optional label rendered next to the switch.
	 * @since 5.0.0
	 */
	public $label;

	/**
	 * Widget's run function
	 */
	public function run() {

		list($name, $id) = $this->resolveNameID();

		// Bootstrap 5 renders a switch entirely in CSS: a checkbox with form-check-input inside a
		// form-check.form-switch wrapper. The bootstrap-switch plugin this used to drive was
		// archived without ever supporting Bootstrap 4, let alone 5.
		self::addCssClass($this->htmlOptions, 'form-check-input');
		if (!isset($this->htmlOptions['role'])) {
			$this->htmlOptions['role'] = 'switch';
		}

		$wrapperHtmlOptions = $this->wrapperHtmlOptions;
		self::addCssClass($wrapperHtmlOptions, 'form-check');
		self::addCssClass($wrapperHtmlOptions, 'form-switch');

		echo CHtml::openTag('div', $wrapperHtmlOptions);

		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->form->checkBox($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo CHtml::activeCheckBox($this->model, $this->attribute, $this->htmlOptions);
			}
		} else {
			echo CHtml::checkBox($name, $this->value, $this->htmlOptions);
		}

		if ($this->label !== null && $this->label !== '') {
			echo CHtml::label($this->label, $id, array('class' => 'form-check-label'));
		}

		echo CHtml::closeTag('div');

		$this->registerClientScript($id);
	}

	/**
	 * @param array $htmlOptions
	 * @param string $class
	 */
	protected static function addCssClass(&$htmlOptions, $class) {

		if (isset($htmlOptions['class']) && $htmlOptions['class'] !== '') {
			$htmlOptions['class'] .= ' ' . $class;
		} else {
			$htmlOptions['class'] = $class;
		}
	}

	/**
	 * Registers required css and js files
	 *
	 * @param integer $id the id of the toggle button
	 */
	protected function registerClientScript($id) {

		if (empty($this->events)) {
			return;
		}

		ob_start();
		echo "jQuery('input#$id')";
		foreach ($this->events as $event => $handler) {
			// Plain DOM events now. The plugin namespaced everything as `.bootstrapSwitch`, and
			// its custom `switchChange` event no longer exists - a Bootstrap 5 switch is an
			// ordinary checkbox, so listen for `change`.
			if (!$handler instanceof CJavaScriptExpression && strpos($handler, 'js:') === 0)
				$handler = new CJavaScriptExpression($handler);
			echo ".on('{$event}', " . $handler . ")";
		}

		Yii::app()->clientScript->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');
	}

}
