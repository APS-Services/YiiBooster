<?php
/**
 *## TbDateTimePicker widget class
 *
 * @author: Hrumpa
 * @copyright
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */
Yii::import('booster.widgets.TbBaseInputWidget');
/**
 * Bootstrap DateTimePicker widget
 * @see http://www.malot.fr/bootstrap-datetimepicker/
 *
 * @package booster.widgets.forms.inputs
 */
class TbDateTimePicker extends TbBaseInputWidget {
	
	/**
	 * @var TbActiveForm when created via TbActiveForm.
	 * This attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array the options for the Bootstrap JavaScript plugin.
	 */
	public $options = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 */
	public $events = array();

	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		parent::init();
		
		$this->htmlOptions['type'] = 'text';
		$this->htmlOptions['autocomplete'] = 'off';

		if (!isset($this->options['language'])) {
			$this->options['language'] = substr(Yii::app()->getLanguage(), 0, 2);
		}

	}

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 */
	public function run() {

		list($name, $id) = $this->resolveNameID();

		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			}

		} else {
			echo CHtml::textField($name, $this->value, $this->htmlOptions);
		}

		$this->registerClientScript();
		$this->registerLanguageScript();

		// The smalot datetimepicker this used to drive was archived in 2019 and never supported
		// Bootstrap 4 or 5. Dan Grossman's daterangepicker - already bundled here for
		// TbDateRangePicker - does single-date-with-time too, via singleDatePicker. Format and
		// 24-hour clock default to the Moment locale's, and an empty input stays empty until a
		// date is applied (Booster.dateTimePickerOptions, Booster.dateRangePicker).
		$options = array_merge(
			array('singleDatePicker' => true, 'timePicker' => true),
			(array) $this->options
		);

		Booster::getBooster()->registerPackage('booster');
		ob_start();
		echo "Booster.dateRangePicker('#{$id}', Booster.dateTimePickerOptions(" . CJavaScript::encode((object) $options) . "))";
		foreach ($this->events as $event => $handler) {
			echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";
		}

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');
	}

	/**
	 *### .registerClientScript()
	 *
	 * Registers required client script for bootstrap datepicker. It is not used through bootstrap->registerPlugin
	 * in order to attach events if any
	 */
	public function registerClientScript() {

		Booster::getBooster()->registerPackage('daterangepicker');
	}

	public function registerLanguageScript() {

		// daterangepicker has no locale files of its own: it takes its strings through the
		// `locale` option and formats dates with Moment. A `language` option carried over from the
		// old smalot plugin is mapped onto Moment's locale data for this picker only
		// (Booster.dateTimePickerOptions): moment.locale() changed the default format of every
		// TbDateRangePicker on the page. The locale data has to be present, which is what
		// Booster::$momentLocales controls - it defaults to true precisely so a widget-level
		// language works regardless of the application language.
		if (isset($this->options['language']) && $this->options['language'] !== 'en') {
			$this->options['momentLocale'] = $this->options['language'];
		}
		unset($this->options['language']);
	}
}
