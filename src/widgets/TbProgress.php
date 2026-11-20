<?php
/**
 *## TbProgress class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */
Yii::import('booster.widgets.TbWidget');
/**
 *## Bootstrap progress bar widget.
 *
 * @see <http://twitter.github.com/bootstrap/components.html#progress>
 *
 * @package booster.widgets.decoration
 * @since 0.9.10
 */
class TbProgress extends TbWidget {
	
	/**
	 * @var boolean indicates whether the bar is striped.
	 */
	public $striped = false;

	/**
	 * @var boolean indicates whether the bar is animated.
	 */
	public $animated = false;

	/**
	 * @var integer the amount of progress in percent.
	 */
	public $percent = 0;

	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();

	/**
	 * @var string div content
	 */
	public $content;

	/**
	 * @var array $stacked set to an array of progress bar values to display stacked progress bars
	 * <pre>
	 *  'stacked'=>array(
	 *      array('context' => 'info|success|warning|danger', 'percent'=>'30', 'htmlOptions'=>array('class'=>'custom')),
	 *      array('context' => 'info|success|warning|danger', 'percent'=>'30'),
	 *  )
	 * </pre>
	 * @since 9/21/12 8:14 PM antonio ramirez <antonio@clevertech.biz>
	 */
	public $stacked;

	protected $progressClasses = array('progress');
	protected $progressBarClasses = array('progress-bar');
	
	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		if ($this->isValidContext())
			$this->progressBarClasses[] = 'bg-' . $this->getContextClass();

		// Both of these belong on the bar, not on the container. The old code put them on the
		// container, which was already wrong under Bootstrap 3 - striping has never worked here.
		if ($this->striped)
			$this->progressBarClasses[] = 'progress-bar-striped';

		if ($this->animated)
			$this->progressBarClasses[] = 'progress-bar-animated';

		if ($this->percent < 0)
			$this->percent = 0;
		else if ($this->percent > 100)
			$this->percent = 100;

		if (!empty($this->progressClasses)) {
			$classes = implode(' ', $this->progressClasses);
			if (isset($this->htmlOptions['class'])) {
				$this->htmlOptions['class'] .= ' ' . $classes;
			} else {
				$this->htmlOptions['class'] = $classes;
			}
		}
	}

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 * @since  9/21/12 8:13 PM  antonio ramirez <antonio@clevertech.biz>
	 * Updated to use stacked progress bars
	 */
	public function run() {
		
		echo CHtml::openTag('div', $this->htmlOptions);
		if (empty($this->stacked)) {
			echo CHtml::tag('div', array(
				'class' => implode(' ', $this->progressBarClasses),
				'style' => 'width: ' . $this->percent . '%;',
				'role' => 'progressbar',
				'aria-valuenow' => $this->percent,
				'aria-valuemin' => '0',
				'aria-valuemax' => '100',
			), $this->content);
		} elseif (is_array($this->stacked)) {
			foreach ($this->stacked as $bar) {
				$options = isset($bar['htmlOptions']) ? $bar['htmlOptions'] : array();
				if (empty($options['style'])) {
					$options['style'] = '';
				} else {
					$options['style'] .= ' ';
				}
				$options['style'] .= 'width: ' . $bar['percent'] . '%';

				if (empty($options['class'])) {
					$options['class'] = '';
				} else {
					// Was $options['style'] - a copy/paste slip that appended the separator to the
					// wrong attribute, running the caller's class into ours.
					$options['class'] .= ' ';
				}
				$options['class'] .= 'progress-bar bg-' . $bar['context'];

				$options['role'] = 'progressbar';
				$options['aria-valuenow'] = $bar['percent'];
				$options['aria-valuemin'] = '0';
				$options['aria-valuemax'] = '100';

				echo '<div ' . CHtml::renderAttributes($options) . '>' . @$bar['content'] . '</div>';
			}
		}
		echo CHtml::closeTag('div');
	}
	
	protected function isValidContext($context = false) {
		return in_array($this->context, array(
			self::CTX_SUCCESS, 
			self::CTX_INFO, 
			self::CTX_WARNING, 
			self::CTX_DANGER)
		);
	}
}
