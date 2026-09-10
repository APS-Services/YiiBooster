<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../fakes/AssetsRegistryHook.php');
require_once(__DIR__ . '/../../../src/widgets/TbModal.php');

/**
 * TbModal is the one Bootstrap 5 JS conversion with a real semantic change rather than a
 * renamed call.
 *
 * Bootstrap 3's `$el.modal(options)` constructed the modal AND opened it unless `show: false`
 * was passed - which is how TbModal implemented $autoOpen. Bootstrap 5 removed the `show`
 * option outright: constructing never opens, and show() is a separate call. Had the conversion
 * been a mechanical rename, every modal on the page would have silently stopped opening (or,
 * for autoOpen modals, stopped opening on load).
 */
class TbModalTest extends WidgetTestCase
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
	 * @param array $properties
	 * @return string all script bodies the widget registered, concatenated.
	 */
	protected function scriptFor(array $properties)
	{
		$this->render('TbModal', $properties);

		$reflection = new ReflectionProperty('CClientScript', 'scripts');
		$reflection->setAccessible(true);
		$scripts = $reflection->getValue($this->clientScript);

		$flat = '';
		foreach ((array) $scripts as $position) {
			foreach ((array) $position as $body) {
				$flat .= $body . "\n";
			}
		}

		return $flat;
	}

	/**
	 * @test
	 */
	public function initialisesThroughTheBootstrap5Helper()
	{
		$script = $this->scriptFor(array('id' => 'my-modal'));

		$this->assertStringContainsString('Booster.modal(', $script);
		$this->assertStringContainsString("'my-modal'", $script);
	}

	/**
	 * @test
	 */
	public function doesNotUseTheRemovedJqueryPluginApi()
	{
		$script = $this->scriptFor(array('id' => 'my-modal'));

		$this->assertStringNotContainsString(').modal(', $script);
	}

	/**
	 * @test
	 */
	public function autoOpenFalseDoesNotOpenTheModal()
	{
		$script = $this->scriptFor(array('id' => 'm1', 'autoOpen' => false));

		$this->assertRegExp('/Booster\.modal\([^)]*,\s*false\s*\)/', $script);
	}

	/**
	 * @test
	 */
	public function autoOpenTrueOpensTheModal()
	{
		$script = $this->scriptFor(array('id' => 'm2', 'autoOpen' => true));

		$this->assertRegExp('/Booster\.modal\([^)]*,\s*true\s*\)/', $script);
	}

	/**
	 * A `show` passed through options used to be the only way to control this. It still works,
	 * but is translated rather than forwarded, since Bootstrap 5 would ignore it.
	 *
	 * @test
	 */
	public function legacyShowOptionIsTranslatedIntoAutoOpen()
	{
		$script = $this->scriptFor(array('id' => 'm3', 'options' => array('show' => true)));

		$this->assertRegExp('/Booster\.modal\([^)]*,\s*true\s*\)/', $script);
		$this->assertStringNotContainsString('"show"', $script, 'show is not a Bootstrap 5 modal option.');
	}

	/**
	 * @test
	 */
	public function otherOptionsAreStillForwarded()
	{
		$script = $this->scriptFor(array(
			'id' => 'm4',
			'options' => array('backdrop' => 'static', 'keyboard' => false),
		));

		$this->assertStringContainsString('backdrop', $script);
		$this->assertStringContainsString('static', $script);
	}
}
