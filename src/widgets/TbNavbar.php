<?php
/**
 *## TbNavbar class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

Yii::import('booster.widgets.TbCollapse');

/**
 *## Bootstrap navigation bar widget.
 *
 * @package booster.widgets.navigation
 * @since 0.9.7
 */
class TbNavbar extends CWidget {
	
	const CONTAINER_PREFIX = 'yii_booster_collapse_';
	
	// Navbar types.
	const TYPE_DEFAULT = 'default';
	const TYPE_INVERSE = 'inverse';

	// Navbar fix locations.
	/**
	 * @var string breakpoint at which the navbar expands from its collapsed state.
	 * One of 'sm', 'md', 'lg', 'xl', 'xxl'. Bootstrap 4 made this mandatory - a navbar with no
	 * navbar-expand-* class never expands.
	 * @since 5.0.0
	 */
	public $expand = 'lg';

	const FIXED_TOP = 'top';
	const FIXED_BOTTOM = 'bottom';

	/**
	 * @var string the navbar type. Valid values are 'inverse'.
	 * @since 1.0.0
	 */
	public $type = self::TYPE_DEFAULT;

	/**
	 * @var string the text for the brand.
	 */
	public $brand;

	/**
	 * @var string the URL for the brand link.
	 */
	public $brandUrl;

	/**
	 * @var array the HTML attributes for the brand link.
	 */
	public $brandOptions = array();

	/**
	 * @var array navigation items.
	 * @since 0.9.8
	 */
	public $items = array();

	/**
	 * @var mixed fix location of the navbar if applicable.
	 * Valid values are 'top' and 'bottom'. Defaults to 'top'.
	 * Setting the value to false will make the navbar static.
	 * @since 0.9.8
	 */
	public $fixed = self::FIXED_TOP;

	/**
	 * @var boolean whether the nav span over the full width. Defaults to false.
	 * @since 0.9.8
	 */
	public $fluid = false;

	/**
	 * @var boolean whether to enable collapsing on narrow screens. Default to true.
	 */
	public $collapse = true;

	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();
	
	/**
	 * @var array the widget options for the collapsed toggle button.
	 */
	public $toggleButtonWidgetOptions = array();

	/**
	 *### .init()
	 *
	 * Initializes the widget.
	 */
	public function init() {
		
		if ($this->brand !== false) {
			if (!isset($this->brand)) {
				$this->brand = CHtml::encode(Yii::app()->name);
			}

			if (!isset($this->brandUrl)) {
				$this->brandUrl = Yii::app()->homeUrl;
			}

			$this->brandOptions['href'] = CHtml::normalizeUrl($this->brandUrl);

			if (isset($this->brandOptions['class'])) {
				$this->brandOptions['class'] .= ' navbar-brand';
			} else {
				$this->brandOptions['class'] = 'navbar-brand';
			}
		}

		$classes = array('navbar');

		// Required in Bootstrap 4+: without navbar-expand-* the collapse never expands and the
		// navbar stays permanently stacked.
		$classes[] = 'navbar-expand-' . $this->expand;

		// navbar-default and navbar-inverse are gone. Bootstrap 5.3 expresses the light/dark
		// distinction with a background utility plus a colour mode, rather than a navbar variant.
		if ($this->type === self::TYPE_INVERSE) {
			$classes[] = 'bg-dark';
			if (!isset($this->htmlOptions['data-bs-theme'])) {
				$this->htmlOptions['data-bs-theme'] = 'dark';
			}
		} else {
			$classes[] = 'bg-body-tertiary';
		}

		// navbar-fixed-* became the general-purpose fixed-* utility.
		if ($this->fixed !== false && in_array($this->fixed, array(self::FIXED_TOP, self::FIXED_BOTTOM))) {
			$classes[] = 'fixed-' . $this->fixed;
		}

		if (!empty($classes)) {
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class'])) {
				$this->htmlOptions['class'] .= ' ' . $classes;
			} else {
				$this->htmlOptions['class'] = $classes;
			}
		}
		
		if ($this->collapse) {
			// The three icon-bar spans became a single element whose glyph comes from CSS.
			if (!isset($this->toggleButtonWidgetOptions['label'])) {
				$this->toggleButtonWidgetOptions['label'] = '<span class="navbar-toggler-icon"></span>';
			}
			if (!isset($this->toggleButtonWidgetOptions['htmlOptions'])) {
				$this->toggleButtonWidgetOptions['htmlOptions'] = array();
			}

			$target = '#' . self::CONTAINER_PREFIX . $this->id;
			$defaults = array(
				'class' => 'navbar-toggler',
				'type' => 'button',
				'data-bs-toggle' => 'collapse',
				'data-bs-target' => $target,
				'aria-controls' => self::CONTAINER_PREFIX . $this->id,
				'aria-expanded' => 'false',
				'aria-label' => Yii::t('zii', 'Toggle navigation'),
			);
			$this->toggleButtonWidgetOptions['htmlOptions'] += $defaults;
		}
	}

	/**
	 *### .run()
	 *
	 * Runs the widget.
	 */
	public function run() {
		
		echo CHtml::openTag('nav', $this->htmlOptions);
		echo '<div class="' . $this->getContainerCssClass() . '">';

		// Bootstrap 4 removed the navbar-header wrapper: brand and toggler are now direct
		// children of the container, with the brand first.
		if ($this->brand !== false) {
			if ($this->brandUrl !== false) {
				echo CHtml::openTag('a', $this->brandOptions) . $this->brand . '</a>';
			} else {
				unset($this->brandOptions['href']); // spans cannot have a href attribute
				echo CHtml::openTag('span', $this->brandOptions) . $this->brand . '</span>';
			}
		}

		if ($this->collapse) {
			// Rendered directly rather than through TbButton, which would add its own `btn`
			// class - navbar-toggler brings its own sizing and would fight with it.
			echo CHtml::tag(
				'button',
				$this->toggleButtonWidgetOptions['htmlOptions'],
				$this->toggleButtonWidgetOptions['label']
			);
		}

		echo '<div class="collapse navbar-collapse" id="'.self::CONTAINER_PREFIX.$this->id.'">';
		foreach ($this->items as $item) {
			if (is_string($item)) {
				echo $item;
			} else {
				if (!isset($item['type'])) {
					$item['type'] = 'navbar';
				}
				
				if (isset($item['class'])) {
					$className = $item['class'];
					unset($item['class']);

					$this->controller->widget($className, $item);
				}
			}
		}
		echo '</div></div></nav>';
	}

	/**
	 *### .getContainerCssClass()
	 *
	 * Returns the navbar container CSS class.
	 * @return string the class
	 */
	protected function getContainerCssClass() {
		
		return $this->fluid ? 'container-fluid' : 'container';
	}
}
