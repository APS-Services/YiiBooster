<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../../src/widgets/TbBreadcrumbs.php');

/**
 * Expectations updated for Bootstrap 5: items carry breadcrumb-item, and the current one is
 * marked aria-current. Bootstrap 5 draws the separator from
 * `.breadcrumb-item + .breadcrumb-item::before`, so without those classes the trail renders as a
 * plain run-together list.
 */
class TbBreadcrumbsTest extends PHPUnit_Framework_TestCase
{
	private function makeWidget()
	{
		return new TbBreadcrumbs();
	}

	/**
	 * @param TbBreadcrumbs $widget
	 */
	private function runWidget($widget)
	{
		ob_start();
		$widget->run();
		return ob_get_clean();
	}

	public function testInstanceClassName()
	{
		$widget = $this->makeWidget();
		$this->assertInstanceOf('TbBreadcrumbs', $widget);
		$this->assertInstanceOf('CBreadcrumbs', $widget);
	}

	public function testDefaultAttributesValues()
	{
		$widget = $this->makeWidget();

		$this->assertAttributeEquals('ul', 'tagName', $widget);
		$this->assertAttributeEquals(array('class' => 'breadcrumb'), 'htmlOptions', $widget);
		$this->assertAttributeEquals('{label}', 'inactiveLinkTemplate', $widget);
		$this->assertAttributeEquals(' &raquo; ', 'separator', $widget);
	}

	public function testSeparatorInit()
	{
		$widget = $this->makeWidget();

		$this->assertAttributeEquals(' &raquo; ', 'separator', $widget);
	}

	public function testHomeLink()
	{
		// default home link
		$widget = $this->makeWidget();
		$widget->homeLink = null;
		$widget->links = array('test');
		$this->runWidget($widget);
		$defaultHomeLink = CHtml::link(Yii::t('zii', 'Home'), Yii::app()->homeUrl);
		$this->assertAttributeEquals($defaultHomeLink, 'homeLink', $widget);

		// no home link
		$widget = $this->makeWidget();
		$widget->homeLink = false;
		$widget->links = array('test');

		$content = $this->runWidget($widget);
		$actualHtml = new DOMDocument();
		$actualHtml->loadHTML($content);

		$expectedHtml = new DOMDocument();
		$expectedHtml->loadHTML('<ul class="breadcrumb"><li aria-current="page" class="breadcrumb-item active">test</li></ul>');

		$this->assertEquals($expectedHtml, $actualHtml);

		// home link as plain text
		$widget = $this->makeWidget();
		$widget->homeLink = 'foobar';
		$widget->links = array('test');

		$content = $this->runWidget($widget);
		$actualHtml = new DOMDocument();
		$actualHtml->loadHTML($content);

		$expectedHtml = new DOMDocument();
		$expectedHtml->loadHTML('<ul class="breadcrumb"><li aria-current="page" class="breadcrumb-item active">foobar</li><li aria-current="page" class="breadcrumb-item active">test</li></ul>');

		//echo $expectedHtml->saveHTML()."\n";
		//echo $actualHtml->saveHTML();

		$this->assertEquals($expectedHtml, $actualHtml);
	}

	public function testLinks()
	{
		// no output produced on empty links
		$widget = $this->makeWidget();
		$content = $this->runWidget($widget);
		$this->assertEmpty($content);

		// separator between links
		$widget = $this->makeWidget();
		$widget->homeLink = 'foobar';
		$widget->links = array('foo' => 'bar', 'end');

		$content = $this->runWidget($widget);
		$actualHtml = new DOMDocument();
		$actualHtml->loadHTML($content);

		$expectedHtml = new DOMDocument();
		$expectedHtml->loadHTML('<ul class="breadcrumb"><li aria-current="page" class="breadcrumb-item active">foobar</li><li class="breadcrumb-item"><a href="bar">foo</a></li><li aria-current="page" class="breadcrumb-item active">end</li></ul>');

		$this->assertEquals($expectedHtml, $actualHtml);
	}
};