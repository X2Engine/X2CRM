<div id="yii-debug-toolbar-switcher">
    <a href="javascript:;//"><?php echo YiiDebug::t('TOOLBAR')?></a>
</div>
<div id="yii-debug-toolbar" style="display:none;">
    <div id="yii-debug-toolbar-buttons">
        <ul>
            <li><br />&nbsp;<br /></li>
            <?php foreach ($panels as $panel): ?>
            <li class="yii-debug-toolbar-button <?php echo $panel->id ?>">
                <a class="yii-debug-toolbar-link" href="javascript:;//" id="yii-debug-toolbar-tab-<?php echo $panel->id ?>">
                    <?php echo CHtml::encode($panel->menuTitle); ?>
                    <?php if (!empty($panel->menuSubTitle)): ?>
                    <br />
                    <small><?php echo $panel->menuSubTitle; ?></small>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div id="resource-usage">
            <?php $this->widget('YiiDebugToolbarResourceUsage', array(
                'title'=>'Resource usage',
                'htmlOptions'=>array(
                    'class'=>'panel'
                )
            )); ?>
        </div>
    </div>

    <?php foreach ($panels as $panel) : ?>
    <div id="<?php echo $panel->id ?>" class="yii-debug-toolbar-panel">
        <div class="yii-debug-toolbar-panel-title">
        <a href="javascript:;//" class="yii-debug-toolbar-panel-close"><?php echo YiiDebug::t('Close')?></a>
        <h3>
            <?php echo CHtml::encode($panel->title); ?>
            <?php if ($panel->subTitle) : ?>
            <small><?php echo CHtml::encode($panel->subTitle); ?></small>
            <?php endif; ?>
        </h3>
        </div>
        <div class="yii-debug-toolbar-panel-content">
            <div class="scroll">
                <div class="scrollcontent">
                <?php $panel->run(); ?>
                <br />
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
(function($) {$(function(){yiiDebugToolbar.init()})}(jQuery));
</script>
