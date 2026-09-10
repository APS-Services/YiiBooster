# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

YiiBooster is a widget library (not an application) for **Yii 1.1**, wrapping Twitter Bootstrap 3 plus a pile of jQuery
plugins. Everything under `src/` is what end users install; the repo root is development scaffolding (build, tests, docs).
Targets **PHP 5.3** and Yii >= 1.1.15 — no namespaces, no short array syntax in `src/`, PHPUnit pinned to 4.8 because
it is the last version supporting PHP 5.3.

## Commands

**The dev toolchain runs on PHP 7.4, not the system PHP.** `src/` still targets PHP 5.3 syntax (that is the
shipped artifact), but the test/build tooling is installed against 7.4 — the original PHPUnit 4.8 pin cannot
install on a modern PHP, and every PHPUnit release up to 7.5.20 is blocked by a security advisory. Prefix
Composer and PHPUnit invocations with `php7.4` (`composer` is a phar at `/usr/local/bin/composer`):

```shell
php7.4 /usr/local/bin/composer install
```

Tests — PHPUnit is run from inside `tests/` so `phpunit.xml`'s relative paths resolve:

```shell
cd tests && php7.4 ../vendor/bin/phpunit                     # whole suite
cd tests && php7.4 ../vendor/bin/phpunit unit/widgets/TbAlertTest.php   # single file
cd tests && php7.4 ../vendor/bin/phpunit --filter onInitWhenNoAlertsSetAllDefined   # single test
```

The suite runs on PHPUnit 8.5. `tests/bootstrap.php` aliases `PHPUnit\Framework\TestCase` to the old
`PHPUnit_Framework_TestCase` name, so tests are still written in the underscored style. Deprecation warnings
from `assertAttributeEquals` are expected and do not fail the build; those assertions are removed in PHPUnit 9
and will need replacing before any further upgrade.

`phploc`, `phpcpd` and `pdepend` are no longer Composer dev-dependencies — their `sebastian/*` requirements are
version-locked to a PHPUnit major and conflict with ours. They are listed under `suggest`; install them
standalone if you need those `phing` report targets.

Phing drives everything else (`build.xml`, config in `build/build.properties`):

```shell
./vendor/bin/phing            # = `dist`, builds end-user bundle into dist/ named after project.version
./vendor/bin/phing check      # phploc, phpcpd, test (w/ coverage), phpmd, phpcs, pdepend, codebrowser -> reports/
./vendor/bin/phing phpcs      # code style alone, ruleset in build/ruleset.xml
./vendor/bin/phing doc        # apigen API docs + pinocchio annotated sources -> doc/
./vendor/bin/phing clean      # removes dist/, doc/, reports/
```

`check` needs XDebug: without a coverage report PDepend fails too.

## Architecture

**The `Booster` application component is the hub.** `src/components/Booster.php` must be registered in the host app's
config under the component name `booster`. Its `init()`:

1. stores itself in a static singleton (`Booster::setBooster($this)`),
2. sets the `booster` path alias to `src/` if undefined,
3. merges the built-in client-script packages with user-supplied `$packages` and pushes them all into
   `CClientScript::addPackage()`,
4. registers the core CSS/JS packages according to the many boolean flags (`enableCdn`, `minify`, `coreCss`,
   `fontAwesomeCss`, `enableBootboxJS`, …).

It bails out early under CLI unless the constant `IS_IN_TESTS` is defined — that constant exists purely so the test
bootstrap can exercise asset registration from the console.

**Widgets never touch `Yii::app()` for the component.** They call `Booster::getBooster()`, which returns the singleton or
falls back to looking for a `booster` component on the current module, then the application. That is the seam to be aware
of when a widget's assets don't show up.

**Asset packages** live in `src/components/packages.php` — a plain PHP file `require`d with `$this` bound to the Booster
instance, so each entry can branch on `$this->enableCdn` / `$this->minify` / `$this->getAssetsUrl()`. Three packages
(`bootstrap.css`, `select2`, `chosen`) are built in Booster methods instead because they need more logic. The actual
vendored JS/CSS lives in `src/assets/<plugin>/` and is published wholesale via `CAssetManager::publish()`; adding a
third-party plugin means dropping files in `src/assets/` *and* adding a package entry.

**Widget class hierarchy** (all classes prefixed `Tb`, all in the flat `src/widgets/` directory, referenced from views as
`booster.widgets.TbFoo`):

- `TbWidget` (abstract, extends `CWidget`) — contextual state constants (`CTX_PRIMARY`, `CTX_DANGER`, …) plus
  `addCssClass()`/`getContextClass()` helpers. Most display widgets extend this.
- `TbBaseInputWidget` (extends `CInputWidget`) — adds the `ct-form-control` class and a default placeholder from the
  model attribute label. Datepickers, Select2, colorpicker etc. extend this.
- `src/widgets/input/TbInput*` is the older Bootstrap-2-era form-layout hierarchy (vertical/horizontal/inline/search),
  used by `TbForm`/`CForm` integration. New work generally goes through `TbActiveForm` instead.

**`TbActiveForm`** (extends `CActiveForm`) is the main form API: dozens of `xxxGroup($model, $attribute, $options)`
methods. Most are one-liners delegating to `widgetGroupInternal('booster.widgets.TbSomeWidget', ...)`, so wiring a new
input widget into forms is usually adding one such wrapper.

**Grids** are the heaviest part: `TbGridView` extends `CGridView`, and `TbExtendedGridView`, `TbGroupGridView` and
`TbJsonGridView` (client-side rendering via jQote2 templates) each extend `TbGridView`, plus a family of column classes
(`Tb*Column`). `TbExtendedGridView` also defines the `TbOperation` family (sum/count/percent summary rows) inline in the
same file. Server-side counterparts for the interactive columns live in `src/actions/` (`TbToggleAction`,
`TbSortableAction`, `TbExtendedTooltipAction`) and are wired into the host app's controller `actions()`.

Other pieces: `src/filters/BoosterFilter.php` loads the component per-action instead of preloading it;
`TbEditableSaver` handles the X-Editable save round-trip with model rule/safe-attribute checks; `src/gii/` ships a Gii
CRUD generator emitting Booster markup; `src/helpers/TbHtml.php` exists only for `yii-auth` compatibility (see
`src/helpers/README.md`) and is not the canonical helper.

## Tests

`tests/bootstrap.php` spins up a `MinimalApplication` (a `CApplication` subclass in `tests/fakes/`) with the alias
`bootstrap` pointing at `src/` (the `booster` alias is set by `Booster::init()` itself), a real `CAssetManager` writing
into `tests/runtime/assets/`, and the Booster component attached — note it is attached under the name `bootstrap` there,
which works only because `init()` registers the singleton eagerly. `tests/fakes/AssetsRegistryHook.php` is a `CClientScript` double used to assert which
packages/scripts a widget registered.

Test files `require_once` the widget file (and its parent class file) directly at the top — there is no autoloading of
`src/` classes in the suite, so a new test must include the whole ancestor chain. Tests are plain
`PHPUnit_Framework_TestCase` with `@test` annotations rather than `test*` method names.

## Conventions

- Tabs for indentation, Unix line endings, no closing `?>`; `build/ruleset.xml` (php_codesniffer) is the authority.
- Docblocks use the `*## ClassName class file` heading style and `@package booster.widgets.<category>` — Apigen and the
  documentation site rely on those package tags.
- Per `CONTRIBUTING.md`, every change appends a line to `CHANGELOG.md` under the current development section:
  `- **(fix)** description #issue (username)` or `- **(enh)** …`. Work happens on feature branches off `master`.
- The release version lives in `build/build.properties` (`project.version`), not in composer.json.
