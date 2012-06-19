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
<h2><center><?php echo Yii::t('dashboard','Widget Dashboard'); ?></center></h2>
<?php echo Yii::t('app','<center><p>Below you see a physical listing of your widgets. Please re-order and re-size them until you are satisfied.</br>The alterations you make to this page <b>DO NOT</b> change the default listing on the right side of your screen.</p></center>');
/*
$form = $this->beginWidget('CActiveForm',array(
    'id'=>'dashboard-form',
    'enableAjaxValidation'=>false,
    'action'=>array('changeColumns','id'=>Yii::app()->user->getId()),
));
echo $form->dropDownList($model,'',array('0'=>'Number of Rows','1'=>'2 ROWS(DEFAULT)','2'=>'3 ROWS','3'=>'4 ROWS'),array());
echo CHtml::ajaxSubmitButton('Change Columns',array('class'=>'x2-button dashSubmit', 'id'=>'submitting'));
$this->endWidget();
 */
?>
</form>
<div class="blah">
<?php
$dataProvider = $model->search('dash');
$itemCount = $dataProvider->getItemCount();
$this->widget('zii.widgets.CListView',array(
    'id'=>'dashboard-grid',
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css',
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view',
    'viewData'=>array(
        'itemCount' => $dataProvider->getItemCount(),
    ),
));
?>
</div>
