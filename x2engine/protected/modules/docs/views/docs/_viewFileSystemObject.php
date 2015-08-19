<?php
echo X2Html::tag('div', array(
    'id' => $data->id . '-file-system-object',
    'data-type' => $data->type,
    'data-id' => $data->objId,
    'class' => 'view file-system-object'.($data->type=='folder'?' file-system-object-folder':' file-system-object-doc')
        . ($data->validDraggable() ? ' draggable-file-system-object' : '')
        . ($data->validDroppable() ? ' droppable-file-system-object' : ''),
        ), '', false);
?>
<div class="file-system-clear-fix">
    <div class="file-system-object-link">
        <span style="margin-right:5px;"><?php echo $data->getIcon(); ?></span>
        <span><?php echo $data->getLink(); ?></span>
    </div>
    <div class="file-system-object-attributes">
        <div class="file-system-object-owner"><?php echo $data->getOwner(); ?></div>
        <div class="file-system-object-last-updated"><?php echo $data->getLastUpdateInfo(); ?></div>
        <div class="file-system-object-visibility"><?php echo $data->getVisibility(); ?></div>
    </div>
</div>
<?php echo X2Html::tag('/div'); ?>
