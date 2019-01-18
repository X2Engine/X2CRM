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
