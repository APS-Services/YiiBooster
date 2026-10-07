<?php
/**
 *## TbModal class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## Bootstrap modal widget.
 *
 * @see <http://twitter.github.com/bootstrap/javascript.html#modals>
 *
 * @since 0.9.3
 * @package booster.widgets.modals
 */
class TbModal extends CWidget {
	
	/**
	 * @var boolean indicates whether to automatically open the modal when initialized. Defaults to 'false'.
	 */
	public $autoOpen = false;

	/**
	 * @var boolean indicates whether the modal should use transitions. Defaults to 'true'.
	 */
	public $fade = true;

	/**
	 * @var array the options for the Bootstrap Javascript plugin.
	 */
	public $options = array();

	/**
	 * @var string[] the Javascript event handlers.
	 */
	public $events = array();

	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();
	
 	/**
     	 * @var array the HTML attributes for the modal-dialog div.
         */
        public $modalDialogOptions = array();

        /**
         * @var array the HTML attributes for the modal-content div.
         */
        public $modalContentOptions = array();

	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		if (!isset($this->htmlOptions['id'])) {
			$this->htmlOptions['id'] = $this->getId();
		}

		// Bootstrap 5 removed the modal's `show` option: constructing never opens it, and show()
		// is an explicit call. autoOpen is passed to the client helper instead. A `show` value
		// left in options would simply be ignored by Bootstrap, so drop it rather than let it
		// look meaningful.
		if (isset($this->options['show'])) {
			$this->autoOpen = (bool) $this->options['show'];
			unset($this->options['show']);
		}

		$classes = array('modal');

		if ($this->fade === true) {
			$classes[] = 'fade';
		}

		if (!empty($classes)) {
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class'])) {
				$this->htmlOptions['class'] .= ' ' . $classes;
			} else {
				$this->htmlOptions['class'] = $classes;
			}
		}
		
        	if (isset($this->modalDialogOptions['class'])) {
            		$this->modalDialogOptions['class'] .= ' modal-dialog';
        	} else {
            		$this->modalDialogOptions['class'] = 'modal-dialog';
        	}

        	if (isset($this->modalContentOptions['class'])) {
            		$this->modalContentOptions['class'] .= ' modal-content';
        	} else {
            		$this->modalContentOptions['class'] = 'modal-content';
        	}

        	echo CHtml::openTag('div', $this->htmlOptions); //modal
        	echo CHtml::openTag('div', $this->modalDialogOptions); //modal-dialog
        	echo CHtml::openTag('div', $this->modalContentOptions); //modal-content
	}

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 */
	public function run() {
		
		$id = $this->htmlOptions['id'];

		echo CHtml::closeTag('div'); //modal-content
        	echo CHtml::closeTag('div'); //modal-dialog
        	echo CHtml::closeTag('div'); //modal

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();

		Booster::getBooster()->registerPackage('booster');
		$options = CJavaScript::encode($this->options);
		$autoOpen = CJavaScript::encode((bool) $this->autoOpen);
		$cs->registerScript(__CLASS__ . '#' . $id, "Booster.modal('{$id}', {$options}, {$autoOpen});");

		foreach ($this->events as $name => $handler) {
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(__CLASS__ . '#' . $id . '_' . $name, "jQuery('#{$id}').on('{$name}', {$handler});");
		}
	}
}
