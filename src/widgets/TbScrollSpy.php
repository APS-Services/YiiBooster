<?php
/**
 *## TbScrollSpy class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2012-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php) 
 */

/**
 *## Bootstrap scrollspy widget.
 *
 * @see <http://twitter.github.com/bootstrap/javascript.html#scrollspy>
 *
 * @since 1.0.0
 * @package booster.widgets.supplementary
 */
class TbScrollSpy extends CWidget
{
	/**
	 * @var string the CSS selector for the scrollspy element. Defaults to 'body'.
	 */
	public $selector = 'body';

	/**
	 * @var string the CSS selector for the spying element.
	 */
	public $target;

	/**
	 * @var integer the scroll offset (in pixels).
	 */
	public $offset;

	/**
	 * @var array string[] the Javascript event handlers.
	 */
	public $events = array();

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 */
	public function run()
	{
		// Bootstrap 3 picked scrollspy up from its data attribute through the data-api, so
		// setting that attribute from script was enough. Bootstrap 5 only consults it during its
		// own start-up, so an attribute written afterwards is never seen and the component has to
		// be constructed explicitly.
		$options = array();
		if (isset($this->target)) {
			$options['target'] = $this->target;
		}
		if (isset($this->offset)) {
			$options['offset'] = $this->offset;
		}

		$selector = CJavaScript::encode($this->selector);
		$script = 'Booster.scrollSpy(' . $selector . ', ' . CJavaScript::encode($options) . ');';

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		Booster::getBooster()->registerPackage('booster');
		$cs->registerScript(__CLASS__ . '#' . $this->selector, $script, CClientScript::POS_READY);

		foreach ($this->events as $name => $handler) {
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(
				__CLASS__ . '#' . $this->selector . '_' . $name,
				"jQuery('{$this->selector}').on('{$name}', {$handler});"
			);
		}
	}
}

