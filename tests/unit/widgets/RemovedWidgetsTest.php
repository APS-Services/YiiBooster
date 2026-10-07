<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');

foreach (array('TbHeroUnit', 'TbJumbotron') as $removedWidget) {
	require_once(__DIR__ . '/../../../src/widgets/' . $removedWidget . '.php');
}
foreach (array('TbInput', 'TbInputHorizontal', 'TbInputVertical', 'TbInputInline', 'TbInputSearch') as $removedInput) {
	require_once(__DIR__ . '/../../../src/widgets/input/' . $removedInput . '.php');
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
		);
	}

	/**
	 * @test
	 * @dataProvider removedWidgets
	 *
	 * @param string $class
	 */
	public function removedWidgetThrowsInsteadOfRendering($class)
	{
		$widget = new $class(Yii::app()->getController());

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
			$this->assertStringContainsString(
				'removed in YiiBooster 5.0',
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
		$widget = new $class(Yii::app()->getController());

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
}
