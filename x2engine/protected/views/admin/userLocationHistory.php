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





//Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/admin/userHistory.css');


?>
<!--<div class="page-title"><h2><?php //echo Yii::t('admin', 'User Location'); ?></h2></div>-->
<div >
    <?php
        // Display a grid of user login history
        $this->widget('X2GridViewGeneric', array(
            'id' => 'location-history-grid',
            'title'=>Yii::t('admin', 'User Location History'),
            'dataProvider' => $locationHistoryDataProvider,
                'baseScriptUrl'=>  
                Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
                'template'=> '<div class="page-title">{title}'
                        .'{buttons}{summary}</div>{items}{pager}',
            'buttons' => array ('autoResize', 'map'),
            'defaultGvSettings' => array (
                'username' => 100,
                'firstName' => 100,
                'lastName' => 100,
                'lat' => 100,
                'lon' => 100,
                'IP' => 100,
                'createDate' => 180,
            ),
            'gvSettingsName' => 'login-history-grid',
            'columns'=>array(
                    array (
                    'name' => 'username',
                    'header' => Yii::t('admin','User'),
                    'type' => 'raw',
                    'value' => 'User::model()->findByPk($data->recordId)->username',
                ),
                    array (
                    'name' => 'firstName',
                    'header' => Yii::t('admin','First Name'),
                    'type' => 'raw',
                    'value' => 'User::model()->findByPk($data->recordId)->firstName',
                ),
                    array (
                    'name' => 'lastName',
                    'header' => Yii::t('admin','Last Name'),
                    'type' => 'raw',
                    'value' => 'User::model()->findByPk($data->recordId)->lastName',
                ),
                    array (
                    'name' => 'latitude',
                    'header' => Yii::t('admin','Latitude'),
                    'type' => 'raw',
                    'value' => '$data->lat',
                ),
                    array (
                    'name' => 'longitude',
                    'header' => Yii::t('admin','Longitude'),
                    'type' => 'raw',
                    'value' => '$data->lon',
                ),
                    array (
                    'name' => 'IP',
                    'header' => Yii::t('admin','IP Address'),
                    'type' => 'raw',
                    'value' => '$data->ipAddress',
                ),
                    array (
                    'name' => 'createDate',
                    'header' => Yii::t('admin','Create Date'),
                    'type' => 'raw',
                    'value' => 'Formatter::formatCompleteDate($data->createDate)',
                ),
            ),
        ));
    ?>


    <div class="error">
<?php //echo $form->errorSummary($model); ?>
    </div>

    <?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n";  ?>
</div>
