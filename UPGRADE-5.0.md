# Upgrading to YiiBooster 5.0 (Bootstrap 5)

YiiBooster 5.0 ships **Bootstrap 5.3.3**. This is a breaking release: widgets emit Bootstrap 5
markup, there are no Bootstrap 3 compatibility shims, and there is no configuration switch between
the two.

**If you are coming from YiiBooster 3.x you are crossing two Bootstrap majors, not one.** 3.x was
built on Bootstrap **2**; 4.0 moved to Bootstrap 3, and 5.0 moves to Bootstrap 5. The 3.x → 4.x hop
renamed most of the form API and several widgets, and those changes bite first — before anything
Bootstrap 5 specific. Read [Part 1](#part-1--coming-from-yiibooster-3x) first if that is you;
everyone else can start at [Part 2](#part-2--bootstrap-3-to-bootstrap-5).

---

## Before you start

1. **Tag your current version** so rollback is one command.
2. **Purge the published assets directory** after upgrading —
   `rm -rf <webroot>/assets/*`. `CAssetManager` hashes by directory mtime, so without this your
   browser keeps getting the old CSS and it looks like nothing changed.
3. **Grep your application** for the things listed under [What will fail loudly](#what-will-fail-loudly)
   and [What will fail quietly](#what-will-fail-quietly). The second list is the one that costs time.

---

## Part 1 — coming from YiiBooster 3.x

### 1.1 Form methods were renamed: `xxxRow()` → `xxxGroup()`

This is the single largest change for a 3.x application. Every `TbActiveForm` row method was renamed
in 4.0. The old names do not exist, so each one is a `Call to undefined method` at runtime.

| YiiBooster 3.x | 5.0 |
|---|---|
| `textFieldRow()` | `textFieldGroup()` |
| `passwordFieldRow()` | `passwordFieldGroup()` |
| `textAreaRow()` | `textAreaGroup()` |
| `fileFieldRow()` | `fileFieldGroup()` |
| `numberFieldRow()` | `numberFieldGroup()` |
| `maskedTextFieldRow()` | `maskedTextFieldGroup()` |
| `dropDownListRow()` | `dropDownListGroup()` |
| `checkBoxRow()` | `checkboxGroup()` *(note the lower-case `b`)* |
| `checkBoxListRow()` | `checkboxListGroup()` *(note the lower-case `b`)* |
| `radioButtonRow()` | `radioButtonGroup()` |
| `radioButtonListRow()` | `radioButtonListGroup()` |
| `datepickerRow()` | `datePickerGroup()` *(note the capital `P`)* |
| `datetimepickerRow()` | `dateTimePickerGroup()` *(capital `T` and `P`)* |
| `timepickerRow()` | `timePickerGroup()` *(capital `P`)* |
| `dateRangeRow()` | `dateRangeGroup()` |
| `colorpickerRow()` | `colorPickerGroup()` *(capital `P`)* |
| `select2Row()` | `select2Group()` |
| `typeAheadRow()` | `typeAheadGroup()` |
| `ckEditorRow()` | `ckEditorGroup()` |
| `redactorRow()` | `redactorGroup()` |
| `html5EditorRow()` | `html5EditorGroup()` |
| `markdownEditorRow()` | `markdownEditorGroup()` |
| `captchaRow()` | `captchaGroup()` |

Watch the casing on the picker and checkbox methods — a case-insensitive search-and-replace will
produce names that still do not exist.

These have **no direct equivalent**:

| YiiBooster 3.x | What to do |
|---|---|
| `inputRow()` | Call the specific `xxxGroup()` method for the input type. |
| `customRow()` | `customFieldGroup()` |
| `uneditableRow()` | Bootstrap dropped `uneditable-input`. Use a disabled/readonly input, or `form-control-plaintext`. |
| `checkBoxListInlineRow()`, `radioButtonListInlineRow()` | Pass `'inline' => true` to `checkboxListGroup()` / `radioButtonListGroup()`. |
| `checkBoxGroupsListRow()`, `radioButtonGroupsListRow()` | Removed. Compose the groups yourself. |
| `toggleButtonRow()` | `switchGroup()` |
| `passfieldFieldRow()` | Removed — see `TbPassfield` below. |

If you want to migrate gradually rather than in one pass, a `__call` shim in your own
`TbActiveForm` subclass will map the old names onto the new ones. YiiBooster deliberately does not
ship one: it would hide exactly the call sites you need to find. Treat it as scaffolding and delete
it once the views are updated.

### 1.2 Widgets renamed in 4.0

Each of these keeps a stub in 5.0 that throws with the new name, so you will get a clear message
rather than a missing-file fatal — but they must still be updated.

| YiiBooster 3.x | 5.0 |
|---|---|
| `TbBox` | `TbPanel` (renders a Bootstrap 5 **card**) |
| `TbToggleButton` | `TbSwitch` (renders Bootstrap 5's **native** switch) |
| `TbPickerColumn` | `TbJsonPickerColumn` |

### 1.3 `type` became `context`

In 3.x the contextual colour of a button, label, alert or progress bar was `type`. From 4.0 it is
`context`, because `type` came to mean the HTML button type.

```php
// 3.x
$this->widget('bootstrap.widgets.TbButton', array('type' => 'primary', 'label' => 'Save'));

// 5.0
$this->widget('booster.widgets.TbButton', array('context' => 'primary', 'label' => 'Save'));
```

`TYPE_INVERSE` has no equivalent — Bootstrap dropped it. Use `context => 'dark'`.

### 1.4 The component, filter and path alias were all renamed

3.x registered a component class called `Bootstrap`, a `BootstrapFilter`, and referenced widgets
through the `bootstrap.` alias. All three became `Booster` in 4.0.

```php
// 3.x
'components' => array(
    'bootstrap' => array('class' => 'ext.bootstrap.components.Bootstrap'),
),
// and in views:
$this->widget('bootstrap.widgets.TbButton', ...);
// and in controllers:
array('ext.bootstrap.filters.BootstrapFilter - delete'),

// 5.0
'components' => array(
    'booster' => array('class' => 'path.alias.to.booster.components.Booster'),
),
$this->widget('booster.widgets.TbButton', ...);
array('path.alias.to.booster.filters.BoosterFilter - delete'),
```

The component **must** be named `booster`: `Booster::getBooster()`, which the widgets use
internally to reach it, looks it up under exactly that name. Registering it as `bootstrap` leaves
that returning `null`, and any widget that needs it fails.

### 1.5 Bootstrap 2 markup in your own views

Anything you hand-wrote against Bootstrap 2 needs updating twice over. The most common:

| Bootstrap 2 | Bootstrap 5 |
|---|---|
| `.row` + `.span1`…`.span12` | `.row` + `.col-*` (12-column, but `col-sm-6` style names) |
| `.control-group` / `.controls` | `.mb-3` / a `.col-*` wrapper |
| `.form-actions` | no equivalent — a plain container with spacing utilities |
| `.form-search`, `.form-inline`, `.form-horizontal` | removed; use flex utilities / per-group `.row` |
| `.input-prepend`, `.input-append`, `.add-on` | `.input-group` + `.input-group-text` |
| `.input-block-level` | `.w-100` |
| `.btn-mini`, `.btn-small`, `.btn-large` | `.btn-sm`, `.btn-sm`, `.btn-lg` (mini is gone) |
| `.icon-*` | `.bi bi-*` (Bootstrap Icons, bundled) |
| `.hero-unit` | removed — utilities on a plain container |
| `.well`, `.thumbnail` | removed — use `.card` |
| `.alert-error` | `.alert-danger` |
| `.label`, `.label-*` | `.badge`, `.text-bg-*` |
| `.bar` (inside `.progress`) | `.progress-bar` |
| `.navbar-inner`, `.brand` | removed; `.navbar-brand` |
| `.nav-list`, `.dropdown-submenu` | removed |
| `.muted` | `.text-body-secondary` |
| `.text-error` | `.text-danger` |
| `.img-polaroid` | `.img-thumbnail` |
| `.pull-left` / `.pull-right` | `.float-start` / `.float-end` |
| `.hidden-phone`, `.visible-*` | `.d-none`, `.d-*-block` responsive display utilities |
| modal: flat `.modal > .modal-header` | `.modal > .modal-dialog > .modal-content > .modal-header` |
| `<a class="close">&times;</a>` | `<button class="btn-close"></button>` |

---

## Part 2 — Bootstrap 3 to Bootstrap 5

### 2.1 Widgets removed in 5.0

All of these keep a stub that throws a `CException` naming the replacement, so upgrading code fails
at the offending line rather than on a missing include. The stubs are deleted in 5.1.

| Removed | Why | Use instead |
|---|---|---|
| `TbHtml`, `TbArray` | 4,338 lines of Bootstrap **2** markup with no callers inside the library; existed only for `yii-auth` compatibility (#443) | Fork `yii-auth`'s handful of views, or `CHtml` |
| `TbInput`, `TbInputHorizontal`, `TbInputVertical`, `TbInputInline`, `TbInputSearch` | Bootstrap 2 markup, and unreachable — `TbForm` dispatches through `TbFormInputElement` onto `TbActiveForm`'s `*Group()` methods | `TbActiveForm` |
| `TbHeroUnit` | Bootstrap 2 class, dead since Bootstrap 3 | Utilities on a plain container |
| `TbJumbotron` | Component removed in Bootstrap 5 | `<div class="p-5 bg-body-tertiary rounded-3">` |
| `TbChosen` | Chosen archived upstream | `TbSelect2` |
| `TbPassfield` | Pass\*Field archived, and only its minified build was vendored | A password input plus your own strength meter |
| `TbFileUpload` | Broken for years — registered four asset files absent from the repository | A plain file input, or integrate blueimp jQuery-File-Upload directly |
| `TbImageGallery` | Its plugin reads `$.fn.modal.Constructor.prototype` at load time and throws under Bootstrap 5, taking the page's JavaScript with it | A framework-neutral lightbox (blueimp Gallery, GLightbox, PhotoSwipe) |
| `TbModalManager` | Bootstrap 5 stacks modals natively | `TbModal` |

`TbActiveForm::chosenGroup()` and `passFieldGroup()` are gone with their widgets, as is the `pass`
alias in `TbFormInputElement`.

### 2.2 Behaviour removed

`TbButton::$toggle` and `TbButtonGroup::$toggle` now **throw**. Bootstrap 5 deleted the button
plugin's toggle and checkbox/radio behaviour outright — there is no attribute to rename them to, and
emitting the old one would look migrated while doing nothing. Use inputs with the `btn-check` class
inside the button group.

### 2.3 Icons

Glyphicons no longer exist. YiiBooster bundles **Bootstrap Icons 1.11.3** and renders every icon
through one helper, which accepts all the old spellings:

```php
'icon' => 'trash'                     // works
'icon' => 'glyphicon-trash'           // works
'icon' => 'glyphicon glyphicon-trash' // works
'icon' => 'icon-trash'                // works (Bootstrap 2 style)
'icon' => 'fa fa-trash'               // passed through untouched
```

Names that changed between Glyphicons and Bootstrap Icons are translated automatically (`ok` →
`check-lg`, `remove` → `x-lg`, `user` → `person`, `time` → `clock`, and about 80 more). An unmapped
legacy name is logged as a warning under `YII_DEBUG`, so a blank icon becomes a searchable log line
rather than a mystery.

To use Font Awesome instead, set `iconPrefix`:

```php
'booster' => array(
    'class' => 'booster.components.Booster',
    'iconPrefix' => 'fa',
    'bootstrapIconsCss' => false,
),
```

**Raw `glyphicon-*` or `icon-*` markup in your own views is not touched** — only values passed to
widgets go through the helper. Grep for those separately.

### 2.4 Contextual states

`default` is still an accepted context name but now renders as `secondary`; Bootstrap 5 has no
`default` variant. `secondary`, `light` and `dark` are new.

### 2.5 Form markup

| Bootstrap 3 | Bootstrap 5 |
|---|---|
| `form-group` | `mb-3` — configurable via `TbActiveForm::$groupCssClass` |
| `control-label` | `col-form-label` (horizontal) / `form-label` (vertical) |
| `help-block` | `form-text` |
| `input-group-addon` | `input-group-text` |
| `has-error` / `has-success` | `is-invalid` / `is-valid`, **on the control** |
| `form-control` on a `<select>` | `form-select` |
| `.checkbox` / `.radio` wrappers | `.form-check` with `form-check-input` / `form-check-label` |
| `form-inline`, `form-horizontal` | removed |

The hardcoded `col-sm-3` / `col-sm-9` horizontal grid is now `$labelCssClass` / `$controlCssClass`.

**Validation state.** Bootstrap 5 styles the invalid state on the control, while Yii's client
validation toggles a class on the container. YiiBooster bridges the two: Yii keeps flagging the
container with a neutral marker class, and an `afterValidateAttribute` hook mirrors the real state
onto the input. If you set `clientOptions['afterValidateAttribute']` yourself you will replace that
bridge — call `Booster.markValidationState(attribute, hasError)` from your handler to keep it.

If your own CSS targets `.has-error`, update it to `.is-invalid`.

### 2.6 JavaScript

Bootstrap 5 still installs its jQuery plugins when jQuery is present, so `$el.modal()` and friends
keep working — but **only from `DOMContentLoaded` onwards**, and the bridge gives you no way to
dispose an instance. Prefer the class API:

```js
// Bootstrap 3
$('#myModal').modal('show');
$('[data-toggle="tooltip"]').tooltip();

// Bootstrap 5
bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal')).show();
new bootstrap.Tooltip(el);
```

Code that reads `$.fn.modal` or `$.fn.tooltip` *before* `DOMContentLoaded` will not find them.

All Bootstrap data attributes are now namespaced: `data-toggle` → `data-bs-toggle`,
`data-target` → `data-bs-target`, `data-dismiss` → `data-bs-dismiss`, `data-slide` →
`data-bs-slide`, `data-spy` → `data-bs-spy`. Update any you have written by hand.

Widget `events` options are unaffected — `'shown.bs.modal' => 'js:function(){...}'` still works,
because Bootstrap 5 triggers a jQuery event alongside the native one.

`TbSwitch` is the exception: it no longer uses a plugin, so its `options` are ignored and its
events are plain DOM events. The plugin's `switchChange` event does not exist; listen for `change`.

### 2.7 Other markup changes

| Bootstrap 3 | Bootstrap 5 |
|---|---|
| `panel`, `panel-heading`, `panel-body` | `card`, `card-header`, `card-body` |
| `label label-*` | `badge text-bg-*` |
| `badge-*` colours | `text-bg-*` |
| `<li class="active">` in pagination | `<li class="page-item active">` + `<a class="page-link">` |
| `<li>` in breadcrumbs | `<li class="breadcrumb-item">` |
| `<li class="divider">` | `<li><hr class="dropdown-divider"></li>` |
| `<span class="caret">` | removed — drawn by `.dropdown-toggle::after` |
| `nav` items | `<li class="nav-item"><a class="nav-link">`; `active`/`disabled` move to the `<a>` |
| dropdown items | `<a class="dropdown-item">` |
| `nav-stacked` | `flex-column` |
| `table-condensed` | `table-sm` (the type name `condensed` still works) |
| `carousel` `.item` | `.carousel-item`; controls are `<button>`, indicators are buttons in a `div` |
| `tab-pane active in` | `tab-pane active show` |
| `btn-default` | `btn-secondary` |
| `btn-xs` | `btn-sm` (removed in Bootstrap 4) |
| `btn-block` | `w-100`, or a `d-grid` wrapper |
| `btn-group-justified` | `d-flex w-100` |
| `.hide` | `.d-none` |
| `navbar-default` / `navbar-inverse` | a background utility plus `data-bs-theme` |
| `navbar-fixed-top` | `fixed-top` |
| `navbar-toggle` + three `icon-bar` | `navbar-toggler` + one `navbar-toggler-icon` |

`TbNavbar` gains `$expand` (default `lg`), which emits the `navbar-expand-*` class Bootstrap 4+
requires. Without it a navbar never expands and stays permanently collapsed.

---

## What will fail loudly

These throw with a message naming the replacement. Grep for them first:

```
grep -rn "TbHtml::\|TbInput\|TbBox\|TbToggleButton\|TbPickerColumn" protected/
grep -rn "TbChosen\|TbPassfield\|TbFileUpload\|TbImageGallery\|TbModalManager" protected/
grep -rn "TbHeroUnit\|TbJumbotron" protected/
grep -rn "chosenGroup\|passFieldGroup" protected/
grep -rn "'toggle'\s*=>" protected/
grep -rnE "\w+Row\(" protected/views/          # 3.x form API
grep -rn "bootstrap.widgets.\|BootstrapFilter\|components.Bootstrap" protected/   # 3.x aliases
```

## What will fail quietly

These render wrong without erroring, and are what actually costs time:

```
grep -rn "glyphicon\|icon-" protected/views/    # raw icon markup in your own templates
grep -rn "has-error\|has-success" protected/    # your own CSS or JS targeting them
grep -rn "data-toggle\|data-target\|data-dismiss" protected/
grep -rn "panel\|well\|thumbnail\|jumbotron" protected/
grep -rn "span[0-9]\|control-group\|form-actions" protected/   # Bootstrap 2 leftovers
grep -rn "'type'\s*=>\s*'\(primary\|info\|success\|warning\|danger\|inverse\)'" protected/
```

## Bundled plugin changes

**select2 3.5.1 → 4.1.0-rc.0**, with `select2-bootstrap-5-theme`. `TbSelect2` sets
`theme: 'bootstrap-5'` for you. If your own JavaScript drives a Select2 control, the 3.x
programmatic API is gone:

```js
$el.select2('val', v);        // 3.x
$el.val(v).trigger('change'); // 4.x

$el.select2('enable', false);  // 3.x
$el.prop('disabled', true).trigger('change');  // 4.x
```

`initSelection` and `query` were replaced by `dataAdapter`/`ajax`. If you pass either through
`TbSelect2::$options`, see Select2's own 3.5 → 4.0 release notes.

**bootstrap-datepicker 1.3.1 → 1.10.0.** No API change.

**x-editable** is archived upstream, so its popover container and select2 adapter are patched in
place here for Bootstrap 5. `TbEditable`, `TbEditableField`, `TbEditableColumn` and
`TbEditableDetailView` keep their APIs. Note that only the unminified build ships - the vendored
minified copy could not be regenerated from patched source, and shipping a stale one would have
quietly reintroduced the Bootstrap 3 container wherever `minify` is on, which is the default.

### Replaced plugins

Every remaining Bootstrap 3-era plugin has been swapped for a maintained, framework-neutral
library. The widgets keep their names and their PHP surface, but the **JavaScript options you pass
through `$options` belong to the new library** — they are not translated.

| Widget | Was | Now |
|---|---|---|
| `TbMarkdownEditor` | bootstrap-markdown (2022, Bootstrap 3 only) | **EasyMDE 2.20** |
| `TbMarkdownEditorJs` | PageDown (unmaintained) | **removed** — use `TbMarkdownEditor` |
| `TbHtml5Editor` | bootstrap3-wysihtml5 (2020) | **Quill 2.0** |
| `TbDateTimePicker` | smalot datetimepicker (archived 2019) | **daterangepicker 3.1**, `singleDatePicker` mode |
| `TbDateRangePicker` | loose daterangepicker 1.3.12 | **daterangepicker 3.1** |
| `TbTags` | bootstrap-tags (2017) | **Select2 `tags` mode** |
| `TbTypeahead` | typeahead.js (abandoned 2015) | **Awesomplete 1.1** |
| `TbColorPicker` | bootstrap-colorpicker (archived 2022) | **Coloris 0.25** |

Things to check when you upgrade:

- **Editor options.** `TbMarkdownEditor::$options` and `TbHtml5Editor::$editorOptions` are passed
  straight to EasyMDE and Quill. Old bootstrap-markdown / wysihtml5 option names will be ignored.
  Quill's toolbar is configured with `modules.toolbar`.
- **Quill edits a `div`, not the textarea.** The widget keeps your original field, hides it, and
  syncs Quill's HTML into it on every change — so the value still posts under the same name and no
  controller or model change is needed. If you had CSS targeting the textarea, it is now hidden.
- **`TbTypeahead` remote sources.** Bloodhound is gone and Awesomplete has no equivalent, so a
  Bloodhound-style `datasets` entry now throws rather than silently producing an empty list. Local
  lists keep working; the new `list` property is the preferred way to pass one. For remote lookups,
  drive the Awesomplete instance from your own fetch.
- **`TbDateTimePicker` options** are now daterangepicker's. `language` is mapped onto Moment's
  locale for you; the smalot plugin's other options are not.
- **`TbTags` options** are now Select2's.
- **`TbColorPicker`** binds by selector through Coloris and has no jQuery plugin. Its `events` are
  attached as DOM listeners — Coloris fires `coloris:pick` on the input.