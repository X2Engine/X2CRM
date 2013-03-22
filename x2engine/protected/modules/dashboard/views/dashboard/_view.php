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
$totalITEMS = $widget->dataProvider->getTotalItemCount();
$position = $index+1;
$menu = '<form name="form1">\n<select name="menu'.$data->name.'" id="menu">';
for($i = 1; $i <= $totalITEMS; $i+=1){
    $indexPLUS = $index+1;
    $selected = '';
    if ($i == $indexPLUS){ $selected=' selected = "selected" ';}
    $menu .= '<option name="option'.$i.'" value='.$i.'>'.$i.'</option>';
}
$menu .= '</select>';
$menu .= CHtml::link('Submit','#',array('onclick'=>"callToggle(); return false;"));
$orderLink = CHtml::link('Edit Order','#',array('onclick'=>"toggleOrderMenu('$position'); return false;","class"=>"submitFORM"));
$menu .= '</form>';
?>
<?php
$visibility = $data->showDASH;
$visible = ($visibility==1);
Yii::app()->clientScript->registerScript('toggleWidgetState', "
function toggleWidgetState(widget,state){
    $.ajax({
       url : 'widgetState',
       type: 'GET',
       data: 'widget='+widget+'&state='+state,
       success: function(response){
           if(response=='success'){
               var link = $('#'+widget+' .portlet-minimize a');
               var newLink = (link.html()=='[+]')? '[&ndash;]': '[+]';
               link.html(newLink);
               $('#'+widget+' .portlet-content').toggle('blind',{},200);
            }
        },
    });
}  ",CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('toggleOrderMenu', "
function toggleOrderMenu(position){
    blar = '$menu';
    var widget = $(this).
    $('div.'+widget+'ORDER').html(blar);
    $('div.'+widget+'ORDER').select.div
}", CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('callToggle', "
function callToggle(){
    var val = $('#menu').val();
    alert('$data->name');
    $('div.".$data->name."ORDER').html('$orderLink');
    toggleWidgetOrder($data->name,val);
}", CClientScript::POS_HEAD);
$jQueryOptions = array(
   'opacity'=>0.6,
   'handle'=>'.portlet-decoration',
   'distance'=>20,
   'delay'=>150,
   'revert'=>50,
   'update'=>"js:function(){
        $.ajax({
            type: 'POST',
            url: 'widgetOrder',
            data: $(this).sortable('serialize'),
        });
    }"
    );
$options = CJavaScript::encode($jQueryOptions);
Yii::app()->getClientScript()->registerScript('SortableWidgets'.'#'.$data->name,"jQuery('#{$data->name}').sortable({$options});");
$itemCount = $widget->dataProvider->getItemCount();
$minimizeLink = CHtml::link($visible? '[&ndash;]' : '[+]','#',array('onclick'=>"toggleWidgetState('$data->name',".($visible? 0 : 1)."); return false;"));
$realNUM = round($itemCount/$item);
$change = (($index % $realNUM) == 0);
if ($change && $index != ($itemCount-1)){ ?>
    </div>
<?php }if ($index == 0 || $change){
    echo "<div class='itemsColumn$item'>";
}
$this->beginWidget('zii.widgets.CPortlet',array(
       'title'=>"<div class='portlet-order'>".$orderLink."</div>".$data->dispNAME."<div class='portlet-minimize'>".$minimizeLink."</div>",
        'id'=>$data->name
));

$this->widget($data->name);
$this->endWidget();
?>
