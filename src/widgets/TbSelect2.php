<?php

/**
 * ##  TbSelect2 class file.
 *
 * @author Antonio Ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 * ## Select2 wrapper widget
 *
 * @see http://ivaynberg.github.io/select2/
 *
 * @package booster.widgets.forms.inputs
 */
class TbSelect2 extends CInputWidget {

	/**
	 * @var TbActiveForm when created via TbActiveForm.
	 * This attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array @param data for generating the list options (value=>display)
	 */
	public $data = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 */
	public $events = array();

	/**
	 * @var bool whether to display a dropdown select box or use it for tagging
	 */
	public $asDropDownList = true;

	/**
	 * @var string the default value.
	 */
	public $val;

	/**
	 * @var
	 */
	public $options;

	/**
	 * @var bool
	 * @since 2.1.0
	 */
	public $readonly = false;

	/**
	 * @var bool
	 * @since 2.1.0
	 */
	public $disabled = false;

	/**
	 * ### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		$this->normalizeData();

		$this->normalizeOptions();

		$this->addEmptyItemIfPlaceholderDefined();

		$this->setDefaultWidthIfEmpty();

		// disabled & readonly
		if (!empty($this->htmlOptions['readonly'])) {
			$this->readonly = true;
		}
		if (!empty($this->htmlOptions['disabled'])) {
			$this->disabled = true;
		}
	}

	/**
	 * ### .run()
	 *
	 * Runs the widget.
	 */
	public function run() {
		list($name, $id) = $this->resolveNameID();

		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->asDropDownList ?
					$this->form->dropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions) :
					$this->form->hiddenField($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo $this->asDropDownList ?
					CHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions) :
					CHtml::activeHiddenField($this->model, $this->attribute, $this->htmlOptions);
			}
		} else {
			echo $this->asDropDownList ?
				CHtml::dropDownList($name, $this->value, $this->data, $this->htmlOptions) :
				CHtml::hiddenField($name, $this->value, $this->htmlOptions);
		}

		$this->registerClientScript($id);
	}

	/**
	 * ### .registerClientScript()
	 *
	 * Registers required client script for bootstrap select2. It is not used through bootstrap->registerPlugin
	 * in order to attach events if any
	 *
	 * @param $id
	 *
	 * @throws CException
	 */
	public function registerClientScript($id) {

		Booster::getBooster()->registerPackage('select2');

		$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';

		// Select2 4.x dropped the 3.x programmatic API. Setting a value is now a plain jQuery
		// .val() followed by a change event, and readonly/disabled are ordinary DOM properties -
		// .select2('val'), .select2('readonly') and .select2('enable') no longer exist.
		if (!empty($this->val)) {
			$data = is_array($this->val) ? CJSON::encode($this->val) : CJavaScript::encode($this->val);
			$defValue = ".val($data).trigger('change')";
		} else
			$defValue = '';

		if ($this->readonly) {
			// Select2 has no readonly state of its own; a disabled control is the closest thing
			// that still submits nothing, which is what the 3.x option effectively did.
			$defValue .= ".prop('disabled', true).trigger('change')";
		} elseif ($this->disabled) {
			$defValue .= ".prop('disabled', true).trigger('change')";
		}

		ob_start();
		echo "jQuery('select#{$id}').select2({$options})";
		foreach ($this->events as $event => $handler) {
			echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";
		}
		echo $defValue;

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');
	}

	private function setDefaultWidthIfEmpty() {
		if (empty($this->options['width'])) {
			$this->options['width'] = 'resolve';
		}

		// select2-bootstrap-5-theme only applies when Select2 is told to use it by name; without
		// this the control renders in Select2's own default skin next to Bootstrap 5 inputs.
		if (empty($this->options['theme'])) {
			$this->options['theme'] = 'bootstrap-5';
		}
	}

	private function normalizeData() {
		if (!$this->data)
			$this->data = array();
	}

	private function addEmptyItemIfPlaceholderDefined() {
		if (!empty($this->htmlOptions['placeholder']))
			$this->options['placeholder'] = $this->htmlOptions['placeholder'];

		if (!empty($this->options['placeholder']) && empty($this->htmlOptions['multiple']))
			$this->prependDataWithEmptyItem();
	}

	private function normalizeOptions() {
		if (empty($this->options)) {
			$this->options = array();
		}
	}

	private function prependDataWithEmptyItem() {
		$this->data = array('' => '') + $this->data;
	}

}
