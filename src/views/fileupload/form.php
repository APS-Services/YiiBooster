<?php
/**
 * The file upload form used as target for the file upload widget
 *
 * @var TbFileUpload $this
 * @var array $htmlOptions
 * @var string $name
 */
?>
<?php echo CHtml::beginForm($this->url, 'post', $this->htmlOptions); ?>
<div class="fileupload-buttonbar row">
    <div class="col-sm-7">
        <!-- The fileinput-button span is used to style the file input field as button -->
		<span class="btn btn-success fileinput-button"> <i class="bi bi-plus-lg" aria-hidden="true"></i> <span>Add files...</span>
			<?php
			if ($this->hasModel()) :
				echo CHtml::activeFileField($this->model, $this->attribute, $htmlOptions) . "\n"; else :
				echo CHtml::fileField($name, $this->value, $htmlOptions) . "\n";
			endif;
			?>
		</span>
        <button type="submit" class="btn btn-primary start">
            <i class="bi bi-upload" aria-hidden="true"></i>
            <span>Start upload</span>
        </button>
        <button type="reset" class="btn btn-warning cancel">
            <i class="bi bi-slash-circle" aria-hidden="true"></i>
            <span>Cancel upload</span>
        </button>
        <button type="button" class="btn btn-danger delete">
            <i class="bi bi-trash" aria-hidden="true"></i>
            <span>Delete</span>
        </button>
        <input type="checkbox" class="toggle">
    </div>
    <div class="col-sm-5 fileupload-progress fade">
        <!-- The global progress bar -->
        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0;"></div>
        </div>
        <!-- The extended global progress information -->
        <div class="progress-extended">&nbsp;</div>
    </div>
</div>
<!-- The loading indicator is shown during image processing -->
<div class="fileupload-loading"></div>
<br>
<!-- The table listing the files available for upload/download -->
<div class="container-fluid" style="margin-bottom: 15px;">
    <table class="table table-striped">
        <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
    </table>
</div>
<?php echo CHtml::endForm(); ?>

