<?php
/**
 *## TbTypeahead class file.
 *
 * @author Amr Bedair <amr.bedair@gmail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @since v4.0.0
 * 
 * @todo add support of bloodhound datasets, and remote ajax 
 * @see <https://github.com/twitter/typeahead.js/blob/master/doc/jquery_typeahead.md#bloodhound-integration>
 */

/**
 *## Twitter typeahead widget.
 *
 * @see https://github.com/twitter/typeahead.js
 *
 * @since 4.0.0
 * @package booster.widgets.forms.inputs
 */

Yii::import('booster.widgets.TbBaseInputWidget');

class TbTypeahead extends TbBaseInputWidget {
	
	/**
	 * @var array the options for the twitter typeahead widget
	 * @see <https://github.com/twitter/typeahead.js/blob/master/doc/jquery_typeahead.md#options>
	 */
	public $options = array();
	
	/**
	 * @var array the datasets for the twitter typeahead widget 
	 * @see <https://github.com/twitter/typeahead.js/blob/master/doc/jquery_typeahead.md#datasets>
	 */
	public $datasets = array();

	/**
	 * @var array a flat list of suggestions, passed straight to Awesomplete.
	 * Preferred over $datasets, which exists for backwards compatibility.
	 * @since 5.0.0
	 */
	public $list = array();

	/**
	 * Initializes the widget.
	 */
	public function init() {
		if(empty($this->options))
			$this->options['minLength'] = 1;
		$this->registerClientScript();
		
		if(!isset($this->htmlOptions['class']) || empty($this->htmlOptions['class']))
			$this->htmlOptions['class'] = 'typeahead';
		else
			$this->htmlOptions['class'] .= ' typeahead';
		
		parent::init();
	}

	/**
	 * Runs the widget.
	 */
	public function run() {

		list($name, $id) = $this->resolveNameID();

		if (isset($this->htmlOptions['id'])) {
			$id = $this->htmlOptions['id'];
		} else {
			$this->htmlOptions['id'] = $id;
		}

		if (isset($this->htmlOptions['name'])) {
			$name = $this->htmlOptions['name'];
		}

		if ($this->hasModel()) {
			echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
		} else {
			echo CHtml::textField($name, $this->value, $this->htmlOptions);
		}

		$options = array_merge(
			array('minChars' => isset($this->options['minLength']) ? $this->options['minLength'] : 1),
			$this->awesompleteOptions()
		);
		$options['list'] = $this->resolveList();

		Yii::app()->clientScript->registerScript(
			__CLASS__ . '#' . $id,
			"new Awesomplete(document.getElementById('{$id}'), " . CJavaScript::encode($options) . ");"
		);
	}

	/**
	 * @return array $options minus the typeahead.js-only keys that Awesomplete does not know.
	 */
	protected function awesompleteOptions() {

		$options = $this->options;
		unset($options['minLength'], $options['highlight'], $options['hint'], $options['classNames']);

		return $options;
	}

	/**
	 * Flattens the legacy `datasets` structure into the flat list Awesomplete expects.
	 *
	 * typeahead.js was abandoned in 2015 and its remote support came from Bloodhound, which
	 * Awesomplete has no equivalent for - a remote source is now a fetch you write yourself and
	 * feed to the instance. Rather than silently returning an empty list for those, this says so.
	 *
	 * @return array
	 * @throws CException
	 */
	protected function resolveList() {

		if (!empty($this->list)) {
			return $this->list;
		}

		$datasets = $this->datasets;
		if (isset($datasets['source'])) {
			$datasets = array($datasets);
		}

		$list = array();
		foreach ($datasets as $dataset) {
			if (!isset($dataset['source'])) {
				throw new CException('The source for a Typeahead dataset was not set');
			}

			if (!is_array($dataset['source']) || isset($dataset['source']['name'])) {
				throw new CException(
					'TbTypeahead no longer supports Bloodhound remote sources. typeahead.js was '
					. 'abandoned in 2015 and is replaced by Awesomplete in YiiBooster 5.0, which '
					. 'takes a plain list. Pass one through the `list` property, or drive the '
					. 'Awesomplete instance yourself for remote lookups. See UPGRADE-5.0.md.'
				);
			}

			$list = array_merge($list, array_values($dataset['source']));
		}

		return $list;
	}

	/**
	 * Registers the Awesomplete assets.
	 */
	public function registerClientScript() {

		Booster::getBooster()->registerPackage('awesomplete');
	}

}
