<?php
/**
 * This is temporary harness to support current code which is tightly coupled to Yii application object.
 * It should be called once before each test, and instantiates our minimal CApplication object.
 */

define('ROOT_DIR', realpath(__DIR__ . '/../'));

// Included the Yii
define('YII_PATH', ROOT_DIR . '/vendor/yiisoft/yii/framework');

// disable Yii error handling logic
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

// Set up the shorthands for test app paths
define('APP_ROOT', ROOT_DIR . '/tests/runtime');
define('APP_RUNTIME', APP_ROOT . '/runtime'); // yes, second "runtime" directory inside
define('APP_ASSETS', APP_ROOT . '/assets');

is_dir(APP_RUNTIME) or mkdir(APP_RUNTIME);
is_dir(APP_ASSETS) or mkdir(APP_ASSETS);

// composer autoloader
require_once(ROOT_DIR . '/vendor/autoload.php');

// The suite was written against PHPUnit 4.8, which is unusable on any PHP we can install today
// (and every release up to 7.5.20 is blocked by a security advisory). We run PHPUnit 8.5, which
// dropped the underscored class names in favour of namespaced ones. Aliasing here keeps the
// existing test cases - and any new ones written in the same style - working unchanged.
if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
	class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

require_once(YII_PATH . '/YiiBase.php');
require_once(ROOT_DIR . '/tests/fakes/Yii.php');

YiiBase::$enableIncludePath = false;

// Instantiated the test app
require_once(ROOT_DIR . '/tests/fakes/MinimalApplication.php');

Yii::createApplication(
	'MinimalApplication',
	array(
		'basePath' => APP_ROOT,
		'runtimePath' => APP_RUNTIME,
		'aliases' => [
			'fakes' => ROOT_DIR . '/tests/fakes',
			'bootstrap' => ROOT_DIR . '/src',
			// Several widgets call Yii::import('booster.widgets.X') at file scope. In an
			// application that alias is set by Booster::init(), but the component here is lazy,
			// so a test that require_once's such a widget would fatal before anything ran.
			'booster' => ROOT_DIR . '/src',
		],
		'components' => array(
			'assetManager' => array(
				'basePath' => APP_ASSETS // do not forget to clean this folder sometimes
			),
			// INSTALL.md requires this component to be named `booster`: Booster::getBooster(),
			// which widgets use to reach it, looks it up under that name. The harness previously
			// registered it as `bootstrap`, so getBooster() returned null and any widget calling
			// it fatalled - which no test noticed, because nothing rendered a widget.
			'booster' => array(
				'class' => 'booster.components.Booster'
			),
		)
	)
);

// fix bug in yii's autoloader (https://github.com/yiisoft/yii/issues/1907)
Yii::import('fakes.*');

// See the `Boostrap.init()` method for explanation why it is needed
define('IS_IN_TESTS', true);
