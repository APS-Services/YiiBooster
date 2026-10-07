<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../fakes/AssetsRegistryHook.php');
require_once(__DIR__ . '/../../../src/widgets/TbTags.php');
require_once(__DIR__ . '/../../../src/widgets/TbColorPicker.php');
require_once(__DIR__ . '/../../../src/widgets/TbTabs.php');

/**
 * Regression tests for the Bootstrap 5 plugin replacements, covering the defects a code review
 * found after the swap - each of which was a case of the plugin call being replaced while the
 * markup or options it depended on were left behind.
 */
class PluginReplacementTest extends WidgetTestCase
{
	/**
	 * @var AssetsRegistryHook
	 */
	protected $clientScript;

	protected function setUp(): void
	{
		parent::setUp();

		$this->clientScript = new AssetsRegistryHook();
		Yii::app()->setComponent('clientScript', $this->clientScript);
	}

	/**
	 * @return string every script body the widget registered.
	 */
	protected function registeredScripts()
	{
		$reflection = new ReflectionProperty('CClientScript', 'scripts');
		$reflection->setAccessible(true);

		$flat = '';
		foreach ((array) $reflection->getValue($this->clientScript) as $position) {
			foreach ((array) $position as $body) {
				$flat .= $body . "\n";
			}
		}

		return $flat;
	}

	/**
	 * bootstrap-tags decorated an empty div and kept the value in a sibling hidden field it
	 * synchronised itself. Select2 is select-backed - it reads and writes the <select>'s options -
	 * so pointing it at the old div left it with nothing to bind to and the value never updated.
	 *
	 * @test
	 */
	public function tagsRendersAMultipleSelectForSelect2()
	{
		$xpath = $this->renderXPath('TbTags', array('name' => 'Post[tags]'));

		$select = $this->firstNode($xpath, '//select');
		$this->assertNotEquals('', $select->getAttribute('multiple'), 'Select2 tags mode needs a multiple select.');
		$this->assertStringStartsWith('tags_', $select->getAttribute('id'));

		// The select must be the submitting control, not a decorative sibling of a hidden field.
		$this->assertNodeCount($xpath, '//input[@type="hidden"]', 0);
		$this->assertNodeCount($xpath, '//div[contains(@class, "tags")]', 0);
	}

	/**
	 * @test
	 */
	public function tagsInitialisesSelect2OnThatSameSelect()
	{
		$this->render('TbTags', array('name' => 'Post[tags]'));
		$script = $this->registeredScripts();

		$this->assertStringContainsString('.select2(', $script);
		$this->assertStringContainsString("jQuery('#tags_", $script);
		$this->assertStringNotContainsString('.tags(', $script, 'The bootstrap-tags plugin is gone.');
	}

	/**
	 * @test
	 */
	public function tagsPreSelectsExistingValues()
	{
		$xpath = $this->renderXPath('TbTags', array(
			'name' => 'Post[tags]',
			'tagData' => array('yii', 'bootstrap'),
		));

		$this->assertNodeCount($xpath, '//select/option[@selected]', 2);
		$this->assertNodeCount($xpath, '//select/option[@value="yii"]', 1);
		$this->assertNodeCount($xpath, '//select/option[@value="bootstrap"]', 1);
	}

	/**
	 * A plain text column holding "a, b" has to work as well as an array.
	 *
	 * @test
	 */
	public function tagsAcceptsACommaSeparatedValue()
	{
		$xpath = $this->renderXPath('TbTags', array(
			'name' => 'Post[tags]',
			'value' => 'yii, bootstrap ,  php',
		));

		$this->assertNodeCount($xpath, '//select/option', 3);
		$this->assertNodeCount($xpath, '//select/option[@value="php"]', 1);
	}

	/**
	 * @return array
	 */
	public function colorFormats()
	{
		return array(
			// configured format, expected Coloris format, expected alpha flag
			'hex'   => array('hex', 'hex', 'false'),
			'rgb'   => array('rgb', 'rgb', 'false'),
			'hsl'   => array('hsl', 'hsl', 'false'),
			// rgba is not a Coloris format; it must become rgb with alpha enabled.
			'rgba'  => array('rgba', 'rgb', 'true'),
		);
	}

	/**
	 * Coloris accepts hex, rgb, hsl, auto or mixed. `rgba` was valid under
	 * bootstrap-colorpicker, and passing it straight through reaches a format radio that does not
	 * exist, throwing as the picker opens.
	 *
	 * @test
	 * @dataProvider colorFormats
	 *
	 * @param string $configured
	 * @param string $expectedFormat
	 * @param string $expectedAlpha
	 */
	public function colorPickerTranslatesFormatsColorisUnderstands($configured, $expectedFormat, $expectedAlpha)
	{
		$this->render('TbColorPicker', array('name' => 'Post[colour]', 'format' => $configured));
		$script = $this->registeredScripts();

		// CJavaScript::encode emits single-quoted keys, not JSON.
		$this->assertStringContainsString("'format':'" . $expectedFormat . "'", $script);
		$this->assertStringContainsString("'alpha':" . $expectedAlpha, $script);
		$this->assertStringNotContainsString("'format':'rgba'", $script);
	}

	/**
	 * `flex-column` alone only sets flex-direction; without d-flex the wrapper is still a block,
	 * so side placement rendered identically to the default.
	 *
	 * @test
	 */
	public function sideTabPlacementProducesAFlexRow()
	{
		$xpath = $this->renderXPath('TbTabs', array(
			'placement' => TbTabs::PLACEMENT_LEFT,
			'tabs' => array(array('label' => 'One', 'content' => 'first', 'active' => true)),
		));

		$wrapper = $this->firstNode($xpath, '//div[contains(@class, "d-flex")]');
		$this->assertStringContainsString('d-flex', $wrapper->getAttribute('class'));
		$this->assertStringNotContainsString('flex-row-reverse', $wrapper->getAttribute('class'));
	}

	/**
	 * @test
	 */
	public function rightTabPlacementReversesTheRow()
	{
		$xpath = $this->renderXPath('TbTabs', array(
			'placement' => TbTabs::PLACEMENT_RIGHT,
			'tabs' => array(array('label' => 'One', 'content' => 'first', 'active' => true)),
		));

		$wrapper = $this->firstNode($xpath, '//div[contains(@class, "d-flex")]');
		$this->assertStringContainsString('flex-row-reverse', $wrapper->getAttribute('class'));
	}

	/**
	 * Every library vendored under src/assets is redistributed by `phing dist`, so its licence
	 * notice has to travel with it.
	 *
	 * @test
	 */
	public function vendoredLibrariesCarryTheirLicence()
	{
		$root = dirname(dirname(dirname(__DIR__))) . '/src/assets/';

		// Libraries whose upstream ships no LICENSE file but carries the notice in the file
		// header instead - which satisfies MIT just as well.
		$noticeInHeader = array('daterangepicker' => 'daterangepicker.js');

		foreach (array('select2', 'easymde', 'quill', 'awesomplete', 'coloris', 'bootstrap-icons',
				'bootbox', 'bootstrap', 'daterangepicker') as $library) {

			$files = glob($root . $library . '/LICEN[CS]E*');

			if (!empty($files)) {
				continue;
			}

			$this->assertArrayHasKey(
				$library,
				$noticeInHeader,
				"Vendored library '{$library}' has no LICENSE file and is not recorded as carrying "
				. 'its notice in a file header.'
			);

			$header = substr(file_get_contents($root . $library . '/' . $noticeInHeader[$library]), 0, 600);
			$this->assertRegExp('/licen[cs]e/i', $header, $library);
			$this->assertRegExp('/copyright/i', $header, $library);
		}
	}
}
