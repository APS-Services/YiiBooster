<?php
/**
 *## TbHtml5Editor class file
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 *## TbHtml5Editor widget
 *
 * Implements the bootstrap-wysihtml5 editor
 * @see https://github.com/jhollingworth/bootstrap-wysihtml5
 *
* @package booster.widgets.forms.inputs.wysiwyg
 */
class TbHtml5Editor extends CInputWidget {
	
	/**
	 * Editor language
	 * Supports: de-DE, es-ES, fr-FR, pt-BR, sv-SE, it-IT
	 */
	public $lang = 'en';

	/**
	 * Html options that will be assigned to the text area
	 */
	public $htmlOptions = array();

	/**
	 * Editor options that will be passed to the editor
	 */
	public $editorOptions = array();

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

		$this->registerClientScript($id);

		$this->htmlOptions['id'] = $id;

		if (!array_key_exists('style', $this->htmlOptions)) {
			$this->htmlOptions['style'] = "width:{$this->width};height:{$this->height};";
		}
		// Do we have a model?
		if ($this->hasModel()) {
			echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
		} else {
			echo CHtml::textArea($name, $this->value, $this->htmlOptions);
		}
	}

	/**
	 * Register required script files
	 *
	 * @param string $id
	 */
	public function registerClientScript($id) {

		Booster::getBooster()->registerPackage('quill');

		$options = CJSON::encode($this->editorOptions);

		// Quill replaces bootstrap3-wysihtml5, whose upstream died in 2020. Quill edits a div
		// rather than a textarea, so it is mounted next to the field and writes its HTML back on
		// every change - which keeps the original input as the thing that actually submits, and
		// means no server-side change is needed.
		Yii::app()->getClientScript()->registerScript(
			__CLASS__ . '#' . $id,
			"(function () {"
			. " var input = document.getElementById('{$id}');"
			. " if (!input) { return; }"
			. " var host = document.createElement('div');"
			. " host.className = 'booster-quill';"
			. " host.innerHTML = input.value;"
			. " input.parentNode.insertBefore(host, input.nextSibling);"
			. " input.style.display = 'none';"
			. " var o = {$options};"
			. " if (!o.theme) { o.theme = 'snow'; }"
			. " var editor = new Quill(host, o);"
			. " editor.on('text-change', function () {"
			. " input.value = editor.root.innerHTML;"
			. " });"
			. " })();"
		);
	}

	private function insertDefaultStylesheetIfColorsEnabled()
	{
		if (empty($this->editorOptions['color'])) {
			return;
		}

		$defaultStyleSheetUrl = Booster::getBooster()->getAssetsUrl() . '/css/wysiwyg-color.css';
		array_unshift($this->editorOptions['stylesheets'], $defaultStyleSheetUrl); // we want default css to be first
	}

	private function normalizeStylesheetsProperty()
	{
		if (empty($this->editorOptions['stylesheets'])) {
			$this->editorOptions['stylesheets'] = array();
		} else if (is_array($this->editorOptions['stylesheets'])) {
			$this->editorOptions['stylesheets'] = array_filter(
				$this->editorOptions['stylesheets'],
				'is_string'
			);
		} else if (is_string($this->editorOptions['stylesheets'])) {
			$this->editorOptions['stylesheets'] = array($this->editorOptions['stylesheets']);
		} else // presumably if this option is neither an array or string then it's some erroneous value; clean it
		{
			$this->editorOptions['stylesheets'] = array();
		}
	}
}
