<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../../src/components/Booster.php');

/**
 * Main tests for initialization of Booster component
 */
class BoosterTest extends PHPUnit_Framework_TestCase {

	public function testInstantiate() {
		
		$component = new Booster();
		$this->assertInstanceOf('Booster', $component);
	}

	/**
	 * @test
	 */
	public function CanInitWithoutContext() {
		
		$component = new Booster();
		$component->init();
	}

	public function BootstrapCssFilenames() {
		
		// Keep this in step with Booster::createBootstrapCssPackage() and with the `bootstrap.js`
		// package in src/components/packages.php - see cdnCssAndJsAgreeOnBootstrapVersion() below.
		$cdn_url = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist';
		$local_url = 'assets'; // make sure it's equal to `assetsUrl` defined in relevant test!
		return array(
			// $cdn, $responsive, $fontawesome, $mincss, $expected_filename

			// Note that CDN hosts only responsive, minified variants.
			// array(true, true, true, true, "{$cdn_url}/css/bootstrap-combined.no-icons.min.css"),
			// array(true, true, true, false, "{$local_url}/bootstrap/css/bootstrap.no-icons.css"),
			// array(true, true, false, true, "{$cdn_url}/css/bootstrap-combined.min.css"),
			// array(true, true, false, false, "{$local_url}/bootstrap/css/bootstrap.css"),

			// CDN does not host non-responsive variants
			// array(true, false, true, true, "{$local_url}/bootstrap/css/bootstrap.no-responsive.no-icons.min.css"),
			// array(true, false, true, false, "{$local_url}/bootstrap/css/bootstrap.no-responsive.no-icons.css"),
			// array(true, false, false, true, "{$local_url}/bootstrap/css/bootstrap.no-responsive.min.css"),
			// array(true, false, false, false, "{$local_url}/bootstrap/css/bootstrap.no-responsive.css"),

			// Local
			// array(false, true, true, true, "{$local_url}/bootstrap/css/bootstrap.no-icons.min.css"),
			// array(false, true, true, false, "{$local_url}/bootstrap/css/bootstrap.no-icons.css"),
			array(true, true, false, true, "{$cdn_url}/css/bootstrap.min.css"),
			array(true, true, false, false, "{$cdn_url}/css/bootstrap.css"),
			array(false, true, false, true, "{$local_url}/bootstrap/css/bootstrap.min.css"),
			array(false, true, false, false, "{$local_url}/bootstrap/css/bootstrap.css"),

			// Same as if $enableCdn=true, because CDN does not host non-responsive variants.
			// array(false, false, true, true, "{$local_url}/bootstrap/css/bootstrap.no-responsive.no-icons.min.css"),
			// array(false, false, true, false, "{$local_url}/bootstrap/css/bootstrap.no-responsive.no-icons.css"),
			// array(false, false, false, true, "{$local_url}/bootstrap/css/bootstrap.no-responsive.min.css"),
			// array(false, false, false, false, "{$local_url}/bootstrap/css/bootstrap.no-responsive.css"),
		);
	}

	/**
	 * @test
	 * @dataProvider BootstrapCssFilenames
	 *
	 * @param $cdn
	 * @param $responsive
	 * @param $fontawesome
	 * @param $mincss
	 * @param $expected_filename
	 */
	public function UsesBootstrapCssDependingOnSwitches($cdn, $responsive, $fontawesome, $mincss, $expected_filename) {
		
		$component = new Booster();
		$component->_assetsUrl = 'assets';
		$component->cs = new AssetsRegistryHook();

		$component->enableCdn = $cdn;
		$component->responsiveCss = $responsive;
		$component->fontAwesomeCss = $fontawesome;
		$component->minify = $mincss;

		$component->init();
		$component->registerBootstrapCss();

		$this->assertTrue(
			$component->cs->hasRegisteredCssFile($expected_filename)
		);
	}

	/**
	 * @param bool $enableCdn
	 * @return Booster an initialized component with its package graph built.
	 */
	protected function makeInitializedComponent($enableCdn = false)
	{
		$component = new Booster();
		$component->_assetsUrl = 'assets';
		$component->cs = new AssetsRegistryHook();
		$component->enableCdn = $enableCdn;
		$component->init();

		return $component;
	}

	/**
	 * Bootstrap 5 dropped its own jQuery dependency, which makes it tempting to drop this edge -
	 * an earlier migration attempt did exactly that. It has to stay: about twenty packages declare
	 * `'depends' => array('bootstrap.js')` and rely on it to pull jQuery in first, as do Yii's own
	 * CActiveForm and CGridView client scripts. Removing it is a script-ordering bug that only
	 * shows up in production.
	 *
	 * @test
	 */
	public function bootstrapJsPackageStillPullsInJquery()
	{
		$packages = $this->makeInitializedComponent()->packages;

		$this->assertArrayHasKey('bootstrap.js', $packages);
		$this->assertContains(
			'jquery',
			$packages['bootstrap.js']['depends'],
			'bootstrap.js must keep depending on jquery even though Bootstrap 5 itself does not.'
		);
	}

	/**
	 * Before 5.0 the CDN branches shipped Bootstrap 3.2.0 CSS against 3.3.2 JS, from a host that
	 * no longer resolves. Nothing caught it because the two URLs live in different files -
	 * packages.php for the JS, Booster::createBootstrapCssPackage() for the CSS.
	 *
	 * @test
	 */
	public function cdnCssAndJsAgreeOnBootstrapVersion()
	{
		$packages = $this->makeInitializedComponent(true)->packages;

		// Matches both the jsdelivr form (bootstrap@5.3.3) and the old maxcdn one (bootstrap/3.3.2).
		$pattern = '#bootstrap[@/](\d+\.\d+\.\d+)#';

		$this->assertRegExp($pattern, $packages['bootstrap.js']['baseUrl']);
		$this->assertRegExp($pattern, $packages['bootstrap.css']['baseUrl']);

		preg_match($pattern, $packages['bootstrap.js']['baseUrl'], $js);
		preg_match($pattern, $packages['bootstrap.css']['baseUrl'], $css);

		$this->assertEquals(
			$css[1],
			$js[1],
			'CDN-hosted Bootstrap CSS and JS must be the same version.'
		);
	}

	/**
	 * The local assets must not drift from the CDN version either.
	 *
	 * @test
	 */
	public function bundledAssetsMatchTheAdvertisedCdnVersion()
	{
		$packages = $this->makeInitializedComponent(true)->packages;
		preg_match('#bootstrap[@/](\d+\.\d+\.\d+)#', $packages['bootstrap.css']['baseUrl'], $cdn);

		$bundled = file_get_contents(dirname(dirname(dirname(__DIR__))) . '/src/assets/bootstrap/css/bootstrap.min.css');

		$this->assertStringContainsString(
			'Bootstrap  v' . $cdn[1],
			substr($bundled, 0, 200),
			'src/assets/bootstrap/ is not the version the CDN branch points at.'
		);
	}

	/**
	 * Bootstrap 5 needs Popper for tooltips, popovers and dropdowns. We ship the bundle build
	 * rather than adding a separate Popper package, so the filename matters.
	 *
	 * @test
	 */
	public function bootstrapJsPackageShipsThePopperBundle()
	{
		$packages = $this->makeInitializedComponent()->packages;

		$this->assertStringContainsString(
			'bootstrap.bundle',
			$packages['bootstrap.js']['js'][0],
			'Without the bundle build, Popper is missing and dropdowns/tooltips/popovers break.'
		);
	}
}
