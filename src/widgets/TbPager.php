<?php
/**
 *## TbPager class file.
 *
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## Bootstrap pager.
 *
 * @see <http://twitter.github.com/bootstrap/components.html#pagination>
 *
 * @package booster.widgets.supplementary
 */
class TbPager extends CLinkPager {
	
	// Pager alignments.
	const ALIGNMENT_CENTER = 'centered';
	const ALIGNMENT_RIGHT = 'right';

	/**
	 * @var string attributes for the pager container tag.
	 */
	public $containerTag = 'div';
	
	/**
	 * @var array HTML attributes for the pager container tag.
	 */
	public $containerHtmlOptions = array();
	
	/**
	 * @var string the pager alignment.
	 * Valid values are 'centered' and 'right'.
	 */
	public $alignment = self::ALIGNMENT_RIGHT;

	/**
	 * @var string the text shown before page buttons.
	 * Defaults to an empty string, meaning that no header will be displayed.
	 */
	public $header = '';
	
	/**
	 * @var string the URL of the CSS file used by this pager.
	 * Defaults to false, meaning that no CSS will be included.
	 */
	public $cssFile = false;

	/**
	 * @var boolean whether to display the first and last items.
	 */
	public $displayFirstAndLast = false;

	/**
	 *### .init()
	 *
	 * Initializes the pager by setting some default property values.
	 */
	public function init() {
		
		if ($this->nextPageLabel === null) {
			$this->nextPageLabel = '&raquo;';
		}

		if ($this->prevPageLabel === null) {
			$this->prevPageLabel = '&laquo;';
		}

		$classes = array('pagination');

		// Bootstrap 5 aligns pagination with flex utilities on the list itself. Before 5.0 this
		// floated the list with a Bootstrap 3 utility class, or set text alignment on the
		// container with an inline style.
		if ($this->alignment === self::ALIGNMENT_RIGHT) {
			$classes[] = 'justify-content-end';
		} elseif ($this->alignment === self::ALIGNMENT_CENTER) {
			$classes[] = 'justify-content-center';
		}

		// Append rather than assign. This used to overwrite htmlOptions['class'] outright, so a
		// caller-supplied class was silently discarded.
		$existing = isset($this->htmlOptions['class']) ? trim($this->htmlOptions['class']) : '';
		$this->htmlOptions['class'] = trim(implode(' ', $classes) . ' ' . $existing);

		parent::init();
	}
	
	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run() {
		
		$this->registerClientScript();
		$buttons=$this->createPageButtons();
		if(empty($buttons))
			return;
		echo CHtml::openTag($this->containerTag, $this->containerHtmlOptions);
		echo $this->header;
		echo CHtml::tag('ul',$this->htmlOptions,implode("\n",$buttons));
		echo $this->footer;
		echo CHtml::closeTag($this->containerTag);
	}

	/**
	 *### .createPageButtons()
	 *
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons() {

		if (($pageCount = $this->getPageCount()) <= 1) {
			return array();
		}

		list ($beginPage, $endPage) = $this->getPageRange();

		$currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()

		$buttons = array();

		// first page
		if ($this->displayFirstAndLast) {
			$buttons[] = $this->createPageButton($this->firstPageLabel, 0, 'first', $currentPage <= 0, false);
		}

		// prev page
		if (($page = $currentPage - 1) < 0) {
			$page = 0;
		}

		$buttons[] = $this->createPageButton($this->prevPageLabel, $page, 'previous', $currentPage <= 0, false);

		// internal pages
		for ($i = $beginPage; $i <= $endPage; ++$i) {
			$buttons[] = $this->createPageButton($i + 1, $i, '', false, $i == $currentPage);
		}

		// next page
		if (($page = $currentPage + 1) >= $pageCount - 1) {
			$page = $pageCount - 1;
		}

		$buttons[] = $this->createPageButton(
			$this->nextPageLabel,
			$page,
			'next',
			$currentPage >= ($pageCount - 1),
			false
		);

		// last page
		if ($this->displayFirstAndLast) {
			$buttons[] = $this->createPageButton(
				$this->lastPageLabel,
				$pageCount - 1,
				'last',
				$currentPage >= ($pageCount - 1),
				false
			);
		}

		return $buttons;
	}

	/**
	 * Builds the CSS class for one pagination list item.
	 *
	 * Bootstrap 5 styles pagination through page-item on the list item and page-link on the
	 * anchor. Without them the list renders as plain bullets, which is what every grid in an
	 * application looked like before 5.0.
	 *
	 * Shared with TbJsonPager, which hands the same class to a client-side template rather than
	 * rendering it here, so the two must not drift apart.
	 *
	 * @param string $class 'first', 'last', 'next', 'previous' or ''. Not Bootstrap classes -
	 * kept as styling hooks that applications may already target.
	 * @param boolean $hidden whether the button is disabled.
	 * @param boolean $selected whether the button is the current page.
	 * @return string
	 * @since 5.0.0
	 */
	protected function pageItemCssClass($class, $hidden, $selected)
	{
		$classes = array('page-item');

		if ($class !== '') {
			$classes[] = $class;
		}
		if ($hidden) {
			$classes[] = 'disabled';
		}
		if ($selected) {
			$classes[] = 'active';
		}

		return implode(' ', $classes);
	}

	/**
	 *### .createPageButton()
	 *
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 *
	 * @param string $label the text label for the button
	 * @param integer $page the page number
	 * @param string $class the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean $hidden whether this page button is visible
	 * @param boolean $selected whether this page button is selected
	 *
	 * @return string the generated button
	 */
	protected function createPageButton($label, $page, $class, $hidden, $selected)
	{
		$itemOptions = array('class' => $this->pageItemCssClass($class, $hidden, $selected));
		if ($selected) {
			$itemOptions['aria-current'] = 'page';
		}

		$linkOptions = array('class' => 'page-link');
		if ($hidden) {
			// .disabled only styles the item; the anchor is still focusable and clickable, so
			// take it out of the tab order and mark it up as Bootstrap's own docs do.
			$linkOptions['tabindex'] = '-1';
			$linkOptions['aria-disabled'] = 'true';
		}

		return CHtml::tag(
			'li',
			$itemOptions,
			CHtml::link($label, $this->createPageUrl($page), $linkOptions)
		);
	}
}
