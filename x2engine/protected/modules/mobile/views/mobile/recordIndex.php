<?php
/***********************************************************************************
 * copyright (c) 2011-2015 x2engine inc. all rights reserved.
 *
 * x2engine inc.
 * p.o. box 66752
 * scotts valley, california 95067 usa
 * company website: http://www.x2engine.com
 *
 * x2engine inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this software for your internal business purposes only
 * for the number of users purchased by you. your use of this software for
 * additional users is not covered by this license and requires a separate
 * license purchase for such users. you shall not distribute, license, or
 * sublicense the software. title, ownership, and all intellectual property
 * rights in the software belong exclusively to x2engine. you agree not to file
 * any patent applications covering, relating to, or depicting this software
 * or modifications thereto, and you agree to assign any patentable inventions
 * resulting from your use of this software to x2engine.
 *
 * this software is provided "as is" and without warranties of any kind, either
 * express or implied, including without limitation the implied warranties of
 * merchantability, fitness for a particular purpose, title, and non-infringement.
 **********************************************************************************/

//Yii::app()->clientScript->registerCssFile(
//    Yii::app()->controller->module->assetsUrl.'/css/recordIndex.css');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/RecordIndexControllerBase.js');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/RecordIndexController.js');

if (Yii::app()->controller instanceof ProfileController) {
    $title = Yii::t('app', 'Users');
} else {
    $title = Yii::app()->controller->moduleObj->title;
}

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.RecordIndexController ();
", CClientScript::POS_END);

?>
<div class='refresh-content' data-refresh-selector='.page-title'>
<h1 class='page-title ui-title'>
<?php
echo CHtml::encode ($title);
?>
</h1>
</div>

<div class='refresh-content' data-refresh-selector='#header .header-content-right'>
    <div class='search-button ui-btn'>
        <i class='fa fa-search'></i>
    </div>
</div>

<div class='refresh-content' data-refresh-selector='#header .header-content-center'>
    <div class='search-box' style='display: none;'>
        <div class='search-cancel-button ui-btn'>
            <i class='fa fa-arrow-left'></i>
        </div>
        <form action='<?php echo AuxLib::getRequestUrl (); ?>'>
            <input type='text' name='<?php 
                $htmlOptions = array ();
                $attr = $model instanceof Profile ? 'fullName' : 'name';
                CHtml::resolveNameId ($model, $attr, $htmlOptions);
                echo $htmlOptions['name'];
            ?>'
             placeholder='<?php 
                echo 'Search ' . ucfirst ($title); ?>' />
        </form>
        <div class='search-clear-button ui-btn'>
            <i class='fa fa-close'></i>
        </div>
    </div>
</div>

<?php

$this->widget (
    'application.modules.mobile.components.RecordIndexListView', 
    array (
        'dataProvider' => $dataProvider,
        'template' => '{items}{moreButton}',
        'itemView' => 
            $model instanceof Topics ?
                'application.modules.mobile.views.mobile._topicsIndexItem' :
                'application.modules.mobile.views.mobile._recordIndexItem',
        'htmlOptions' => array (
            'class' => 'record-index-list-view'
        ),
        'emptyText' => '<div>'.CHtml::encode (Yii::t('app', 'No results found.')).'<div>', 
    ));


if ($this->hasMobileAction ('mobileCreate') && 
    Yii::app()->user->checkAccess(ucfirst ($this->module->name).'Create')) {
?>

<a href='<?php echo $this->createAbsoluteUrl ('mobileCreate'); ?>' class='fixed-corner-button'>
    <div class='record-create-button'>
    <?php
        echo X2Html::fa ('plus');
    ?>
    </div>
</a>

<?php
}
?>
