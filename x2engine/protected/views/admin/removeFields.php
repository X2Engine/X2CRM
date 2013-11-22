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
<div class="page-title"><h2><?php echo Yii::t('admin','Remove A Custom Field'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','This form will allow you to remove any custom fields you have added.'); ?>
<br>
<b style="color:red;"><?php echo Yii::t('admin','ALL DATA IN DELETED FIELDS WILL BE LOST.'); ?></b>
<form name="removeFields" action="removeField" method="POST">
	<br>
	<select name="field">
            <option value=""><?php echo Yii::t('admin','Select A Field');?></option>
		<?php foreach($fields as $id=>$field){
            $fieldRecord=X2Model::model('Fields')->findByPk($id);
            if(isset($fieldRecord))
                echo "<option value='$id'>$fieldRecord->modelName - $field</option>";
        }  ?>
	</select>
	<br><br>
	<input class="x2-button" type="submit" value="<?php echo Yii::t('admin','Delete');?>" />
</form>
</div>