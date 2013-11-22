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

$this->pageTitle = Yii::app()->name . ' - Group Chat';
$menuItems = array(
            array('label' => Yii::t('app', 'Main Menu'), 'url' => array('/mobile/site/home')),
        );

$this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));
?>
<script type="text/javascript">
    function updateChat(){
        if (x2_skip || x2_pending !=  null){
            //console.log('S='+x2_latest);
            setTimeout(updateChat,x2_chatPoll);
            return;
        }
        //console.log('L='+x2_latest);
        x2_pending = $.ajax({
            type: 'POST',
            url: '<?php echo $this->createUrl('/site/getMessages'); ?>?latest='+x2_latest,
            success:
                function (data){
                var recs = $.parseJSON(data);
                var len = (recs != null) ? recs.length-1 : -1;
                //console.log('D='+recs);
                if(len >= 0){
                    //console.log('A1='+(len+1));
                    for ( var i=len; i>=0; --i ){
                        var rec = recs[i];
                        if (i == 0)
                            x2_latest = rec.timestamp;
                        var html='<h4>'+rec.username+'<small> ('+rec.when+')</small></h4>';
                        html+='<p>'+rec.message+'</p>';
                        $("ul#x2groupchat").prepend('<li>'+html+'</li>');                    
                    }
                    //console.log('T='+x2_latest);
                    $("ul#x2groupchat").listview("refresh");
                }
            },
            complete:
                function (xhr,status){
                x2_pending = null;
                if (!x2_skip)
                    setTimeout(updateChat,x2_chatPoll);
            }
        });

    }

$('body').undelegate('#x2chatsubmit','click').delegate('#x2chatsubmit','click',function(){
if(x2_pending != null){
    x2_pending.abort();
    x2_pending=null;
}
$.ajax({'type':'POST','url':'<?php echo $this->createUrl('/site/newMessage'); ?>','cache':false,'data':$("#x2chatform").serialize()});$("textarea#message").val('');return false;
});

</script>    
<div data-role="collapsible" data-collapsed="true">
    <h3><?php echo Yii::t('mobile', 'Create Message'); ?></h3>
    <p>
    <form id="x2chatform">
        <div data-role="fieldcontain">
            <?php echo CHtml::textArea('message'); ?>
        </div>
        <?php
        echo CHtml::resetButton(Yii::t('mobile', 'Clear'), array('data-inline' => 'true'));
        echo CHtml::button(
                Yii::t('app', 'Send'), array('id' => 'x2chatsubmit', 'data-inline' => 'true'));
        ?>
    </form>
</p>
</div>
<div data-role="collapsible" data-collapsed="false">
    <h3><?php echo Yii::t('mobile', 'Message List'); ?></h3>
    <ul id="x2groupchat" data-role="listview" data-inset="true">
    </ul>
</div>
