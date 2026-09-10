<?php
/**
 *## TbColorPicker class file
 *
 * @author: yiqing95 <yiqing_95@qq.com>
 * @author Antonio Ramirez <antonio@clevertech.biz>
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## TbColorPicker widget class
 *
 * ------------------------------------------------------------------------
 *   in yii  use this to register the necessary js and css files :
 *   <?php  $this->widget('booster.widgets.TbColorPicker', array( )); ?>
 *   and the rest usage you'd better refer the original plugin
 *
 * @see <http://www.eyecon.ro/bootstrap-colorpicker/>
 * ------------------------------------------------------------------------
 *
 * @package booster.widgets.forms.inputs
 */

Yii::import('booster.widgets.TbBaseInputWidget');

class TbColorPicker extends TbBaseInputWidget {

	/**
	 * @var TbActiveForm when created via TbActiveForm.
	 * this attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var string the color format - hex | rgb | rgba. Defaults to 'hex'
	 */
	public $format = 'hex';

	/**
	 * @var string[] the JavaScript event handlers.
	 * @see <http://www.eyecon.ro/bootstrap-colorpicker/> events section
	 *  show    This event fires immediately when the color picker is displayed.
	 *  hide    This event is fired immediately when the color picker is hidden.
	 *  changeColor    This event is fired when the color is changed.
	 *
	 * <pre>
	 *  'events'=>array(
	 *      'changeColor'=>'js:function(ev){
	 *          console.log(ev.color.toHex());
	 *      }',
	 *      'hide'=>'js:function(ev){
	 *        console.log("I am hidden!");
	 *   }')
	 * </pre>
	 */
	public $events = array();

	/**
	 *### .run()
	 *
	 * Widget's run function
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		$this->registerClientScript($id);

		$this->htmlOptions['id'] = $id;

		// Do we have a model?
		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			}
		} else {
			echo CHtml::textField($name, $this->value, $this->htmlOptions);
		}
	}

	/**
	 *### .registerClientScript()
	 *
	 * Registers required
	 *
	 * @param string $id
	 */
	public function registerClientScript($id) {

		Booster::getBooster()->registerPackage('coloris');

		$options = CJavaScript::encode(array_merge(
			array('el' => '#' . $id),
			$this->format ? array('format' => $this->format) : array()
		));

		// Coloris replaces bootstrap-colorpicker, archived in 2022. It binds by selector and has
		// no jQuery dependency, so the widget's `events` are attached as plain DOM listeners -
		// Coloris fires a `coloris:pick` event on the input.
		ob_start();
		echo "Coloris({$options});";
		foreach ($this->events as $event => $handler) {
			echo "\njQuery('#{$id}').on('{$event}', " . CJavaScript::encode($handler) . ");";
		}

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean());
	}
}
