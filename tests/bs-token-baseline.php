<?php
/**
 * YiiBooster project.
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 *
 * Bootstrap 2/3 markup still present in the source tree, recorded per token.
 *
 * This is a RATCHET, not a permanent allowlist. Bootstrap5TokenLintTest asserts the tree matches
 * this file exactly, so migrating a widget fails the suite until you delete its entries here.
 * That is intentional: every migration commit should shrink this file, and it should be an empty
 * list per token by the time 5.0.0 ships. Never add an entry to silence a new violation.
 *
 * Regenerate with: php tests/update-bs-token-baseline.php
 */
return array(
	'bs2-icon-class'         => array(
		'src/views/fileupload/download.php',
		'src/views/fileupload/form.php',
		'src/views/fileupload/upload.php',
		'src/views/gallery/preview.php',
	),
	'bs3-jquery-plugin'      => array(
	),
	'btn-group-justified'    => array(
		'src/widgets/TbButtonGroup.php',
	),
	'btn-removed'            => array(
		'src/widgets/TbButton.php',
	),
	'caret'                  => array(
		'src/widgets/TbBaseMenu.php',
		'src/widgets/TbButton.php',
		'src/widgets/TbDataColumn.php',
		'src/widgets/TbEditableColumn.php',
		'src/widgets/TbJsonGridColumn.php',
	),
	'control-group'          => array(
	),
	'control-label'          => array(
		'src/widgets/TbActiveForm.php',
	),
	'data-api-attribute'     => array(
		'src/views/fileupload/form.php',
		'src/widgets/TbButton.php',
		'src/widgets/TbButtonGroup.php',
		'src/widgets/TbImageGallery.php',
	),
	'float-utility'          => array(
		'src/widgets/TbBulkActions.php',
		'src/widgets/TbPanel.php',
	),
	'form-inline'            => array(
	),
	'glyphicon'              => array(
		'src/gii/bootstrap/BootstrapCode.php',
		'src/widgets/TbCarousel.php',
		'src/widgets/TbExtendedFilter.php',
		'src/widgets/TbTimePicker.php',
	),
	'help-block'             => array(
		'src/gii/bootstrap/templates/default/_form.php',
		'src/widgets/TbActiveForm.php',
	),
	'hero-unit'              => array(
	),
	'hide-utility'           => array(
		'src/views/gallery/preview.php',
		'src/widgets/TbRelationalColumn.php',
	),
	'img-utility'            => array(
	),
	'input-group-addon'      => array(
		'src/widgets/TbActiveForm.php',
		'src/widgets/TbTimePicker.php',
	),
	'jumbotron'              => array(
	),
	'label-context'          => array(
		'src/views/fileupload/download.php',
		'src/views/fileupload/upload.php',
		'src/widgets/TbExtendedFilter.php',
	),
	'nav-stacked'            => array(
		'src/widgets/TbMenu.php',
	),
	'navbar-removed'         => array(
		'src/widgets/TbNavbar.php',
	),
	'panel'                  => array(
		'src/widgets/TbPanel.php',
	),
	'progress-context'       => array(
		'src/views/fileupload/form.php',
		'src/views/fileupload/upload.php',
		'src/widgets/TbProgress.php',
	),
	'validation-state'       => array(
		'src/widgets/TbActiveForm.php',
	),
);
