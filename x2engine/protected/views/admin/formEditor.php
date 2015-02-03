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
?><a href="#" class="x2-button" style="float:right;" onclick="lockEditor();">Hide Borders</a>
<h2><?php echo Yii::t('admin','Form Editor');?></h2>
<div style="width:600px;">
    Drag and drop fields to the layout.  Press save when finished.
</div>
<br /><br />
<?php
$admin=Admin::model()->findByPk(1);
$order=$admin->menuOrder;
$disallow=array(
    'actions',
    'docs',
    'workflow',
);
$pieces=explode(":",$order);
$str="<h4>";
foreach($pieces as $piece){
    if(array_search($piece, $disallow)===false){
        if(isset($_GET['model']) && $piece==$_GET['model']){
            $str.=ucfirst($piece)." | ";
        }else{
            $str.="<a href='?model=".strtolower($piece)."'>".ucfirst($piece)."</a> | ";
        }
    }
}
$str=substr($str,0,-3)."</h4><br />";
echo $str;
?> 
<h4>Versions</h4><br />
<?php
if(isset($_GET['model'])){
    $str2="";
    if(isset($_GET['version'])){
        $str2.="<h4><a href='?model=".$_GET['model']."'>Current</a> | ";
        $versions=FormVersions::model()->findAllByAttributes(array('modelName'=>ucfirst($_GET['model'])));
        foreach($versions as $version){
            if($_GET['version']!=$version->name)
                $str2.="<a href='?model=".$_GET['model']."&version=".CHtml::encode($version->name)."'>".$version->name."</a> | ";
            else
                $str2.=CHtml::encode($version->name)." | ";
        }
    }
    else{
        $str2.="<h4>Current | ";
        $versions=FormVersions::model()->findAllByAttributes(array('modelName'=>ucfirst($_GET['model'])));
        foreach($versions as $version){
                $str2.="<a href='?model=".$_GET['model']."&version=".CHtml::encode($version->name)."'>".$version->name."</a> | ";
        }
    }
    $str2=substr($str2,0,-3);
    $str2.="</h4><br />";
    
    echo $str2;
            
}

if($formUrl==""){
    
}elseif($model instanceof Contacts){
    $this->renderPartial($formUrl,array('contactModel'=>$model,'users'=>User::getNames(),'editor'=>true));
}elseif($model instanceof Actions){
    $this->renderPartial($formUrl,array('actionModel'=>$model,'users'=>User::getNames(),'editor'=>true));
}else{
    $this->renderPartial($formUrl,array('model'=>$model,'users'=>User::getNames(),'contacts'=>Contacts::getAllNames(),'editor'=>true));
}

$fields=Fields::model()->findAllByAttributes(array('modelName'=>get_class($model)), array('order'=>'tabOrder'));
if(isset($_GET['version'])){
    $version=$_GET['version'];
    $version=FormVersions::model()->findByAttributes(array('name'=>$version));
    $sizes=json_decode($version->sizes, true);
    $positions=json_decode($version->positions, true);
    $visibilities=json_decode($version->visibility, true);
    $tempArr=array();
    foreach($fields as $field){
        if(isset($positions[$field->fieldName])){
            $field->coordinates=$positions[$field->fieldName];
            $field->size=$sizes[$field->fieldName];
            $field->visible=$visibilities[$field->fieldName];
            $tempArr[]=$field;
        }
    }
    $fields=$tempArr;
}

?>
<br />
<div style="float:right;border:solid;border-width:1px;border-color:black;padding:10px;">
    <b>Field Control</b><br />
    <ul id="sortable">
    <?php 
        foreach($fields as $field){
            if($field->fieldName!='id'){
                echo "<li class='sortableItem' name='$field->fieldName' id='".$field->fieldName."_list'>".$field->attributeLabel." - <a href='#' onclick=\"toggleBackground('".$field->fieldName."');return false;\">Toggle</a></li>";
                if($field->visible==0){
                    Yii::app()->clientScript->registerScript($field->fieldName.'_background','
                        $("#'.$field->fieldName.'_list").css({"background-color":"lightgrey"});
                    ');
                }
            }
        }
    ?>
    </ul>
<script>
    $('#sortable').sortable();
</script>

</div>
<a style="float:right;position:relative;top:10px;" href="deleteVersion?version=<?php echo isset($_GET['version'])?$_GET['version']:'';?>" class="x2-button">Delete Selected Version</a>
</div>
<form method="POST" onsubmit="calculateValues();">
<input type="hidden" name="coordinates" id="coordinates" value="" />
<input type="hidden" name="sizes" id="sizes" value="" />
<input type="hidden" name="names" id="names" value="" />
<input type="hidden" name="shown" id="shown" value="" />
<input type="hidden" name="order" id="order" value="" />
<input type="submit" style="position:absolute;bottom:50px;"  class="x2-button" value="Save" />
</form>
<form method="POST" onsubmit="calculateValues();" action="saveVersion" style="position:absolute;bottom:100px;">
<input type="hidden" name="coordinates" id="coordinates2" value="" />
<input type="hidden" name="sizes" id="sizes2" value="" />
<input type="hidden" name="names" id="names2" value="" />
<input type="hidden" name="shown" id="shown2" value="" />
<input type="hidden" name="order" id="order2" value="" />
<label>Version Name?</label><input type="text" name="versionName" />
<input type="submit"  class="x2-button" value="Save Version" /> 
</form>

<script>
    var setting=true;
    function lockEditor(){
        
        if(setting==true){
            setting=!setting;
            $(".draggable").css({border: 'none'});
           
        }else{
            setting=!setting;
            $(".draggable").css({'border': 'solid' , 'border-width': '1px'});
        }
    }
    
    function calculateValues(){
        var sizes=new Array();
        var coordinates=new Array();
        var names=new Array();
        var visibility=new Array();
        var order=new Array();
        
        $(".sortableItem").each(function(){
            order.push($(this).attr('name'));
        });
        
        $(".resizable").each(function(){
            var name=$(this).attr('name');
            names.push(name);
            
            var size=$(this).css('width')+":"+$(this).css('height');
            sizes.push(size);

        });
        
        $(".draggable").each(function(){
            var position=$(this).position();
            coordinates.push(position.left+":"+position.top); 
            var visible=$(this).css("visibility");
            if(visible=='visible'){
                visibility.push(1);
            }else{
                visibility.push(0);
            }
        });
        $("#coordinates").val(coordinates);
        $("#sizes").val(sizes);
        $("#names").val(names);
        $("#shown").val(visibility);
        $("#order").val(order);
        $("#coordinates2").val(coordinates);
        $("#sizes2").val(sizes);
        $("#names2").val(names);
        $("#shown2").val(visibility);
        $("#order2").val(order);
        
    }
    
    function toggleBackground(e){
        var bg=$('#'+e+"_list").css('background-color');
        if(bg!='rgba(0, 0, 0, 0)'){
            $('#'+e+"_list").css({'background':'none'});
            $('#'+e).css({'visibility':'visible'});
        }
        else{
            $('#'+e+"_list").css({'background-color':'lightgrey'});
            $('#'+e).css({'visibility':'hidden'});
            
        }
    }
    
</script>
<style>
    .sortableItem{
        border:solid;
        border-width:1px; 
        padding:5px;
        margin-top:1px;
        margin-bottom:1px;
    }
    ul, li {
        list-style-type: none;
    }
    
</style>

