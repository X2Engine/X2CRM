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

<?php
$isImage = $model->isImage();
$maxVal = 200;
$imgStyle = "display:block;max-height:{$maxVal}px; max-width:{$maxVal}px;";
if (!$model->drive && $isImage) {

	$imageUrl = $model->url;

	// Preemptively scale the image to make the preview less glitchy:
	$dim = CJSON::decode($model->resolveDimensions());
	if (!empty($dim)) {
		$vToD = array_flip($dim);
		$scaleDim = $vToD[max(array_values($dim))];
		$otherDim = $scaleDim!='height'?'height':'width';
		$scaleRatio = $maxVal / $dim[$scaleDim];
		$dim[$scaleDim] = $maxVal;
		$dim[$otherDim] *= $scaleRatio;
		$imgStyle .= "height:{$dim['height']};width:{$dim['width']};";
	}
} else {
	$imageUrl = Yii::app()->theme->baseUrl . '/images/media_generic.png';
	$imgStyle .= 'height:48px;width:48px;';
}

echo CHtml::image($imageUrl, $model->description, array('style' => $imgStyle));
?>
<br />
<strong>Size:</strong> <?php echo $model->fmtSize; ?> <br />
<?php if (!$model->drive && $isImage && extension_loaded('gd')): ?>
	<strong><?php echo Yii::t('media','Dimensions:');?></strong> <?php echo $model->fmtDimensions; ?><br />
<?php endif; ?>
<?php if(!empty($model->mimetype)): ?>
	<strong><?php echo Yii::t('media','MIME Info:');?></strong> <?php echo $model->mimetype; ?><br>
<?php endif; ?>
<?php if($model->drive): ?>
	<?php echo Yii::t('media','File is hosted on Google Drive');?>
<?php endif; ?>