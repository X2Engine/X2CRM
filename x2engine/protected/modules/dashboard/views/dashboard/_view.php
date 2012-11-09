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
