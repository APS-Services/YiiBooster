<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(dirname(dirname(__DIR__)) . '/fakes/WidgetTestCase.php');

Yii::import('booster.widgets.TbExtendedGridView');

/**
 * The grid/chart switch of TbExtendedGridView. It was built as a radio toggle group, which
 * TbButtonGroup rejects since Bootstrap 5 removed the button plugin's toggles - so every grid
 * with chartOptions and at least one row threw.
 */
class TbExtendedGridViewChartTest extends WidgetTestCase
{
	/**
	 * @test
	 */
	public function aGridWithAChartRendersItsSwitch()
	{
		$html = $this->render('TbExtendedGridView', array(
			'id' => 'sales',
			'dataProvider' => new CArrayDataProvider(array(array('id' => 1, 'amount' => 3))),
			'columns' => array('id', 'amount'),
			'chartOptions' => array('data' => array('series' => array(array('name' => 'Amount', 'attribute' => 'amount')))),
		));

		$this->assertStringContainsString('sales-grid-control grid', $html);
		$this->assertStringContainsString('sales-grid-control chart', $html);
		$this->assertStringNotContainsString('data-toggle', $html);
	}
}
