<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbPager.php');

/**
 * Contract tests for TbPager, pinned before the Bootstrap 5 rewrite.
 *
 * TbPager is rewritten early in the migration because it currently emits `<li class="active">`
 * with no `page-item`/`page-link`, which leaves every grid's pagination unstyled under Bootstrap 5.
 *
 * These assertions deliberately avoid Bootstrap class names. They cover what must not change when
 * the markup does: how many links are produced, where they point, what labels they carry, and that
 * caller-supplied htmlOptions survive.
 */
class TbPagerTest extends WidgetTestCase
{
	const WIDGET_CLASS = 'TbPager';

	/**
	 * @param int $itemCount
	 * @param int $pageSize
	 * @param int $currentPage
	 * @return CPagination
	 */
	protected function makePages($itemCount = 100, $pageSize = 10, $currentPage = 0)
	{
		$pages = new CPagination($itemCount);
		$pages->pageSize = $pageSize;
		$pages->setCurrentPage($currentPage);

		return $pages;
	}

	/**
	 * @test
	 */
	public function rendersOneAnchorPerPageButton()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 0),
		));

		// 10 pages, but maxButtonCount defaults to 10, plus prev/next. Assert we get a link per
		// list item rather than hardcoding Bootstrap's structure.
		$items = $xpath->query('//li');
		$this->assertGreaterThan(0, $items->length, 'Pager rendered no list items at all.');
		$this->assertNodeCount(
			$xpath,
			'//li[a]',
			$items->length,
			'Every pager item should contain an anchor.'
		);
	}

	/**
	 * @test
	 */
	public function pageLinksCarrySequentialPageParameters()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 0),
		));

		$second = $this->firstNode($xpath, '//a[normalize-space(text())="2"]');
		$this->assertStringContainsString(
			'page=2',
			$second->getAttribute('href'),
			'The "2" button must link to page 2.'
		);
	}

	/**
	 * @test
	 */
	public function usesConfiguredPreviousAndNextLabels()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 2),
			'prevPageLabel' => 'PREVIOUS',
			'nextPageLabel' => 'NEXT',
		));

		$this->assertNodeCount($xpath, '//a[normalize-space(text())="PREVIOUS"]', 1);
		$this->assertNodeCount($xpath, '//a[normalize-space(text())="NEXT"]', 1);
	}

	/**
	 * @test
	 */
	public function firstAndLastButtonsAppearOnlyWhenRequested()
	{
		$without = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 5),
			'firstPageLabel' => 'FIRST',
			'lastPageLabel' => 'LAST',
			'displayFirstAndLast' => false,
		));
		$this->assertNodeCount($without, '//a[normalize-space(text())="FIRST"]', 0);

		$with = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 5),
			'firstPageLabel' => 'FIRST',
			'lastPageLabel' => 'LAST',
			'displayFirstAndLast' => true,
		));
		$this->assertNodeCount($with, '//a[normalize-space(text())="FIRST"]', 1);
		$this->assertNodeCount($with, '//a[normalize-space(text())="LAST"]', 1);
	}

	/**
	 * @test
	 */
	public function containerTagIsConfigurable()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(),
			'containerTag' => 'nav',
		));

		$this->assertNodeCount($xpath, '//nav//ul', 1, 'containerTag should wrap the pager list.');
	}

	/**
	 * KNOWN BUG, documented rather than asserted-as-correct.
	 *
	 * TbPager::init() overwrites htmlOptions['class'] instead of appending to it, so a
	 * caller-supplied class is silently discarded:
	 *
	 *     if (isset($this->htmlOptions['class'])) {
	 *         $this->htmlOptions['class'] = ' ' . $classes;   // <- should append
	 *     }
	 *
	 * Every other widget in this library appends via TbWidget::addCssClass(). This test records
	 * today's behaviour so the Bootstrap 5 rewrite of TbPager has to make a deliberate decision
	 * about it - fixing the bug will fail this test, which is the intended prompt to update it.
	 *
	 * @test
	 */
	public function callerSuppliedListClassIsCurrentlyDiscarded()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(),
			'htmlOptions' => array('class' => 'my-custom-class'),
		));

		$list = $this->firstNode($xpath, '//ul');
		$this->assertStringNotContainsString(
			'my-custom-class',
			$list->getAttribute('class'),
			'If this now passes through, the init() bug was fixed - update this test to assert it survives.'
		);
	}

	/**
	 * @test
	 */
	public function containerHtmlOptionsArePassedThrough()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(),
			'containerHtmlOptions' => array('id' => 'my-pager', 'data-role' => 'pagination'),
		));

		$container = $this->firstNode($xpath, '//*[@id="my-pager"]');
		$this->assertEquals('pagination', $container->getAttribute('data-role'));
	}
}
