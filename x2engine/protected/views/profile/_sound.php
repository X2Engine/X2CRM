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

$type=$data->associationType;
?>
<div class="<?php echo $type; ?>-row" id="<?php echo $type."_".$data->id; ?>">
<?php
echo CHtml::link(
	$data->fileName,
	'#',
	array(
		'onclick'=>"setSound('".$type."','".$data->id."','".$data->fileName."','".$data->uploadedBy."'); return false;",
        'style'=>$data->fileName==Yii::app()->params->profile->$type?'font-weight:bold':'',
        'id'=>'sound-'.$data->id,
	)
);

if($data->uploadedBy == Yii::app()->user->getName()) {
	echo CHtml::link(
		'[x]',
		'#',
		array(
			'onclick'=>"deleteSound('".$type."','".$data->id."'); return false;",
			'class'=>'delete-link'
		)
	);
}
echo '<br />';
?>
</div>
