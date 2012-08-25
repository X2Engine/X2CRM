<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
?>

<div id="x2-inline-tags" class="form">
	<b><?php echo Yii::t('app', 'Tags'); ?></b>
	<div id="x2-tag-list">
		<?php foreach($tags as $tag) {
			echo '<span class="tag"><span class="delete-tag">[x]</span> '.CHtml::link($tag['tag'],array('/search/search?term=%23'.substr($tag['tag'],1)), array('class'=>'')).'</span>';
		}?>
	</div>
</div>

<?php
// give javascript URLs, model type, and model id
// javascript is in /js/tags.js
$appendTag = $this->controller->createUrl('/site/appendTag');
$removeTag = $this->controller->createUrl('/site/removeTag');
$searchUrl = $this->controller->createUrl('/search/search');
$type = ucfirst($modelName);
$id = $model->id;
Yii::app()->clientScript->registerScript('tags-list', "
$(function() {

	$('#x2-inline-tags').data('appendTagUrl', '$appendTag');
	$('#x2-inline-tags').data('removeTagUrl', '$removeTag');
	$('#x2-inline-tags').data('searchUrl', '$searchUrl');
	$('#x2-inline-tags').data('type', '$type');
	$('#x2-inline-tags').data('id', $id);	
});",CClientScript::POS_HEAD);
?>