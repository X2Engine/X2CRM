<?php
/**
 * @var $this GalleryManager
 * @var $model GalleryPhoto
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
?>
<?php echo CHtml::openTag('div', $this->htmlOptions); ?>
<!-- Gallery Toolbar -->
<div class="btn-toolbar gform">
    <span class="btn btn-success fileinput-button x2-button highlight">
        <i class="icon-plus icon-white"></i>
        <?php echo Yii::t('galleryManager.main', 'Add…'); ?>
        <input type="file" name="image" class="afile" accept="image/*" multiple="multiple"/>
    </span>

    <div class="btn-group x2-button-group">
        <label class="btn x2-button">
            <input type="checkbox" style="margin: 0;" class="select_all"/>
            <?php echo Yii::t('galleryManager.main', 'Select all'); ?>
        </label>
        <span class="btn disabled edit_selected x2-button"><i class="icon-pencil"></i> <?php echo Yii::t('galleryManager.main', 'Edit'); ?></span>
        <span class="btn disabled remove_selected x2-button"><i class="icon-remove"></i> <?php echo Yii::t('galleryManager.main', 'Remove'); ?></span>
    </div>
</div>
<!-- Gallery Photos -->
<div class="sorter">
    <div class="images"></div>
    <br style="clear: both;"/>
</div>

<!-- Modal window to edit photo information -->
<div class="dialog editor-dialog">
    <div class="dialog-body">
        <div class="edit-form"></div>
    </div>
</div>
<div class="dialog preview-dialog">
    <div class="dialog-body centered-item-container">
        <div class="preview-display"></div>
    </div>
</div>
<div class="overlay">
    <div class="overlay-bg">&nbsp;</div>
    <div class="drop-hint">
        <span class="drop-hint-info"><?php echo Yii::t('galleryManager.main', 'Drop Files Here…') ?></span>
    </div>
</div>
<div class="progress-overlay">
    <div class="overlay-bg">&nbsp;</div>
    <!-- Upload Progress Modal-->
    <div class="modal progress-modal">
        <div class="modal-header">
            <h3><?php echo Yii::t('galleryManager.main', 'Uploading images…') ?></h3>
        </div>
        <div class="modal-body">
            <div class="progress progress-striped active">
                <div class="bar upload-progress"></div>
            </div>
        </div>
    </div>
</div>
<?php echo CHtml::closeTag('div'); ?>
