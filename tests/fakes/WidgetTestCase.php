<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(dirname(__DIR__) . '/fakes/FakeController.php');

/**
 * Base class for tests that assert on a widget's *rendered output*.
 *
 * Before the Bootstrap 5 migration almost nothing in this suite rendered anything - the tests
 * inspected properties after init() instead. Since rendering markup is the entire purpose of this
 * library, that left its actual output untested.
 *
 * Subclasses assert on the parts of the output that must survive a Bootstrap version change -
 * ids, input names and values, item counts, htmlOptions passthrough, generated URLs - and NOT on
 * Bootstrap's own class names. Those change by design in this migration; asserting them would just
 * produce tests whose only possible outcome is "update the expectation".
 */
abstract class WidgetTestCase extends PHPUnit_Framework_TestCase
{
	protected function setUp(): void
	{
		$_SERVER['REQUEST_URI'] = 'test';
		$controller = new FakeController('fake');
		// Widgets that build URLs (pagers, grids) end up in CController::createUrl('') which
		// resolves the empty route via getAction()->getId(). Without a current action that is a
		// fatal, so the fake controller needs one.
		$controller->setAction(new CInlineAction($controller, 'index'));
		Yii::app()->setController($controller);
	}

	/**
	 * Renders a widget and returns its HTML.
	 *
	 * @param string $class widget class name, already require_once'd by the caller.
	 * @param array $properties widget properties.
	 * @return string
	 */
	protected function render($class, array $properties = array())
	{
		$widget = new $class(Yii::app()->getController());
		foreach ($properties as $name => $value) {
			$widget->$name = $value;
		}

		ob_start();
		try {
			$widget->init();
			$widget->run();
		} catch (Exception $e) {
			ob_end_clean();
			throw $e;
		}

		return ob_get_clean();
	}

	/**
	 * Renders a widget and returns an XPath handle over its markup.
	 *
	 * @param string $class
	 * @param array $properties
	 * @return DOMXPath
	 */
	protected function renderXPath($class, array $properties = array())
	{
		return $this->xpath($this->render($class, $properties));
	}

	/**
	 * @param string $html
	 * @return DOMXPath
	 */
	protected function xpath($html)
	{
		$document = new DOMDocument();
		// Widget output is a fragment, so libxml complains about the missing doctype/root element
		// and about HTML5 elements it does not know. Neither is a problem for querying.
		$previous = libxml_use_internal_errors(true);
		$document->loadHTML('<div id="test-root">' . $html . '</div>');
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		return new DOMXPath($document);
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $query
	 * @param int $expected
	 * @param string $message
	 */
	protected function assertNodeCount(DOMXPath $xpath, $query, $expected, $message = '')
	{
		$nodes = $xpath->query($query);
		$this->assertEquals(
			$expected,
			$nodes->length,
			$message !== '' ? $message : "Expected {$expected} node(s) matching: {$query}"
		);
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $query
	 * @return DOMElement
	 */
	protected function firstNode(DOMXPath $xpath, $query)
	{
		$nodes = $xpath->query($query);
		$this->assertGreaterThan(0, $nodes->length, "No node matched: {$query}");

		return $nodes->item(0);
	}
}
