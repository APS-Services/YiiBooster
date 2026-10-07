# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

YiiBooster is a widget library (not an application) for **Yii 1.1**, wrapping **Bootstrap 5.3.3** plus a handful of
frontend plugins. Everything under `src/` is what end users install; the repo root is development scaffolding (build,
tests, docs). `src/` targets **PHP 5.3** syntax and Yii >= 1.1.15 — no namespaces, no short array syntax. The dev
toolchain does not (see Commands).

The current version is **5.0.0** (unreleased), the Bootstrap 5 migration. It is a breaking release: widgets emit
Bootstrap 5 markup, with no Bootstrap 3 shims and no config switch. `UPGRADE-5.0.md` is the migration guide and is the
place to record anything that changes a consumer's markup or API — it has a separate path for applications coming from
YiiBooster 3.x, which was Bootstrap 2-based.

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
./vendor/bin/phing check      # test, phpmd, phpcs -> reports/ (the phploc/phpcpd/pdepend/codebrowser
                              # targets fail unless you install those tools standalone; see above)
./vendor/bin/phing phpcs      # code style alone, ruleset in build/ruleset.xml
./vendor/bin/phing doc        # apigen + pinocchio -> doc/ (both are `suggest`-only; install standalone)
./vendor/bin/phing clean      # removes dist/, doc/, reports/
```

Coverage needs XDebug, which is not installed here either — so `check` produces no coverage report by default. The
reliable gate is the PHPUnit suite plus the token lint; `.github/workflows/tests.yml` runs exactly that.

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
instance, so each entry can branch on `$this->enableCdn` / `$this->minify` / `$this->getAssetsUrl()`. Two packages
(`bootstrap.css`, `select2`) are built in Booster methods instead because they need more logic. The actual vendored
JS/CSS lives in `src/assets/<plugin>/` and is published wholesale via `CAssetManager::publish()`; adding a third-party
plugin means dropping files in `src/assets/` *and* adding a package entry.

Two rules that are easy to get wrong here. **Local and CDN filenames must match**, because both branches of a package
resolve the same relative paths — renaming a file on import silently breaks `enableCdn` only. And **`minify` defaults to
true**, so a stale or unpatched `.min.*` next to a patched source file is what production will actually serve; where a
vendored library has been patched in place (x-editable), only the unminified build is shipped for exactly that reason.

**Two of our own runtime pieces**, both worth knowing before adding anything:

- `src/helpers/TbIcon.php` renders every icon. Bootstrap has shipped no icons since 3.x, so Bootstrap Icons is bundled
  and `TbIcon` translates the old Glyphicons/Bootstrap 2 names onto it. Widgets call `TbIcon::render($icon)` — never
  build an icon element by hand.
- `src/assets/js/booster.js` holds the client-side helpers (`Booster.*`): tooltip/popover instance lifecycle, component
  construction, and the validation-state bridge. Bootstrap 5 does still install its jQuery plugins when jQuery is
  present, but only from `DOMContentLoaded` and with no way to dispose an instance — which is why widgets construct
  components explicitly through these helpers rather than through `$el.modal()` and friends.

**Widget class hierarchy** (all classes prefixed `Tb`, all in the flat `src/widgets/` directory, referenced from views as
`booster.widgets.TbFoo`):

- `TbWidget` (abstract, extends `CWidget`) — contextual state constants (`CTX_PRIMARY`, `CTX_DANGER`, …) plus
  `addCssClass()`/`getContextClass()` helpers. Most display widgets extend this.
- `TbBaseInputWidget` (extends `CInputWidget`) — adds the `ct-form-control` class and a default placeholder from the
  model attribute label. Datepickers, Select2, colorpicker etc. extend this.
- `src/widgets/input/TbInput*` are **removal stubs**, not a form hierarchy. They were Bootstrap 2 markup and were
  already unreachable: the `CForm` path runs `TbForm` → `TbFormInputElement` → `TbActiveForm`'s `*Group()` methods and
  never touched them.

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
CRUD generator emitting Booster markup — worth remembering that it generates into *user* projects, so stale markup there
propagates into every new scaffold.

**Removed widgets keep a throwing stub** rather than being deleted outright. Yii 1.1 resolves widgets by path, so a
missing file gives only `include(TbFoo.php): failed to open stream`; a stub whose `init()` throws a `CException` naming
the replacement reports at the line that used the widget instead. Follow that pattern for any further removal. The
existing stubs are scheduled for deletion in 5.1. Removed *behaviour* (as opposed to a removed class) throws too — see
`TbButton::$toggle` — rather than silently no-op'ing.

## Tests

`tests/bootstrap.php` spins up a `MinimalApplication` (a `CApplication` subclass in `tests/fakes/`) with both the
`bootstrap` and `booster` aliases pointing at `src/`, a real `CAssetManager` writing into `tests/runtime/assets/`, and
the Booster component attached **under the name `booster`** — which is what `Booster::getBooster()` looks up, so
registering it as anything else leaves that returning `null` and any widget needing it fails.

Both aliases are set because several widgets call `Yii::import('booster.widgets.X')` at file scope, and the component
that normally defines that alias is lazy — so a test that `require_once`'s such a widget would fatal before running.

Three test helpers:

- `tests/fakes/WidgetTestCase.php` — base class for tests that assert on *rendered* output, with `render()` /
  `renderXPath()`. Assert the things that must survive a framework change (ids, input names, counts, `htmlOptions`
  passthrough), not Bootstrap's own class names.
- `tests/fakes/AssetsRegistryHook.php` — a `CClientScript` double for asserting which packages/scripts a widget
  registered.
- `tests/unit/Bootstrap5TokenLintTest.php` + `tests/bs-token-lint.php` + `tests/bs-token-baseline.php` — scans `src/`
  for Bootstrap 2/3 markup and asserts the tree matches the recorded baseline **exactly**, failing both on regression
  and on progress. The baseline is currently empty and should stay that way. Regenerate with
  `php7.4 tests/update-bs-token-baseline.php` after genuinely removing legacy markup — never to silence a new hit. The
  scanner strips PHP comments first, so docblocks may name old class names freely.

Test files `require_once` the widget file (and its parent class file) directly at the top — there is no autoloading of
`src/` classes in the suite, so a new test must include the whole ancestor chain. Tests are plain
`PHPUnit_Framework_TestCase` with `@test` annotations rather than `test*` method names.

## Conventions

- Tabs for indentation, Unix line endings, no closing `?>`. `build/ruleset.xml` (php_codesniffer) is nominally the
  authority, but `src/` has never satisfied it — thousands of mostly-whitespace violations predate any current work, so
  CI runs phpcs advisory rather than gating. Match the surrounding file; don't reformat on the way past.
- Docblocks use the `*## ClassName class file` heading style and `@package booster.widgets.<category>` — Apigen and the
  documentation site rely on those package tags.
- Per `CONTRIBUTING.md`, every change appends a line to `CHANGELOG.md` under the current development section:
  `- **(fix)** description #issue (username)` or `- **(enh)** …`. Work happens on feature branches off `master`.
- The release version lives in `build/build.properties` (`project.version`), not in composer.json.
