<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$menuItems = array(
	array('label'=>Yii::t('actions','Action List')),
	array('label'=>Yii::t('actions','Create'),'url'=>array('create')),
);
$this->actionMenu = $this->formatMenu($menuItems);

function trimText($text) {
	if(strlen($text)>150)
		return substr($text,0,147).'...';
	else
		return $text;
}

?>
<div class="page-title icon actions" id="page-header">
    <h2>Actions</h2>
    <div class="title-bar" style="padding-left:0px;">
        <?php echo CHtml::link(Yii::t('app','Back to Top'),'#',array('class'=>'x2-button right','id'=>'scroll-top-button')); ?>
        <?php echo CHtml::link(Yii::t('app','Filters'),'#',array('class'=>'controls-button x2-button right','id'=>'advanced-controls-toggle')); ?>
        <?php echo CHtml::link(Yii::t('app','New Action'),array('/actions/create'),array('class'=>'controls-button x2-button right','id'=>'create-button')); ?>
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
    $(document).on('click','.view',function(e){
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
                    $('#action-view-pane').html('<iframe style="width:100%;height:800px" id="action-frame" src="actions/viewAction?id='+id+'" onload="createControls('+id+', false);"></iframe>');
                }
            }else{
                $(this).addClass('important');
                $('.items').css('margin-right','20px').animate({'margin-right': '60%'});
                $('#action-view-pane').addClass($(this).attr('id'));
                lastClass=$(this).attr('id');
                var pieces=lastClass.split('-');
                var id=pieces[1];
                $('#action-view-pane').show();
                $('#action-view-pane').animate({width: '59%'});;
                clickedFlag=!clickedFlag;
                $('#action-view-pane').html('<iframe style="width:100%;height:800px" id="action-frame" src="actions/viewAction?id='+id+'" onload="createControls('+id+', false);"></iframe>');
            }
        }
    });

</script>
<style>
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
