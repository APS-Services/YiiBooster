<?php
/**
 *## TbPanel widget class
 *
 * @author amr bedair <amr.bedair@gmail.com>
 * @copyright Copyright &copy; Clevertech 2014-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

Yii::import('booster.widgets.TbWidget');
Yii::import('booster.helpers.TbIcon');

/**
 * TbPanel widget.
 *
 *@see <https://getbootstrap.com/docs/5.3/components/card/>
 *
 * @package booster.widgets.grouping
 */
class TbPanel extends TbWidget {
	
	/**
	 * @var mixed
	 * Panel title
	 * If set to false, a panel with no title is rendered
	 */
	public $title = '';

	/**
	 * @var string
	 * The class icon to display in the header title of the panel.
	 * @see <http://twitter.github.com/bootstrap/base-css.html#icon>
	 */
	public $headerIcon;

	/**
	 * @var string
	 * Panel Content
	 * optional, the content of this attribute is echoed as the panel content
	 */
	public $content = '';
	
	public $padContent = true;

	/**
	 * @var array
	 * panel HTML additional attributes
	 */
	public $htmlOptions = array();

	/**
	 * @var array
	 * panel header HTML additional attributes
	 */
	public $headerHtmlOptions = array();

	/**
	 * @var array
	 * panel content HTML additional attributes
	 */
	public $contentHtmlOptions = array();

	/**
	 * @var array the configuration for additional header buttons. Each array element specifies a single button
	 * which has the following format:
	 * <pre>
	 *     array(
	 *        array(
	 *          'class' => 'booster.widgets.TbButton',
	 *          'label' => '...',
	 *          'size' => '...',
	 *          ...
	 *        ),
	 *      array(
	 *          'class' => 'booster.widgets.TbButtonGroup',
	 *          'buttons' => array( ... ),
	 *          'size' => '...',
	 *        ),
	 *      ...
	 * )
	 * </pre>
	 */
	public $headerButtons = array();

	/**
	 *### .init()
	 *
	 * Widget initialization
	 */
	public function init() {
		
		// Bootstrap 4 replaced the panel with the card. A contextual panel coloured its heading
		// and border while leaving the body plain, so the closest Bootstrap 5 equivalent is a
		// border utility on the card plus a text/background utility on the header - not
		// text-bg-* on the card itself, which would colour the body too.
		$this->addCssClass($this->htmlOptions, 'card');

		if($this->isValidContext())
			self::addCssClass($this->htmlOptions, 'border-'.$this->getContextClass());

		if ($this->padContent)
			self::addCssClass($this->contentHtmlOptions, 'card-body');

		if (!isset($this->contentHtmlOptions['id'])) {
			$this->contentHtmlOptions['id'] = $this->getId();
		}

		self::addCssClass($this->headerHtmlOptions, 'card-header');

		if ($this->isValidContext() && $this->getContextClass() !== self::CTX_SECONDARY_CLASS) {
			self::addCssClass($this->headerHtmlOptions, 'text-bg-' . $this->getContextClass());
		}

		echo CHtml::openTag('div', $this->htmlOptions);

		$this->renderHeader();
		$this->renderContentBegin();
	}

	/**
	 *### .run()
	 *
	 * Widget run - used for closing procedures
	 */
	public function run() {
		
		$this->renderContentEnd();
		echo CHtml::closeTag('div') . "\n";
	}

	/**
	 *### .renderHeader()
	 *
	 * Renders the header of the panel with the header control (button to show/hide the panel)
	 */
	public function renderHeader() {
		
		if ($this->title !== false) {
			echo CHtml::openTag('div', $this->headerHtmlOptions);
			if ($this->title) {
				$this->title = '<h3 class="card-title d-inline">' . $this->title . '</h3>';

				if ($this->headerIcon) {
					$icon = TbIcon::render($this->headerIcon);
					if ($icon !== '') {
						$this->title = $icon . ' ' . $this->title;
					}
				}
				
				$this->renderButtons();
				echo $this->title;
			}
			echo '<div class="clearfix"></div>';
			echo CHtml::closeTag('div');
		}
	}

	/**
	 *### .renderButtons()
	 *
	 * Renders a header buttons to display the configured actions
	 */
	public function renderButtons() {
		
		if (empty($this->headerButtons))
			return;

		echo '<div class="float-end">';

		if (!empty($this->headerButtons) && is_array($this->headerButtons)) {
			
			foreach ($this->headerButtons as $options) {
				
				$class = $options['class'];
				unset($options['class']);

				if (strpos($class, 'TbButton') === false)
					throw new CException('Button must be either TbButton, or TbButtonGroup');

				if (!isset($options['htmlOptions']))
					$options['htmlOptions'] = array();

				self::addCssClass($options['htmlOptions'], 'float-end');
				
				$this->controller->widget($class, $options);
			}
		}
		echo '</div>';
	}

	/**
	 *### .renderContentBegin()
	 *
	 * Renders the opening of the content element and the optional content
	 */
	public function renderContentBegin() {
		
		echo CHtml::openTag('div', $this->contentHtmlOptions);
		if (!empty($this->content)) {
			echo $this->content;
		}
	}

	/**
	 *### .renderContentEnd()
	 *
	 * Closes the content element
	 */
	public function renderContentEnd() {
		
		echo CHtml::closeTag('div');
	}

	/**
	 *### .registerClientScript()
	 *
	 * Registers required script files (CSS in this case)
	 */
	public function registerClientScript()
	{
		// do nothing
	}
}
