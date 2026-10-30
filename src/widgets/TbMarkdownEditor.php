<?php
/**
 *## TbMarkdownEditor class file
 *
 * @author: amr bedair <amr.bedair@gmail.com>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * 
 */

/**
 *## Class TbMarkdownEditorJS
 *
 * @see <http://toopay.github.io/bootstrap-markdown/>
 *
 * @package booster.widgets.forms.inputs.wysiwyg
 * 
 * TODO: test [add] support of Events and Editor Panel
 * 
 */
class TbMarkdownEditor extends CInputWidget {
	
	/**
	 * @see <http://toopay.github.io/bootstrap-markdown/>
	 * @var array Editor's Options
	 */
	public $options = array();
	
	/**
	 * Editor width
	 */
	public $width = '100%';

	/**
	 * Editor height
	 */
	public $height = '400px';

	/**
	 * Display editor
	 */
	public function run() {

		list($name, $id) = $this->resolveNameID();

		$this->htmlOptions['id'] = $id;
		
		$this->registerClientScript();

		if (!array_key_exists('style', $this->htmlOptions)) {
			$this->htmlOptions['style'] = "width:{$this->width};height:{$this->height};";
		}
		
		if ($this->hasModel()) { // Do we have a model?
			echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
		} else {
			echo CHtml::textArea($name, $this->value, $this->htmlOptions);
		}
	}

	/**
	 * Register required script files
	 */
	public function registerClientScript() {

		Booster::getBooster()->registerPackage('easymde');

		$id = $this->htmlOptions['id'];
		$options = CJSON::encode($this->options);

		// EasyMDE replaces bootstrap-markdown, which was abandoned and Bootstrap 3-only. It takes
		// the textarea as `element` rather than being applied to a jQuery selection.
		// forceSync (unless the options say otherwise): EasyMDE writes back to the textarea only on
		// the form's submit event, so a form sent by AJAX (CHtml::ajaxSubmitButton, serialize())
		// posted the original text.
		Yii::app()->clientScript->registerScript(
			__CLASS__ . '#' . $id,
			"(function () { var o = {$options}; o.element = document.getElementById('{$id}');"
			. " if (o.forceSync === undefined) { o.forceSync = true; }"
			. " new EasyMDE(o); })();",
			CClientScript::POS_END
		);
	}
}
