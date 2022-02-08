<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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


/**
 * @edition: ent
 */


Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/profile/googleProjectForm.css');

echo CHtml::openTag ('div', array ('id' => 'nexmo-project-form'));

$admin = Yii::app()->settings;
echo CHtml::activeCheckbox ($admin, 'nexmoIntegration');
echo CHtml::activeLabel ($admin, 'nexmoIntegration', array('style'=>'display:inline;'));

echo '<br/><p></p><u>';
echo CHtml::encode(Yii::t('app', 'Activating Nexmo Will enable the following:'));
echo '</u>';
?>
<ul>
    <li>Pop-Up Notification of incoming Caller.</li>
    <li>Pop-Up will search in 'Contacts' with corresponding phone number.</li>
    <li>Pop-Up will <font color="red">show basic</font> caller info.</li>
</ul>
<?php

echo '<hr />';
?>
<div class='integration-description'>
</div>
<?php

echo CHtml::tag ('h3', array (), Yii::t('app', 'Configuring (New) Nexmo Integration'));

echo X2Html::orderedList (array (
    Yii::t('app', 'Visit the {link}', array (
        '{link}' => '<a href="https://dashboard.nexmo.com/voice/your-applications">Nexmo API DashBoard</a>'
    )),
    CHtml::encode (
                Yii::t('app', 'Create a new application. Do so by pressing the "+ Add new application" button.')
    ),
    CHtml::encode (
                Yii::t('app', 'Copy and paste this url into the sections "Event URL"')
    ).CHtml::tag (
                'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                Yii::app()->controller->createUrl('/marketing/marketing/nexmoInfo')
    ),
    CHtml::encode (
                Yii::t('app', 'Copy and paste this url into the section "Answer URL"')
    ).CHtml::tag (
                'textarea', array ('readonly' => 'readonly', 'style' => 'display: block', 'class'=>'authorized-js-origins'),
                (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
                Yii::app()->controller->createUrl('/marketing/marketing/nexmoAnswer')
    )
));

echo '<hr />';

echo CHtml::tag ('h3', array (), Yii::t('app', 'Nexmo Call Logs'));
echo X2Html::hint2 (Yii::t('app', 'This will show the list of calls from the past 2 week.'));

$phoneLogModel = X2Model::model('PhoneLog');
    $this->widget('zii.widgets.grid.CGridView', array(
        'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template'=> '{items}{pager}',
        'id' => 'Phone-Log-Widget',
        'dataProvider'=> $phoneLogModel->search('nexmo'),
        'columns'=>array(
                array(  
                        'name' => 'timestamp',
                        'header' => 'Time Call',
                        'value'=>'Formatter::formatDateTime($data->timestamp)',
                        'type'=>'raw',
                        'headerHtmlOptions'=>array('style'=>'width:25%;'),
                ),
                array(  
                        'name'=>'recordType',
                        'header'=>'Record Type',
                        'value' => 'PhoneLog::model()->getType($data)',
                        'type' => 'raw',
                        'headerHtmlOptions'=>array('style'=>'width:15%;'),
                ),
                array(  
                        'name'=>'recordId',
                        'header'=>'Record Name',
                        'value' => 'PhoneLog::model()->getLink($data)',
                        'type' => 'raw',
                        'headerHtmlOptions'=>array('style'=>'width:20%;'),
                ),
                array(  
                        'name'=>'number',
                        'header'=>'Phone Number(from)',
                        'value' => 'PhoneNumber::model()->formatPhoneNumber($data->number)',
                        'headerHtmlOptions'=>array('style'=>'width:20%;'),
                ),
                array(  
                        'name'=>'type',
                        'header'=>'Phone Provider',
                        'value' => '$data->type',
                        'headerHtmlOptions'=>array('style'=>'width:20%;'),
                )
        ),
    ));

echo '<hr />';

echo CHtml::closeTag ('div', array ('id' => 'nexmo-project-form'));
?>
