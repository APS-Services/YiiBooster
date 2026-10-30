<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbGridView.php');
require_once(__DIR__ . '/../../../src/widgets/TbDetailView.php');

/**
 * Bootstrap 4 renamed table-condensed to table-sm.
 *
 * The name `condensed` is kept as an accepted input so existing application configuration - and
 * TbDetailView's own default, which includes it - keeps working. A literal grep for
 * "table-condensed" finds nothing in this codebase because the class is composed as
 * 'table-' . $type from a validated constant, which is exactly why this needed a test rather
 * than a search-and-replace.
 */
class TableTypeCssClassTest extends WidgetTestCase
{
	/**
	 * @param string $class
	 * @param string $type
	 * @return string
	 */
	protected function cssClassFor($class, $type)
	{
		$widget = new $class(Yii::app()->getController());
		$method = new ReflectionMethod($widget, 'tableTypeCssClass');
		$method->setAccessible(true);

		return $method->invoke($widget, $type);
	}

	/**
	 * @return array
	 */
	public function widgets()
	{
		return array(
			array('TbGridView'),
			array('TbDetailView'),
		);
	}

	/**
	 * @test
	 * @dataProvider widgets
	 *
	 * @param string $class
	 */
	public function legacyCondensedRendersAsTableSm($class)
	{
		$this->assertEquals('table-sm', $this->cssClassFor($class, 'condensed'));
	}

	/**
	 * @test
	 * @dataProvider widgets
	 *
	 * @param string $class
	 */
	public function tableSmIsAcceptedDirectly($class)
	{
		$this->assertEquals('table-sm', $this->cssClassFor($class, 'sm'));
	}

	/**
	 * @test
	 * @dataProvider widgets
	 *
	 * @param string $class
	 */
	public function unchangedTypesPassThrough($class)
	{
		$this->assertEquals('table-striped', $this->cssClassFor($class, 'striped'));
		$this->assertEquals('table-bordered', $this->cssClassFor($class, 'bordered'));
	}

	/**
	 * TbDetailView defaults to striped + condensed, so its out-of-the-box rendering is the case
	 * most likely to regress silently.
	 *
	 * @test
	 */
	public function detailViewDefaultTypeStillResolves()
	{
		$widget = new TbDetailView(Yii::app()->getController());

		$this->assertContains(TbDetailView::TYPE_CONDENSED, $widget->type);
		$this->assertEquals('table-sm', $this->cssClassFor('TbDetailView', TbDetailView::TYPE_CONDENSED));
	}
}
