<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbWidget.php');
require_once(__DIR__ . '/../../../src/widgets/TbPanel.php');
require_once(__DIR__ . '/../../../src/widgets/TbNavbar.php');
require_once(__DIR__ . '/../../../src/widgets/TbLabel.php');
require_once(__DIR__ . '/../../../src/widgets/TbBadge.php');
require_once(__DIR__ . '/../../../src/widgets/TbProgress.php');

/**
 * Structural assertions for the layout widgets rewritten in Phase 5.
 *
 * These check the shapes Bootstrap 5 actually requires - the ones whose absence makes a component
 * silently render as unstyled markup rather than throwing.
 */
class LayoutMarkupTest extends WidgetTestCase
{
	/**
	 * A panel is a card now. Bootstrap 5 has no .panel rule at all, so leaving the old class
	 * would render an unstyled div.
	 *
	 * @test
	 */
	public function panelRendersAsACard()
	{
		$html = $this->render('TbPanel', array('title' => 'Hello', 'content' => 'Body'));

		$this->assertStringContainsString('card', $html);
		$this->assertStringContainsString('card-header', $html);
		$this->assertStringContainsString('card-body', $html);
		$this->assertStringNotContainsString('panel-heading', $html);
		$this->assertStringNotContainsString('panel-body', $html);
	}

	/**
	 * A contextual panel coloured its header and border but left the body plain, so the card
	 * takes a border utility and the header a text/background one - text-bg-* on the card itself
	 * would colour the body too.
	 *
	 * @test
	 */
	public function contextualPanelColoursBorderAndHeaderOnly()
	{
		$xpath = $this->renderXPath('TbPanel', array(
			'title' => 'Hello',
			'content' => 'Body',
			'context' => 'danger',
		));

		$card = $this->firstNode($xpath, '//div[contains(@class, "card")]');
		$this->assertStringContainsString('border-danger', $card->getAttribute('class'));
		$this->assertStringNotContainsString('text-bg-danger', $card->getAttribute('class'));

		$header = $this->firstNode($xpath, '//div[contains(@class, "card-header")]');
		$this->assertStringContainsString('text-bg-danger', $header->getAttribute('class'));
	}

	/**
	 * Without navbar-expand-* a Bootstrap 4+ navbar never expands and stays permanently collapsed.
	 *
	 * @test
	 */
	public function navbarCarriesAnExpandBreakpoint()
	{
		$html = $this->render('TbNavbar', array('brand' => 'Brand'));

		$this->assertStringContainsString('navbar-expand-lg', $html);
	}

	/**
	 * @test
	 */
	public function navbarDropsTheBootstrap3Wrapper()
	{
		$html = $this->render('TbNavbar', array('brand' => 'Brand'));

		$this->assertStringNotContainsString('navbar-header', $html);
		$this->assertStringNotContainsString('navbar-default', $html);
		$this->assertStringNotContainsString('navbar-inverse', $html);
	}

	/**
	 * @test
	 */
	public function invertedNavbarUsesColourMode()
	{
		$xpath = $this->renderXPath('TbNavbar', array('brand' => 'Brand', 'type' => TbNavbar::TYPE_INVERSE));

		$nav = $this->firstNode($xpath, '//nav');
		$this->assertEquals('dark', $nav->getAttribute('data-bs-theme'));
		$this->assertStringContainsString('bg-dark', $nav->getAttribute('class'));
	}

	/**
	 * @test
	 */
	public function collapsibleNavbarRendersABootstrap5Toggler()
	{
		$xpath = $this->renderXPath('TbNavbar', array('brand' => 'Brand', 'collapse' => true));

		$toggler = $this->firstNode($xpath, '//button[contains(@class, "navbar-toggler")]');

		$this->assertEquals('collapse', $toggler->getAttribute('data-bs-toggle'));
		$this->assertNotEquals('', $toggler->getAttribute('aria-label'));
		$this->assertNodeCount($xpath, '//span[contains(@class, "navbar-toggler-icon")]', 1);
		// The Bootstrap 3 hamburger was three of these.
		$this->assertNodeCount($xpath, '//span[contains(@class, "icon-bar")]', 0);
	}

	/**
	 * Bootstrap 4 removed the label component; both widgets now render badges, and 5.3 expresses
	 * the colour with the combined text/background utility.
	 *
	 * @test
	 */
	public function labelAndBadgeBothRenderContextualBadges()
	{
		foreach (array('TbLabel', 'TbBadge') as $class) {
			$xpath = $this->renderXPath($class, array('label' => 'x', 'context' => 'success'));
			$node = $this->firstNode($xpath, '//span');

			$this->assertStringContainsString('badge', $node->getAttribute('class'), $class);
			$this->assertStringContainsString('text-bg-success', $node->getAttribute('class'), $class);
		}
	}

	/**
	 * Striping and animation belong on the bar, not the container - putting them on the container
	 * was already wrong under Bootstrap 3, so striped progress bars have never actually striped.
	 *
	 * @test
	 */
	public function progressStripingAppliesToTheBar()
	{
		$xpath = $this->renderXPath('TbProgress', array(
			'percent' => 40,
			'striped' => true,
			'animated' => true,
			'context' => 'info',
		));

		$bar = $this->firstNode($xpath, '//div[contains(@class, "progress-bar")]');
		$class = $bar->getAttribute('class');

		$this->assertStringContainsString('progress-bar-striped', $class);
		$this->assertStringContainsString('progress-bar-animated', $class);
		$this->assertStringContainsString('bg-info', $class);
		$this->assertEquals('progressbar', $bar->getAttribute('role'));
		$this->assertEquals('40', $bar->getAttribute('aria-valuenow'));

		$container = $this->firstNode($xpath, '//div[contains(@class, "progress")][not(contains(@class, "progress-bar"))]');
		$this->assertStringNotContainsString('progress-striped', $container->getAttribute('class'));
	}
}
