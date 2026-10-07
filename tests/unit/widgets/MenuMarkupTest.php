<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbBaseMenu.php');
require_once(__DIR__ . '/../../../src/widgets/TbMenu.php');
require_once(__DIR__ . '/../../../src/widgets/TbDropdown.php');

/**
 * Bootstrap 4 restructured navs and dropdowns: items gained nav-item/nav-link and
 * dropdown-item, and the active and disabled states moved from the <li> onto the <a>.
 *
 * TbDropdown overrides renderMenuItem() wholesale rather than extending the base version, so
 * every one of these assertions is checked against both widgets - a change to TbBaseMenu alone
 * would leave dropdowns untouched.
 */
class MenuMarkupTest extends WidgetTestCase
{
	/**
	 * @return array
	 */
	protected function items()
	{
		return array(
			array('label' => 'First', 'url' => array('/one'), 'active' => true),
			array('label' => 'Second', 'url' => array('/two'), 'active' => false),
			array('label' => 'Third', 'url' => array('/three'), 'active' => false, 'disabled' => true),
		);
	}

	/**
	 * @test
	 */
	public function navItemsCarryNavItemAndNavLink()
	{
		$xpath = $this->renderXPath('TbMenu', array('items' => $this->items()));

		$this->assertNodeCount($xpath, '//li[contains(@class, "nav-item")]', 3);
		$this->assertNodeCount($xpath, '//a[contains(@class, "nav-link")]', 3);
	}

	/**
	 * @test
	 */
	public function dropdownItemsCarryDropdownItem()
	{
		$xpath = $this->renderXPath('TbDropdown', array('items' => $this->items()));

		$this->assertNodeCount($xpath, '//a[contains(@class, "dropdown-item")]', 3);
	}

	/**
	 * @return array
	 */
	public function menuWidgets()
	{
		return array(array('TbMenu'), array('TbDropdown'));
	}

	/**
	 * @test
	 * @dataProvider menuWidgets
	 *
	 * @param string $class
	 */
	public function activeStateSitsOnTheAnchorNotTheListItem($class)
	{
		$xpath = $this->renderXPath($class, array('items' => $this->items()));

		$this->assertNodeCount($xpath, '//a[contains(@class, "active")]', 1, $class);
		$this->assertNodeCount($xpath, '//li[contains(@class, "active")]', 0, $class);
	}

	/**
	 * @test
	 * @dataProvider menuWidgets
	 *
	 * @param string $class
	 */
	public function disabledStateSitsOnTheAnchorAndIsAnnounced($class)
	{
		$xpath = $this->renderXPath($class, array('items' => $this->items()));

		$link = $this->firstNode($xpath, '//a[contains(@class, "disabled")]');
		$this->assertEquals('true', $link->getAttribute('aria-disabled'), $class);
		$this->assertNodeCount($xpath, '//li[contains(@class, "disabled")]', 0, $class);
	}

	/**
	 * Bootstrap draws the dropdown caret from .dropdown-toggle::after; an explicit span renders
	 * a second, misplaced triangle next to it.
	 *
	 * @test
	 */
	public function dropdownTogglesEmitNoCaretElement()
	{
		$html = $this->render('TbMenu', array('items' => array(
			array('label' => 'Parent', 'url' => '#', 'active' => false, 'items' => array(
				array('label' => 'Child', 'url' => array('/child'), 'active' => false),
			)),
		)));

		$this->assertStringContainsString('dropdown-toggle', $html);
		$this->assertStringNotContainsString('caret', $html);
	}

	/**
	 * A divider was a styled empty <li> in Bootstrap 3 and is a real rule element now.
	 *
	 * @test
	 */
	public function dividersRenderAsRuleElements()
	{
		$xpath = $this->renderXPath('TbDropdown', array('items' => array(
			array('label' => 'First', 'url' => array('/one'), 'active' => false),
			'---',
			array('label' => 'Second', 'url' => array('/two'), 'active' => false),
		)));

		$this->assertNodeCount($xpath, '//li/hr[contains(@class, "dropdown-divider")]', 1);
	}

	/**
	 * Dropdown links are normally focusable in Bootstrap 5; Bootstrap 3 marked every one
	 * tabindex="-1", which took the whole menu out of the keyboard tab order.
	 *
	 * @test
	 */
	public function enabledDropdownLinksStayInTheTabOrder()
	{
		$xpath = $this->renderXPath('TbDropdown', array('items' => array(
			array('label' => 'First', 'url' => array('/one'), 'active' => false),
		)));

		$link = $this->firstNode($xpath, '//a');
		$this->assertEquals('', $link->getAttribute('tabindex'));
	}

	/**
	 * @test
	 */
	public function stackedNavUsesFlexUtility()
	{
		$html = $this->render('TbMenu', array('items' => $this->items(), 'stacked' => true));

		$this->assertStringContainsString('flex-column', $html);
		$this->assertStringNotContainsString('nav-stacked', $html);
	}
}
