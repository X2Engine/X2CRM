<a href="#" class="x2-button" style="float:right;" onclick="lockEditor();">Hide Borders</a>
<h2>Form Editor</h2>
Drag and drop to edit forms.  Press save when finished.
<br /><br />
<?php
$admin=Admin::model()->findByPk(1);
$order=$admin->menuOrder;
$disallow=array(
    'actions',
    'docs',
    'workflow',
    'sales',
    'accounts',
);
$pieces=explode(":",$order);
$str="<h4>";
foreach($pieces as $piece){
    if(array_search($piece, $disallow)===false){
        if(isset($_GET['model']) && $piece==$_GET['model']){
            $str.=ucfirst($piece)." | ";
        }else{
            $str.="<a href='?model=".lcfirst($piece)."'>".ucfirst($piece)."</a> | ";
        }
    }
}
$str=substr($str,0,-3)."</h4><br />";
echo $str;

if($formUrl==""){
    
}elseif($model instanceof Contacts){
    $this->renderPartial($formUrl,array('contactModel'=>$model,'users'=>UserChild::getNames(),'editor'=>true));
}elseif($model instanceof Actions){
    $this->renderPartial($formUrl,array('actionModel'=>$model,'users'=>UserChild::getNames(),'editor'=>true));
}else{
    $this->renderPartial($formUrl,array('model'=>$model,'users'=>UserChild::getNames(),'contacts'=>Contacts::getAllNames(),'editor'=>true));
}

$fields=Fields::model()->findAllByAttributes(array('modelName'=>get_class($model)), array('order'=>'tabOrder'));

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
    
<script>
    $('#sortable').sortable();
</script>
</div>
</div>
<form method="POST" onsubmit="calculateValues();">
<input type="hidden" name="coordinates" id="coordinates" value="" />
<input type="hidden" name="sizes" id="sizes" value="" />
<input type="hidden" name="names" id="names" value="" />
<input type="hidden" name="shown" id="shown" value="" />
<input type="hidden" name="order" id="order" value="" />
<input type="submit" style="position:absolute;bottom:50px;"  class="x2-button" value="Save" />
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

