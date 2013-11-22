<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$menuItems = array(
	array('label'=>Yii::t('actions','Action List')),
	array('label'=>Yii::t('actions','Create'),'url'=>array('create')),
);
$this->actionMenu = $this->formatMenu($menuItems);

?>
<div class="page-title icon actions" id="page-header">
    <h2><?php echo Yii::t('actions','Actions');?></h2>

    <div class="title-bar" style="padding-left:0px;">
        <?php echo CHtml::link(Yii::t('actions','Back to Top'),'#',array('class'=>'x2-button right','id'=>'scroll-top-button')); ?>
        <?php echo CHtml::link(Yii::t('actions','Filters'),'#',array('class'=>'controls-button x2-button right','id'=>'advanced-controls-toggle')); ?>
        <?php echo CHtml::link(Yii::t('actions','New Action'),array('/actions/actions/create'),array('class'=>'controls-button x2-button right','id'=>'create-button')); ?>
        <?php echo CHtml::link(Yii::t('actions','Switch to Grid'),array('index','toggleView'=>1),array('class'=>'x2-button right')); ?>
    </div>
</div>
<?php echo $this->renderPartial('_advancedControls',$params,true); ?>
<?php
$this->widget('zii.widgets.CListView', array(
			'id'=>'action-list',
			'dataProvider'=>$dataProvider,
			'itemView'=>'application.modules.actions.views.actions._viewIndex',
			'htmlOptions'=>array('class'=>'action list-view','style'=>'width:100%'),
            'viewData'=>$params,
			'template'=>'{items}{pager}',
            'afterAjaxUpdate'=>'js:function(){
                clickedFlag=false;
                lastClass="";
                $(\'#advanced-controls\').after(\'<div class="form" id="action-view-pane" style="float:right;width:0px;display:none;padding:0px;"></div>\');
            }',
        'pager' => array(
                    'class' => 'ext.infiniteScroll.IasPager',
                    'rowSelector'=>'.view',
                    'listViewId' => 'action-list',
                    'header' => '',
                    'options' => array(
                        'history' => true,
                        'triggerPageTreshold' => 2,
                        'trigger'=>Yii::t('app','Load More'),
                        'scrollContainer'=>'.items',
                        'container'=>'.items',
                    ),
                  ),
		));
?>

<script>
    var clickedFlag=false;
    var lastClass="";
    $(document).on('click','#scroll-top-button',function(e){
        e.preventDefault();
        $(".items").animate({ scrollTop: 0 }, "slow");
    });
    $(document).on('click','#advanced-controls-toggle',function(e){
        e.preventDefault();
        if($('#advanced-controls').is(':hidden')){
            $("#advanced-controls").slideDown();
        }else{
            $("#advanced-controls").slideUp();
        }
    });
    $(document).on('ready',function(){
        $('#advanced-controls').after('<div class="form" id="action-view-pane" style="float:right;width:0px;display:none;padding:0px;"></div>');
    });
    <?php 
	if (IS_IPAD) { 
		echo "$(document).on('vclick', '.view', function (e) {" ;
	} else {
		echo "$(document).on('click','.view',function(e){";
	}
	?>
		//alert ('click');
        if(!$(e.target).is('a')){
            e.preventDefault();
            if(clickedFlag){
                if($('#action-view-pane').hasClass($(this).attr('id'))){
                    $('#action-view-pane').removeClass($(this).attr('id'));
                    $('.items').animate({'margin-right': '20px'},400,function(){
                        $('.items').css('margin-right','0px')
                    });
                    $('#action-view-pane').html('<div style="height:800px;"></div>');
                    $('#action-view-pane').animate({width: '0px'},400,function(){
                        $('#action-view-pane').hide();
                    });
                    $(this).removeClass('important');
                    clickedFlag=!clickedFlag;
                }else{
                    $('#'+lastClass).removeClass('important');
                    $(this).addClass('important');
                    $('#action-view-pane').removeClass(lastClass);
                    $('#action-view-pane').addClass($(this).attr('id'));
                    lastClass=$(this).attr('id');
                    var pieces=lastClass.split('-');
                    var id=pieces[1];
                   	$('#action-view-pane').html('<iframe id="action-frame" src="<?php echo Yii::app()->controller->createAbsoluteUrl('/actions/actions/viewAction'); ?>?id='+id+'" onload="createControls('+id+', false);"></iframe>');
                }
            }else{
                $(this).addClass('important');
				if (x2.isAndroid)
                	$('.items').css('margin-right','20px').animate({'margin-right': '5%'});
				else
                	$('.items').css('margin-right','20px').animate({'margin-right': '60%'});
                $('#action-view-pane').addClass($(this).attr('id'));
                lastClass=$(this).attr('id');
                var pieces=lastClass.split('-');
                var id=pieces[1];
                $('#action-view-pane').show();
                $('#action-view-pane').animate({width: '59%'});;
                clickedFlag=!clickedFlag;
                 $('#action-view-pane').html('<iframe id="action-frame" src="<?php echo Yii::app()->controller->createAbsoluteUrl('/actions/actions/viewAction'); ?>?id='+id+'" onload="createControls('+id+', false);"></iframe>');
            }
        }
    });

</script>
<style>
	#action-frame {
		width:99%;
		height:800px;
	}
    #action-list .items{
        clear:none;
        max-height:800px;
        overflow-y:auto;
    }
    #action-list .view{
        clear:none;
    }
    #action-list .view:hover{
        background-color:#FFFFC2;
    }
    .important{
        background-color:#FFFFC2;
    }
    .complete{
        color:green;
    }
</style>
