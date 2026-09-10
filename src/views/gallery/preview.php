<?php
/**
 * preview.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 11/5/12
 * Time: 1:11 PM
 */
?>
<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery fade" data-filter=":odd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title h5"></h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
    <div class="modal-footer">
        <a class="btn modal-download" target="_blank">
            <i class="bi bi-download" aria-hidden="true"></i>
            <!--<span>Download</span>-->
        </a>
        <a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000">
            <i class="bi bi-play-fill" aria-hidden="true"></i>
            <!--<span>Slideshow</span>-->
        </a>
        <a class="btn btn-info modal-prev">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <!--<span>Previous</span>-->
        </a>
        <a class="btn btn-primary modal-next">
            <!--<span>Next</span>-->
            <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
    </div>
  </div>
</div>
