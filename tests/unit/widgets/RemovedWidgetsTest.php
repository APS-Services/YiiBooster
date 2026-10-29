<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');

foreach (array('TbHeroUnit', 'TbJumbotron', 'TbBox', 'TbToggleButton', 'TbPickerColumn', 'TbMarkdownEditorJs', 'TbChosen', 'TbPassfield', 'TbFileUpload', 'TbImageGallery', 'TbModalManager') as $removedWidget) {
	require_once(__DIR__ . '/../../../src/widgets/' . $removedWidget . '.php');
}
foreach (array('TbInput', 'TbInputHorizontal', 'TbInputVertical', 'TbInputInline', 'TbInputSearch') as $removedInput) {
	require_once(__DIR__ . '/../../../src/widgets/input/' . $removedInput . '.php');
}
foreach (array('TbHtml', 'TbArray') as $removedHelper) {
	require_once(__DIR__ . '/../../../src/helpers/' . $removedHelper . '.php');
}

/**
 * Widgets removed in 5.0 keep a stub that throws on init().
 *
 * Yii 1.1 resolves widget classes by path, so simply deleting the file gives an application an
 * "include(TbFoo.php): failed to open stream" fatal with no indication of what to do. The stubs
 * turn that into a message naming the replacement, reported at the line that used the widget.
 *
 * These stubs are scheduled for deletion in 5.1; this test goes with them.
 */
class RemovedWidgetsTest extends WidgetTestCase
{
	/**
	 * @return array
	 */
	public function removedWidgets()
	{
		return array(
			array('TbHeroUnit'),
			array('TbJumbotron'),
			array('TbInput'),
			array('TbInputHorizontal'),
			array('TbInputVertical'),
			array('TbInputInline'),
			array('TbInputSearch'),
			array('TbBox'),
			array('TbToggleButton'),
			array('TbPickerColumn'),
			array('TbMarkdownEditorJs'),
			array('TbChosen'),
			array('TbPassfield'),
			array('TbFileUpload'),
			array('TbImageGallery'),
			array('TbModalManager'),
		);
	}

	/**
	 * Grid columns are built with their grid, widgets with their owner.
	 *
	 * @param string $class
	 * @return CComponent
	 */
	protected function makeRemoved($class)
	{
		$owner = Yii::app()->getController();
		return is_subclass_of($class, 'CGridColumn') ? new $class(new CGridView($owner)) : new $class($owner);
	}

	/**
	 * @test
	 * @dataProvider removedWidgets
	 *
	 * @param string $class
	 */
	public function removedWidgetThrowsInsteadOfRendering($class)
	{
		$widget = $this->makeRemoved($class);

		try {
			$widget->init();
		} catch (CException $e) {
			$this->assertStringContainsString(
				$class,
				$e->getMessage(),
				'The exception should name the widget that was removed.'
			);
			$this->assertStringContainsString(
				'UPGRADE-5.0.md',
				$e->getMessage(),
				'The exception should point at the upgrade guide.'
			);
			// removed in 5.0, or (for applications coming from 3.x) renamed in 4.0
			$this->assertRegExp(
				'/(removed in YiiBooster 5\.0|renamed to \w+ in YiiBooster 4\.0)/',
				$e->getMessage()
			);

			return;
		}

		$this->fail("{$class} was removed in 5.0 and its stub must throw from init().");
	}

	/**
	 * The stubs must not accidentally still be usable widgets.
	 *
	 * @test
	 * @dataProvider removedWidgets
	 *
	 * @param string $class
	 */
	public function removedWidgetNamesItsReplacement($class)
	{
		$widget = $this->makeRemoved($class);

		try {
			$widget->init();
			$this->fail("{$class} did not throw.");
		} catch (CException $e) {
			// Every stub should tell the reader what to use instead, not just that it is gone.
			$this->assertRegExp(
				'/Use [^.]+/',
				$e->getMessage(),
				"{$class}'s removal message does not suggest a replacement."
			);
		}
	}

	/**
	 * TbHtml and TbArray were deleted outright, so their callers got the failed-include fatal the
	 * stubs exist to prevent.
	 *
	 * @test
	 */
	public function removedHelpersThrowOnEveryCall()
	{
		foreach (array('TbHtml' => 'button', 'TbArray' => 'getValue') as $class => $method) {
			try {
				call_user_func(array($class, $method), 'x');
				$this->fail("{$class}::{$method}() did not throw.");
			} catch (CException $e) {
				$this->assertStringContainsString('removed in YiiBooster 5.0', $e->getMessage());
				$this->assertStringContainsString('UPGRADE-5.0.md', $e->getMessage());
				$this->assertRegExp('/Use [^.]+/', $e->getMessage());
			}
		}
	}
}
