<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
$this->menu=array(
    array('label'=>'Sidebar Settings', 'url'=>array('settings?where=side'))
);
$model = new Dashboard;
$model = $model->search('dash');
$hideWidgetJs = '';
$ind = 1;
foreach ($model as $row){
    $class = $row['name'];
    $rowPLUS = $model[$ind+1];
    $classPLUS = $rowPLUS['name'];
    if ($row['showDASH'] == 0){
        $hideWidgetJs .= "$('#widget_" . $class . " .portlet-content').hide();\n";
        $hideWidgetJs .= "$('#widget_" . $classPLUS . " .portlet-content').css('float','right');\n";
    }
}
Yii::app()->clientScript->registerScript('blarg',"
    function blarg(){
        $.ajax({
            url:'" . CHtml::normalizeUrl(array('changeColumns')) . "',
            type: 'POST',
            data: $('#menu1').serialize(),
            update: '.blah',
            success: function(response){
                " . $hideWidgetJs . "
            }
        });
    }", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('hideIntro', "
    function hideIntro(){
        $.ajax({
            url: '" . CHtml::normalizeUrl(array('hideIntro')) . "',
            update: '.intro',
            success: function(response){
                $('div.intro').hide();
            }
        });
    }", CClientScript::POS_HEAD);
$hINT = intval($hINT);
$hideIntro = CHtml::link('Hide Intro', '#', array('onclick'=>"hideIntro();","class"=>"hideIntro"));
if (!($hINT)){
?>
<div class="intro"><?php echo $hideIntro; ?>
<h2><center><?php echo Yii::t('charts','Widget Dashboard'); ?></center></h2>
<?php echo Yii::t('app','<center><p>Below you see a physical listing of your widgets. Please re-order and re-size them until you are satisfied.</br>The alterations you make to this page <b>DO NOT</b> change the default listing on the right side of your screen.</p></center>');?>
</div>
<?php 
}
echo CHtml::ajaxButton(
    "Dashboard Settings",
    $this->createUrl('dashSettings'),
    array('type'=>'POST','data'=>"item=$item"),
    array('class'=>'x2-button alterDASH')
);
?>
<div class="blah" id='large'>
<?php
$this->widget('SortWidg',array(
    'portlets'=>$model,
    'items'=>$item,
    'jQueryOptions'=>array(
        'opacity'=>0.6,
        'handle'=>'.portlet-decoration',
        'distance'=>20,
        'delay'=>150,
        'revert'=>50,
        'update'=>"js:function(){
            $.ajax({
                type: 'POST',
                url: 'widgetOrder',
                data: $('#yw0').sortable('serialize'),
            });
        }"
    ),
));
echo "</div>";
?>
