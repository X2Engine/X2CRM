<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$this->pageTitle = Yii::app()->settings->appName . ' - Group Chat';
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
