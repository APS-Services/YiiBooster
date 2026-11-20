<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

require_once(__DIR__ . '/../../fakes/WidgetTestCase.php');
require_once(__DIR__ . '/../../../src/widgets/TbWidget.php');
require_once(__DIR__ . '/../../../src/widgets/TbAlert.php');

/**
 * Concrete stand-in, since TbWidget is abstract and exposes its context helpers as protected.
 */
class ContextProbeWidget extends TbWidget
{
	public function publicIsValidContext($context = false)
	{
		return $this->isValidContext($context);
	}

	public function publicGetContextClass($context = false)
	{
		return $this->getContextClass($context);
	}
}

/**
 * Contextual state names survive the Bootstrap 5 migration; the CSS classes they map to do not.
 *
 * Bootstrap 5 has no `-default` variant, so `default` now renders as `secondary`. Keeping the
 * name valid means application code passing 'default' - or TbButton::CTX_DEFAULT - keeps working
 * and renders something sensible, rather than emitting a class Bootstrap has never heard of.
 */
class TbWidgetContextTest extends WidgetTestCase
{
	/**
	 * @return array
	 */
	public function contexts()
	{
		return array(
			// context name, expected CSS suffix
			array('default', 'secondary'),
			array('primary', 'primary'),
			array('secondary', 'secondary'),
			array('success', 'success'),
			array('info', 'info'),
			array('warning', 'warning'),
			array('danger', 'danger'),
			array('light', 'light'),
			array('dark', 'dark'),
		);
	}

	/**
	 * @test
	 * @dataProvider contexts
	 *
	 * @param string $context
	 * @param string $expectedClass
	 */
	public function contextNameMapsToItsBootstrap5Class($context, $expectedClass)
	{
		$widget = new ContextProbeWidget(Yii::app()->getController());

		$this->assertTrue(
			$widget->publicIsValidContext($context),
			"'{$context}' should be an accepted context name."
		);
		$this->assertEquals($expectedClass, $widget->publicGetContextClass($context));
	}

	/**
	 * @test
	 */
	public function legacyDefaultContextNoLongerEmitsABootstrap3Class()
	{
		$widget = new ContextProbeWidget(Yii::app()->getController());

		$this->assertNotEquals(
			'default',
			$widget->publicGetContextClass(TbWidget::CTX_DEFAULT),
			'Bootstrap 5 has no -default variant; it must map to something that exists.'
		);
	}

	/**
	 * Subclasses add their own names on top - TbAlert maps `error` onto `danger` - and that
	 * indirection is exactly what let `default` be remapped without breaking callers.
	 *
	 * @test
	 */
	public function subclassContextsStillResolve()
	{
		$alert = new TbAlert(Yii::app()->getController());
		$method = new ReflectionMethod($alert, 'getContextClass');
		$method->setAccessible(true);

		$this->assertEquals('danger', $method->invoke($alert, TbAlert::CTX_ERROR));
		$this->assertEquals('secondary', $method->invoke($alert, TbAlert::CTX_DEFAULT));
	}

	/**
	 * @test
	 */
	public function unknownContextIsRejected()
	{
		$widget = new ContextProbeWidget(Yii::app()->getController());

		$this->assertFalse($widget->publicIsValidContext('mauve'));
	}
}
