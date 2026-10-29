<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(dirname(dirname(__DIR__)) . '/fakes/WidgetTestCase.php');

Yii::import('booster.widgets.TbCarousel');

class TbCarouselTest extends WidgetTestCase
{
	/**
	 * With an own id in htmlOptions the indicators pointed at the widget id (#yw0) while the
	 * carousel and its controls used htmlOptions['id'], so the indicators did nothing.
	 *
	 * @test
	 */
	public function indicatorsTargetTheCarouselsOwnId()
	{
		$xpath = $this->renderXPath('TbCarousel', array(
			'htmlOptions' => array('id' => 'hero'),
			'items' => array(
				array('image' => 'a.png', 'label' => 'A'),
				array('image' => 'b.png', 'label' => 'B'),
			),
		));

		$this->assertNodeCount($xpath, '//div[@id="hero"]', 1);
		$this->assertNodeCount($xpath, '//div[contains(@class, "carousel-indicators")]/button[@data-bs-target="#hero"]', 2);
	}
}
