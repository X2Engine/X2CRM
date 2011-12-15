<h2>Form Editor</h2>
Drag and drop to edit forms.  Press save when finished.
<br /><br />
<?php
$admin=Admin::model()->findByPk(1);
$order=$admin->menuOrder;
$pieces=explode(":",$order);
$str="<h4>";
foreach($pieces as $piece){
    if(isset($_GET['model']) && $piece==$_GET['model'])
        $str.=ucfirst($piece)." | ";
    else
        $str.="<a href='?model=".lcfirst($piece)."'>".ucfirst($piece)."</a> | ";
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

?>
<br />
<input type="submit" class="x2-button" value="Save" />