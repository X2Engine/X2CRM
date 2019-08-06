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




$downloadLink = $model->drive ? 
    CHtml::link( 
        X2Html::fa('external-link').Yii::t('media', 'View in Google Drive'),
            "https://drive.google.com/file/d/".$model->fileName,
            array(
                'class'=>'x2-button download-media x2-blue', 
                'target'=>'_blank'
    )) :
    CHtml::link (
        X2Html::fa('download').Yii::t('media', 'Download File'),
        array(
            'download',
            'id' =>$model->id
        ), array(
            'class'=>'x2-button download-media x2-blue'
    ));
$imageLink = $model->getImage(true);

Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/view.css');

$this->noBackdrop = true;
$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));
$imageExists = $model->fileExists() && $model->isImage();
?>


<div class="page-title icon media">
    <h2>
        <span class="no-bold"><?php echo Yii::t('media','File: '); ?></span>
        <?php 
            echo $model->renderAttribute (($model->drive || !empty($model->name)) ? 
                "name" : "fileName"); 
        ?>
    </h2>
<?php 
echo X2Html::editRecordbutton($model); 
?>
</div>

<div class='<?php echo $imageExists ? 'x2-layout-island ' : ''; ?>tray media-tray'>
    <?php if($imageExists) { ?>
    <div class='column'>
    <?php 
    }
        $assoc = X2Model::getAssociationModel($model->associationType, $model->associationId);
        $this->widget('DetailView', array(
            'model'   => $model,
            'scenario'=> 'Default',
            'specialFields' => array(
                'associationId' => $assoc ? $assoc->link : ''
            ),
            'htmlOptions' => array ('class' => $imageLink ? 'x2-layout-island' : '')
        ));
        echo $downloadLink;
        ?>
    <?php if($imageExists) { ?>
        <textarea id="embedcode" style="background:#eee" class='x2-extra-wide-input share-media'>
            <?php 
            echo $model->getPublicUrl();
            ?>
        </textarea>
    </div>
    <div class='column'>
        <div class="media-image">
            <div class='full-size-screen'>
                <?php
                echo X2Html::fa('expand').' ';
                echo Yii::t('media', 'View Full Size');
                ?>
            </div>
            <?php echo $imageLink ?>
        </div>
    </div>
    <?php
    }
    ?>
    <div class='clear'></div>
</div>

<?php 
$this->widget('X2WidgetList', array(
    'layoutManager' => $layoutManager,
    'block' => 'center',
    'model' => $model,
    'modelType' => 'media'
));
?>

