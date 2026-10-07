<?php
/**
 *## TbTags class file.
 *
 * @author Antonio Ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php) 
 */

/**
 *## TbTags class
 *
 * Encapsulates the Bootstrap Tags plugin by Maxwells
 * @see <https://github.com/maxwells/bootstrap-tags>
 *
 * @package booster.widgets.forms.inputs
 */
Yii::import('booster.helpers.TbCss');

class TbTags extends CInputWidget {
	
	/**
	 * @var TbActiveForm when created via TbActiveForm
	 *
	 * This attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array
	 *
	 * Suggestions for generating the list options:  array('A','B','C')
	 *
	 */
	public $suggestions = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 * The events are on the format:
	 *
	 * <pre>
	 *    // ...
	 *    'whenAddingTag' => 'js:function(tag){ console.log(tag);}',
	 *    // ...
	 * </pre>
	 *
	 * @see <https://github.com/maxwells/bootstrap-tags#overrideable-functions>
	 *
	 */

	public $events = array(
		// whenAddingTag (tag:string) : anything external you'd like to do with the tag
		'whenAddingTag' => null,
		// tagRemoved (tag:string) : find out which tag was removed by either presseing delete key or clicking the (x)
		'tagRemoved' => null,
		// definePopover (tag:string) : must return the popover content for the tag that is being added. (eg "Content for [tag]")
		'definePopover' => null,
		// excludes (tag:string) : returns true if you want the tag to be excluded, false if allowed
		'exclude' => null,
		// pressedReturn (e:triggering event)
		'pressedReturn' => null,
		// pressedDelete (e:triggering event)
		'pressedDelete' => null,
		// pressedDown (e:triggering event)
		'pressedDown' => null,
		// pressedUp (e:triggering event)
		'pressedUp' => null,
	);

	/**
	 * @var array $restricTo the list of allowed tags
	 */
	public $restrictTo;

	/**
	 * @var array list of tags to display initially display
	 */
	public $tagData = array();

	/**
	 * @var array list of popover messages that should be displayed with the tags initially displayed.
	 *
	 * <strong>Note</strong>: Is important that the list matches the index list of those tags in $tagData.
	 */
	public $popoverData;

	/**
	 * @var array $excludes the list of disallowed tags
	 */
	public $exclude = array();

	/**
	 * @var boolean $displayPopovers whether to display popovers with information or not
	 */
	public $displayPopovers;

	/**
	 * @var string $tagClass what class the tag div will have for styling.
	 * Defaults to `btn-success`
	 */
	public $tagClass = 'btn-success';

	/**
	 * @var string $promptText placeholder string when the re are no tags and nothing typed in
	 */
	public $promptText = 'Please, type in your tags...';

	/**
	 * @var array $options the array to configure the js component.
	 */
	protected $options = array();

	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		parent::init();
		
		// Only the options with a Select2 equivalent are forwarded. bootstrap-tags' tagClass,
		// displayPopovers, popoverData and exclude have none, and passing them through would look
		// honoured while doing nothing - see UPGRADE-5.0.md.
		$mapped = array();

		if (!empty($this->suggestions)) {
			$mapped['data'] = array_values($this->suggestions);
		}

		// restrictTo means "no free-form values", which is Select2's tags flag inverted.
		if (!empty($this->restrictTo)) {
			$mapped['data'] = array_values($this->restrictTo);
			$mapped['tags'] = false;
		}

		if ($this->promptText !== null && $this->promptText !== '') {
			$mapped['placeholder'] = $this->promptText;
		}

		$this->options = CMap::mergeArray($mapped, $this->options);
	}

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 */
	public function run() {
		
		list($name, $id) = $this->resolveNameID();

		$this->renderContent($id, $name);
		$this->registerClientScript($id);
	}

	/**
	 *### .renderContent()
	 *
	 * Renders required HTML tags
	 *
	 * @param integer $id
	 * @param string $name
	 *
	 * @return string with HTML tags
	 */
	public function renderContent($id, $name) {

		// bootstrap-tags decorated an empty <div> and kept the real value in a sibling hidden
		// field that it synchronised itself. Select2 is select-backed: it reads and writes the
		// <select>'s options directly, so the select has to be the submitting control. Keeping the
		// old div would leave Select2 with nothing to bind to and the value never updated.
		$this->htmlOptions['id'] = 'tags_' . $id;
		$this->htmlOptions['multiple'] = true;
		self::addCssClass($this->htmlOptions, 'tag-list');

		$selected = $this->resolveTags();
		// Select2's `tags` mode accepts values that are not in the option list, so the current
		// tags are the whole list - there is nothing else to offer.
		// array_combine() on two empty arrays returns false on PHP 5.3/5.4 rather than array(),
		// and src/ targets 5.3 - the empty case is the normal one for a new record.
		$data = empty($selected) ? array() : array_combine($selected, $selected);

		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->form->listBox($this->model, $this->attribute, $data, $this->htmlOptions);
			} else {
				echo CHtml::activeListBox($this->model, $this->attribute, $data, $this->htmlOptions);
			}
		} else {
			echo CHtml::listBox($name, $selected, $data, $this->htmlOptions);
		}
	}

	/**
	 * The tags to pre-select, as a flat list of strings.
	 *
	 * Accepts what both this widget and bootstrap-tags accepted: an array, or a comma-separated
	 * string as stored by a plain text column.
	 *
	 * @return array
	 * @since 5.0.0
	 */
	protected function resolveTags() {

		$value = !empty($this->tagData) ? $this->tagData : null;

		if ($value === null) {
			$value = $this->hasModel()
				? CHtml::value($this->model, $this->attribute)
				: $this->value;
		}

		if (is_array($value)) {
			$tags = $value;
		} elseif ($value === null || $value === '') {
			$tags = array();
		} else {
			$tags = preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
		}

		$tags = array_values(array_unique(array_map('strval', $tags)));

		return $tags;
	}

	/**
	 * @param array $htmlOptions
	 * @param string $class
	 */
	protected static function addCssClass(&$htmlOptions, $class) {

		TbCss::add($htmlOptions, $class);
	}

	/**
	 *### .registerClientScript()
	 *
	 * Registers required client script for bootstrap select2.
	 * It is not used through bootstrap->registerPlugin
	 * in order to attach events if any
	 *
	 * @param string $id
	 */
	public function registerClientScript($id) {

		Booster::getBooster()->registerPackage('select2');

		// bootstrap-tags has been untouched since 2017. Select2 is already bundled and already on
		// 4.x, and its `tags` mode is the same feature - so this drops a dependency rather than
		// swapping one.
		$options = array_merge(
			array('tags' => true, 'tokenSeparators' => array(',', ' '), 'theme' => 'bootstrap-5', 'width' => '100%'),
			(array) $this->options
		);

		Yii::app()->getClientScript()->registerScript(
			__CLASS__ . '#' . $this->getId(),
			"jQuery('#tags_{$id}').select2(" . CJavaScript::encode($options) . ");"
		);
	}
}
