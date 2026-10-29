<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbWidget.php');
require_once(__DIR__ . '/../../../src/widgets/TbButton.php');

/**
 * Class assertions for TbButton.
 */
class TbButtonTest extends WidgetTestCase
{
	/**
	 * buttonType "link" decides the tag, not the look. It used to add btn-link next to the context
	 * class; in Bootstrap 5 btn-link overrides the context's colours, so a success link button was
	 * link-coloured text on a green background - invisible. Found in myAPS.
	 *
	 * @test
	 */
	public function linkButtonKeepsTheColoursOfItsContext()
	{
		$xpath = $this->renderXPath('TbButton', array(
			'buttonType' => TbButton::BUTTON_LINK,
			'context' => 'success',
			'label' => 'Save',
			'url' => '#',
		));

		$link = $this->firstNode($xpath, '//a');
		$classes = explode(' ', $link->getAttribute('class'));
		$this->assertContains('btn-success', $classes);
		$this->assertNotContains('btn-link', $classes);
	}

	/**
	 * A text-style link button is still available, through its context.
	 *
	 * @test
	 */
	public function linkContextStillRendersAsTextLink()
	{
		$xpath = $this->renderXPath('TbButton', array(
			'buttonType' => TbButton::BUTTON_LINK,
			'context' => 'link',
			'label' => 'More',
			'url' => '#',
		));

		$classes = explode(' ', $this->firstNode($xpath, '//a')->getAttribute('class'));
		$this->assertContains('btn-link', $classes);
	}

	/**
	 * Without a context (TbBulkActions passes '') a link button stays a text link, as before.
	 *
	 * @test
	 */
	public function linkButtonWithoutContextIsTextLink()
	{
		$xpath = $this->renderXPath('TbButton', array(
			'buttonType' => TbButton::BUTTON_LINK,
			'context' => '',
			'label' => 'Delete',
			'url' => '#',
		));

		$classes = explode(' ', $this->firstNode($xpath, '//a')->getAttribute('class'));
		$this->assertContains('btn-link', $classes);
	}

	/**
	 * Bootstrap 5 reads tooltip options only from data-bs-* attributes; data-placement and friends
	 * were silently ignored.
	 *
	 * @test
	 */
	public function tooltipOptionsUseBootstrap5Attributes()
	{
		$xpath = $this->renderXPath('TbButton', array(
			'label' => 'Save',
			'tooltip' => true,
			'tooltipOptions' => array('placement' => 'bottom', 'delay' => array('show' => 500)),
			'htmlOptions' => array('title' => 'Save it'),
		));

		$button = $this->firstNode($xpath, '//button');
		$this->assertEquals('tooltip', $button->getAttribute('data-bs-toggle'));
		$this->assertEquals('bottom', $button->getAttribute('data-bs-placement'));
		$this->assertEquals('{"show":500}', $button->getAttribute('data-bs-delay'));
		$this->assertFalse($button->hasAttribute('data-placement'));
	}
}
