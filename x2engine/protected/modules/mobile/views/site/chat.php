<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$this->pageTitle = Yii::app()->name . ' - Group Chat';
$menuItems = array(
            array('label' => Yii::t('app', 'Main Menu'), 'url' => array('site/home/')),
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
            url: '<?php echo $this->createUrl('site/getMessages'); ?>?latest='+x2_latest,
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
$.ajax({'type':'POST','url':'<?php echo $this->createUrl('site/newMessage'); ?>','cache':false,'data':$("#x2chatform").serialize()});$("textarea#message").val('');return false;
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
