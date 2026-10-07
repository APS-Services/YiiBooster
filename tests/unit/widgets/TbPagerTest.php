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
	 * Was a bug until 5.0: init() assigned htmlOptions['class'] instead of appending, silently
	 * discarding whatever the caller passed. Fixed with the Bootstrap 5 rewrite.
	 *
	 * @test
	 */
	public function callerSuppliedListClassSurvives()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(),
			'htmlOptions' => array('class' => 'my-custom-class'),
		));

		$class = $this->firstNode($xpath, '//ul')->getAttribute('class');

		$this->assertStringContainsString('my-custom-class', $class, 'Caller class was discarded.');
		$this->assertStringContainsString('pagination', $class, 'Base class was lost.');
	}

	/**
	 * Bootstrap 5 needs page-item/page-link; without them pagination renders as plain bullets.
	 *
	 * @test
	 */
	public function everyItemCarriesBootstrap5PaginationClasses()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 3),
		));

		$items = $xpath->query('//ul/li');
		$this->assertGreaterThan(0, $items->length);

		$this->assertNodeCount(
			$xpath,
			'//ul/li[contains(concat(" ", normalize-space(@class), " "), " page-item ")]',
			$items->length,
			'Every list item needs page-item.'
		);
		$this->assertNodeCount(
			$xpath,
			'//ul/li/a[contains(concat(" ", normalize-space(@class), " "), " page-link ")]',
			$items->length,
			'Every anchor needs page-link.'
		);
	}

	/**
	 * @test
	 */
	public function currentPageIsMarkedActiveAndAnnounced()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 3),
		));

		$active = $this->firstNode($xpath, '//li[contains(@class, "active")]');

		$this->assertStringContainsString('page-item', $active->getAttribute('class'));
		$this->assertEquals('page', $active->getAttribute('aria-current'));
		$this->assertEquals('4', trim($active->textContent), 'Page 4 is current when currentPage is 3.');
	}

	/**
	 * @test
	 */
	public function disabledItemsAreRemovedFromTheTabOrder()
	{
		$xpath = $this->renderXPath(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(100, 10, 0),
		));

		$link = $this->firstNode($xpath, '//li[contains(@class, "disabled")]/a');

		$this->assertEquals('-1', $link->getAttribute('tabindex'));
		$this->assertEquals('true', $link->getAttribute('aria-disabled'));
	}

	/**
	 * @return array
	 */
	public function alignments()
	{
		return array(
			array(TbPager::ALIGNMENT_RIGHT, 'justify-content-end'),
			array(TbPager::ALIGNMENT_CENTER, 'justify-content-center'),
		);
	}

	/**
	 * Bootstrap 5 aligns pagination with flex utilities; this used to float the list with
	 * pull-right, or set text-align on the container with an inline style.
	 *
	 * @test
	 * @dataProvider alignments
	 *
	 * @param string $alignment
	 * @param string $expectedClass
	 */
	public function alignmentUsesFlexUtilities($alignment, $expectedClass)
	{
		$html = $this->render(self::WIDGET_CLASS, array(
			'pages' => $this->makePages(),
			'alignment' => $alignment,
		));

		$this->assertStringContainsString($expectedClass, $html);
		$this->assertStringNotContainsString('pull-right', $html);
		$this->assertStringNotContainsString('text-align', $html);
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
