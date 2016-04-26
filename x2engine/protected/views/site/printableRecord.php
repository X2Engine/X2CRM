<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

?>

<meta charset="UTF-8">
<link rel='stylesheet' type='text/css' 
 href='<?php echo Yii::app()->getTheme()->getBaseUrl().'/css/x2forms.css'; ?>'/>
<link rel='stylesheet' type='text/css' 
 href='<?php echo Yii::app()->getTheme()->getBaseUrl().'/css/printableRecord.css'; ?>'/>
<!--<link rel='stylesheet' type='text/css' 
 href='<?php //echo Yii::app()->getClientScript()->getCoreScriptUrl().'/rating/jquery.rating.css'; ?>'/>-->
<link rel='stylesheet' type='text/css' 
 href='<?php echo Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css'; ?>'/>
<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
 '/jquery.js'; ?>'></script>
<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
 '/jquery.metadata.js'; ?>'></script>
<script src='<?php echo Yii::app()->getClientScript()->getCoreScriptUrl().
 '/jquery.rating.js'; ?>'></script>

<div class='config-panel-content'>
</div>

<h1 id='page-title'><?php echo addslashes ($pageTitle); ?></h1>
<h3 id='model-title'><?php echo addslashes ($modelTitle); ?></h3>

<?php //echo X2Html::divider(); ?>

<?php 
$this->widget ('DetailView', array(
    'model' => $model,
    'modelName' => $modelClass
));

//$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => $modelClass));
?>

<script>
	$('title').html("<?php echo $pageTitle ?>");

	// replace stars with textual representation
	$('span[id^="<?php echo $modelClass; ?>-<?php echo $id; ?>-rating"]').each (function () {
		var stars = $(this).find ('[checked="checked"]').val ();
        stars = stars ? stars : 0;
		$(this).children ().remove ();
		$(this).html (stars + '/5 <?php echo addslashes (Yii::t('app', 'Stars')); ?>');
	});

	var sections = 1;
	$('.sectionTitle').each(function(){
		var title = $(this).html();
		if (!title) {
			title = 'Section ' + sections++;
		}
		var row = $('<div class="row"></div>').appendTo($('.config-panel-content'));
		$('<span class="label"></span>').appendTo(row).html(title)
		var check = $('<input type="checkbox" checked />').appendTo(row);

		var that = this;
		check.change(function(){
			$(that).closest('.formSection').toggle();
		});
	});

</script>



