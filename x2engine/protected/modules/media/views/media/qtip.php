<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



?>

<?php
$isImage = $model->isImage();
$maxVal = 200;
$imgStyle = "display:block;max-height:{$maxVal}px; max-width:{$maxVal}px;";
if (!$model->drive && $isImage) {

	$imageUrl = $model->getPublicUrl();

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
	<strong><?php echo Yii::t('media','MIME Info:');?></strong> <?php echo $model->renderAttribute('mimetype'); ?><br>
<?php endif; ?>
<?php if($model->drive): ?>
	<?php echo Yii::t('media','File is hosted on Google Drive');?>
<?php endif; ?>