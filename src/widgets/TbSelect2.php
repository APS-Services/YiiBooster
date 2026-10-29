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
	 * @var string Select2 theme used when the options name none. 'bootstrap-5' is the bundled
	 * select2-bootstrap-5-theme; an application with its own Select2 styles (e.g. an admin template
	 * styling Select2's 'default' theme) sets it once, e.g. through the widget factory.
	 * @since 5.0.0
	 */
	public $theme = 'bootstrap-5';

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
		// Select2 4 binds to a <select> only: on the hidden input it rendered nothing started
		if (!$this->asDropDownList) {
			throw new CException('TbSelect2::$asDropDownList = false was removed in YiiBooster 5.0: Select2 4 cannot bind to a hidden input. '
				. 'Use the select (the default); for values typed in freely set options \'tags\' => true, with \'multiple\' => true for several. See UPGRADE-5.0.md.');
		}

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

		$this->registerClientScript($id, $name);
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
	public function registerClientScript($id, $name = null) {

		Booster::getBooster()->registerSelect2Package($this->options);

		$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';

		// Select2 4.x dropped the 3.x programmatic API. Setting a value is now a plain jQuery
		// .val() followed by a change event, and readonly/disabled are ordinary DOM properties -
		// .select2('val'), .select2('readonly') and .select2('enable') no longer exist.
		if (!empty($this->val)) {
			$data = is_array($this->val) ? CJSON::encode($this->val) : CJavaScript::encode($this->val);
			$defValue = ".val($data).trigger('change')";
		} else
			$defValue = '';

		// Select2 4.x has no readonly state, and the comment that used to sit here claiming
		// `disabled` was equivalent to 3.x's `readonly` was wrong in a way that loses data:
		// browsers omit disabled controls from the submitted payload, so a readonly field with an
		// existing value posted nothing and the model was overwritten with null on save. 3.x set
		// the `readonly` property, which is inert on a <select> and only blocked its own UI, so
		// the value always submitted.
		//
		// Readonly therefore disables the control for interaction but adds a hidden field
		// carrying the value, keeping it in the payload. Disabled stays genuinely disabled -
		// not submitting is the point of it.
		if ($this->readonly) {
			$defValue .= ".prop('disabled', true).trigger('change')";
			if ($name === null) {
				$name = $this->hasModel()
					? CHtml::activeName($this->model, $this->attribute)
					: $this->name;
			}
			// one hidden field per value: without [] PHP kept only the last one of a multiple select
			if ((!empty($this->htmlOptions['multiple']) || !empty($this->options['multiple'])) && substr($name, -2) !== '[]') {
				$name .= '[]';
			}
			$this->registerReadonlyValueField($id, $name);
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

	/**
	 * Mirrors a readonly control's value into a hidden field so it still posts.
	 *
	 * A disabled <select> is excluded from the submitted payload, which would silently blank the
	 * attribute on save. The hidden field is written from the live Select2 value on submit, so a
	 * multiple select posts its whole selection.
	 *
	 * @param string $id the select's DOM id.
	 * @param string $name the submitted field name.
	 * @since 5.0.0
	 */
	protected function registerReadonlyValueField($id, $name) {

		$nameJs = CJavaScript::encode($name);

		Yii::app()->getClientScript()->registerScript(
			__CLASS__ . '#readonly#' . $id,
			"(function () {"
			. " var el = document.getElementById(" . CJavaScript::encode($id) . ");"
			. " if (!el) { return; }"
			. " var form = el.form;"
			. " if (!form) { return; }"
			. " form.addEventListener('submit', function () {"
			. " var values = jQuery(el).val();"
			. " if (values === null) { values = []; }"
			. " if (!jQuery.isArray(values)) { values = [values]; }"
			. " jQuery(form).find('input.booster-select2-readonly[data-for=\"' + el.id + '\"]').remove();"
			. " jQuery.each(values, function (i, v) {"
			. " jQuery('<input type=\"hidden\" class=\"booster-select2-readonly\">')"
			. " .attr('name', " . $nameJs . ").attr('data-for', el.id).val(v).appendTo(form);"
			. " });"
			. " });"
			. " })();"
		);
	}

	private function setDefaultWidthIfEmpty() {
		if (empty($this->options['width'])) {
			$this->options['width'] = 'resolve';
		}

		// select2-bootstrap-5-theme only applies when Select2 is told to use it by name; without
		// this the control renders in Select2's own default skin next to Bootstrap 5 inputs.
		if (empty($this->options['theme'])) {
			$this->options['theme'] = $this->theme;
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
