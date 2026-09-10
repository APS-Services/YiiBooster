<?php
/**
 * Built-in client script packages.
 *
 * Please see {@link CClientScript::packages} for explanation of the structure
 * of the returned array.
 *
 * @author Ruslan Fadeev <fadeevr@gmail.com>
 *
 * @var Booster $this
 */
return array(
	'font-awesome' => array(
		'baseUrl' => $this->enableCdn ? '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/' : $this->getAssetsUrl().'/font-awesome/',
		'css' => array(($this->minify || $this->enableCdn) ? 'css/font-awesome.min.css' : 'css/font-awesome.css'),
	),
	'bootstrap.js' => array(
		'baseUrl' => $this->enableCdn ? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/' : $this->getAssetsUrl() . '/bootstrap/',
		// The bundle build embeds Popper, which Bootstrap 5 requires for tooltips, popovers
		// and dropdowns. Shipping it avoids adding a separate Popper package - and a script
		// ordering dependency - to the graph.
		'js' => array($this->minify ? 'js/bootstrap.bundle.min.js' : 'js/bootstrap.bundle.js'),
		// Bootstrap 5 itself no longer needs jQuery, but this edge must stay: roughly twenty
		// packages below declare `'depends' => array('bootstrap.js')` and have always relied on
		// it to pull jQuery in first, as do Yii's own CActiveForm and CGridView client scripts.
		// Dropping it is a script-ordering bug that only shows up in production.
		'depends' => array('jquery'),
	),
	'booster' => array(
		// YiiBooster's own runtime helpers: tooltip/popover instance lifecycle around Yii's AJAX
		// grid updates. See assets/js/booster.js.
		'baseUrl' => $this->getAssetsUrl(),
		'js' => array('js/booster.js'),
		'depends' => array('bootstrap.js'),
	),
	'bootstrap-icons' => array(
		// Bootstrap 4 dropped Glyphicons and Bootstrap 5 ships no icon set, so widgets that render
		// an icon need one. Bootstrap Icons is the set maintained alongside Bootstrap itself.
		// The stylesheet references its font files relatively (fonts/bootstrap-icons.woff2), so the
		// two must stay in the same directory.
		'baseUrl' => $this->enableCdn ? 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/' : $this->getAssetsUrl() . '/bootstrap-icons/',
		'css' => array($this->minify ? 'bootstrap-icons.min.css' : 'bootstrap-icons.css'),
	),
	'bootstrap-yii' => array(
		'baseUrl' => $this->getAssetsUrl(),
		'css' => array('css/bootstrap-yii.css'),
	),
	'jquery-css' => array(
		'baseUrl' => $this->getAssetsUrl(),
		'css' => array('css/jquery-ui-bootstrap.css'),
	),
	'bootbox' => array(
		// bootbox 6 targets Bootstrap 4/5 and drives the modal through Bootstrap's jQuery bridge,
		// so it needs both jQuery and Bootstrap present - and Bootstrap's bridge only appears on
		// DOMContentLoaded, which is after bootbox's own script runs. That is fine because bootbox
		// only calls it in response to user code, never at load.
		'baseUrl' => $this->getAssetsUrl() . '/bootbox/',
		'js' => array($this->minify ? 'bootbox.min.js' : 'bootbox.js'),
		'depends' => array('jquery', 'bootstrap.js'),
	),
	'notify' => array(
		'baseUrl' => $this->getAssetsUrl() . '/notify/',
		'js' => array($this->minify ? 'notify.min.js' : 'notify.js'),
		'depends' => array('jquery'),
	),
	//widgets start
    'ui-layout' => array(
        'baseUrl' => $this->getAssetsUrl() . '/ui-layout/',
        'css' => array('css/layout-default.css'),
        'js' => array($this->minify ? 'js/jquery.layout.min.js' : 'js/jquery.layout.js'),
        'depends' => array('jquery', 'jquery.ui'),
    ),
	'datepicker' => array(
		'depends' => array('jquery'),
		'baseUrl' => $this->enableCdn ? 'https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/' : $this->getAssetsUrl() . '/bootstrap-datepicker/',
		// Filenames match upstream's dist layout exactly, so the CDN and local branches resolve
		// the same relative paths. They previously diverged - the local copy was renamed on
		// import, which would 404 the moment enableCdn was switched on.
		'css' => array($this->minify ? 'css/bootstrap-datepicker3.min.css' : 'css/bootstrap-datepicker3.css'),
		// The noconflict snippet lives in its own file so the vendored library stays pristine and
		// can be dropped in wholesale on upgrade. It captures $.fn.datepicker so the patched
		// jQuery UI build can restore it - that collision is jQuery UI vs bootstrap-datepicker and
		// is unrelated to Bootstrap's own version.
		'js' => array($this->minify ? 'js/bootstrap-datepicker.min.js' : 'js/bootstrap-datepicker.js', 'js/bootstrap-datepicker-noconflict.js')
	),
	'datetimepicker' => array(
		'depends' => array('jquery'),
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-datetimepicker/', // Not in CDN yet
		'css' => array($this->minify ? 'css/bootstrap-datetimepicker.css' : 'css/bootstrap-datetimepicker.css'),
		'js' => array($this->minify ? 'js/bootstrap-datetimepicker.min.js' : 'js/bootstrap-datetimepicker.js')
	),
	'date' => array(
		'baseUrl' => $this->enableCdn ? '//cdnjs.cloudflare.com/ajax/libs/datejs/1.0/' : $this->getAssetsUrl() . '/js/',
		'js' => array('date.min.js')
	),
	'colorpicker' => array(
		'depends' => array('jquery'),
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-colorpicker/',
		'css' => array($this->minify ? 'css/bootstrap-colorpicker.min.css' : 'css/bootstrap-colorpicker.css'),
		'js' => array($this->minify ? 'js/bootstrap-colorpicker.min.js' : 'js/bootstrap-colorpicker.js')
	),
	'x-editable' => array(
		// x-editable is archived upstream and its popover container is patched in place for
		// Bootstrap 5 (see the "Editable Popover (Bootstrap 5)" block in the js). Only the
		// unminified build is shipped: the vendored .min.js could not be regenerated from the
		// patched source, and serving a stale minified copy would silently reintroduce the
		// Bootstrap 3 container in exactly the configuration most sites run - minify defaults
		// to true.
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-editable/',
		'css' => array('css/bootstrap-editable.css'),
		'js' => array('js/bootstrap-editable.js'),
		'depends' => array('jquery', 'bootstrap.js', 'datepicker') /* datepicker must come before editable */
	),
	'moment' => array(
		'baseUrl' => $this->getAssetsUrl(),
		'js' => array('js/moment.min.js'),
	),
	'picker' => array(
		'baseUrl' => $this->getAssetsUrl() . '/picker',
		'js' => array('bootstrap.picker.js'),
		'css' => array('bootstrap.picker.css'),
		'depends' => array('bootstrap.js')
	),
	'bootstrap.wizard' => array(
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-wizard',
		'js' => array($this->minify ? 'jquery.bootstrap.wizard.min.js' : 'jquery.bootstrap.wizard.js')
	),
	'ajax-cache' => array(
		'baseUrl' => $this->getAssetsUrl() . '/ajax-cache',
		'js' => array('jquery.ajax.cache.js'),
	),
	'jqote2' => array(
		'baseUrl' => $this->getAssetsUrl() . '/jqote2',
		'js' => array('jquery.jqote2.min.js'),
	),
	'json-grid-view' => array(
		'baseUrl' => $this->getAssetsUrl() . '/json-grid-view',
		'js' => array('jquery.json.yiigridview.js'),
		'depends' => array('jquery', 'jqote2', 'ajax-cache')
	),
	'group-grid-view' => array(
		'baseUrl' => $this->getAssetsUrl() . '/group-grid-view',
		'js' => array('jquery.group.yiigridview.js'),
		'depends' => array('jquery', 'jqote2', 'ajax-cache')
	),
	'redactor' => array(
		'baseUrl' => $this->getAssetsUrl() . '/redactor',
		'js' => array($this->minify ? 'redactor.min.js' : 'redactor.js'),
		'css' => array('redactor.css'),
		'depends' => array('jquery')
	),
	'timepicker' => array(
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-timepicker',
		'js' => array('js/bootstrap-timepicker.js'),
		'css' => array($this->minify ? 'css/bootstrap-timepicker.min.css' : 'css/bootstrap-timepicker.css'),
		'depends' => array('bootstrap.js')
	),
	'ckeditor' => array(
		'baseUrl' => $this->getAssetsUrl() . '/ckeditor',
		'js' => array('ckeditor.js')
	),
	'highcharts' => array(
		'baseUrl' => $this->enableCdn ? '//code.highcharts.com' : $this->getAssetsUrl() . '/highcharts',
		'js' => array($this->minify ? 'highcharts.js' : 'highcharts.src.js')
	),
	'wysihtml5' => array(
		'depends' => array('bootstrap.js'),
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap3-wysihtml5',
		'css' => array('bootstrap-wysihtml5.css'),
		'js' => array('wysihtml5-0.3.0.js', 'bootstrap3-wysihtml5.js'),
	),
	'markdown' => array(
		'depends' => array('bootstrap.js'),
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-markdown',
		'css' => array('css/bootstrap-markdown.min.css'),
		'js' => array('js/bootstrap-markdown.js', 'js/to-markdown.js', 'js/markdown.js'),
	),
	'typeahead' => array(
		'depends' => array('jquery'),
		'baseUrl' => $this->getAssetsUrl() . '/typeahead',
		'css' => array('css/typeahead.css'),
		'js' => array($this->minify ? 'js/typeahead.bundle.min.js' : 'js/typeahead.bundle.js'),
	),
	'bootstrap-tags' => array(
		'depends' => array('jquery'),
		'baseUrl' => $this->getAssetsUrl() . '/bootstrap-tags',
		'css' => array('css/bootstrap-tags.css'),
		'js' => array($this->minify ? 'js/bootstrap-tags.min.js' : 'js/bootstrap-tags.js'),
	),
);
