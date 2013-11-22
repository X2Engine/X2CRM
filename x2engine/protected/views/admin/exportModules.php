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
?>
<div class="page-title"><h2><?php echo Yii::t('admin','Export A Module');?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Please select a model to export.');?>

<form name="exportModule" action="exportModule" method="POST">
	<br>
	<select name="name">
		<?php
		if(empty($modules))
			echo '<option value="" disabled="disabled">---</option>';
		
		foreach($modules as $name=>$module)
			echo "<option value='$name'>$module</option>";
		?>
	</select>
	<br><br>
	<input type="submit" class="x2-button" value="<?php echo Yii::t('admin','Export');?>" />
</form>
<?php 
if($dlFlag){
	echo "<br>";
	echo CHtml::link("Download Module",Yii::app()->request->baseUrl.'/'.$file.'.zip',array('id'=>'download-link','class'=>'x2-button'));
}
?>
</div>