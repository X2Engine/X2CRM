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



$modTitles = array(
    'contact' => Modules::displayName(false),
    'contacts' => Modules::displayName(),
);


//these hidden field is here to stop google auto fill from filling in the grid
$ConFields = X2Model::model("Contacts")->getFields();
foreach($ConFields as $field){
    echo '<input type="hidden" id="Contacts[' . $field->fieldName . ']" name="Contacts[' . $field->fieldName . ']">';      
}

$heading = '';

$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'import', 'export', 'map', 'savedMaps',
);
$enableSelectAllOnAllPages = false;
if ($this->route == 'contacts/contacts/index') {
    $heading = Yii::t('contacts', 'All {module}', array('{module}' => $modTitles['contacts']));
    
    $dataProvider = $model->searchAll();
    $enableSelectAllOnAllPages = true;
    //unset($menuItems[0]['url']);
    //unset($menuItems[4]); // View List
} elseif ($this->route == 'contacts/contacts/myContacts') {
    $heading = Yii::t('contacts', 'My {module}', array('{module}' => $modTitles['contacts']));
    $dataProvider = $model->searchMyContacts();
    $menuOptions = array_merge($menuOptions, array('createList', 'viewList'));
} elseif ($this->route == 'contacts/contacts/newContacts') {
    $heading = Yii::t('contacts', 'Today\'s {module}', array('{module}' => $modTitles['contacts']));
    $dataProvider = $model->searchNewContacts();
    $menuOptions = array_merge($menuOptions, array('createList', 'viewList'));
}
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);

Yii::app()->clientScript->registerScript('search', "
/*$('.search-button').unbind('click').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});*/

$('#content').on('mouseup','#contacts-grid a',function(e) {
	document.cookie = 'vcr-list=" . $this->getAction()->getId() . "; expires=0; path=/';
});
", CClientScript::POS_READY);
?>


<div class="search-form" style="display:none">
    <?php
//    $this->renderPartial('_search', array(
//        'model' => $model,
//        'users' => User::getNames(),
//    ));
    ?>
</div><!-- search-form -->
<?php
$this->widget('X2GridView', array(
    'id' => 'contacts-grid',
    'enableQtips' => true,
    'qtipManager' => array(
        'X2GridViewQtipManager',
        'loadingText' => addslashes(Yii::t('app', 'loading...')),
        'qtipSelector' => ".contact-name"
    ),
    'title' => $heading,
    'enableSelectAllOnAllPages' => $enableSelectAllOnAllPages,
    'buttons' => array('advancedSearch', 'clearFilters', 'columnSelector', 'autoResize', 'showHidden'),
    'template' =>
    '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">' .
    '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">' .
    '<div id="x2-gridview-page-title" ' .
    'class="page-title icon contacts x2-gridview-fixed-title">' .
    '{title}{buttons}{filterHint}{massActionButtons}{summary}{topPager}' .
    '{items}{pager}',
    'fixedHeader' => true,
    'dataProvider' => $dataProvider,
    // 'enableSorting'=>false,
    // 'model'=>$model,
    'filter' => $model,
    'pager' => array('class' => 'CLinkPager', 'maxButtonCount' => 10),
    // 'columns'=>$columns,
    'modelName' => 'Contacts',
    'viewName' => 'contacts',
    // 'columnSelectorId'=>'contacts-column-selector',
    'defaultGvSettings' => array(
        'gvCheckbox' => 30,
        'name' => 125,
        'email' => 165,
        'leadSource' => 83,
        'leadstatus' => 91,
        'phone' => 107,
        'lastActivity' => 78,
        'gvControls' => 73,
    ),
    'specialColumns' => array(
        'name' => array(
            'name' => 'name',
            'header' => Yii::t('contacts', 'Name'),
            'value' => '$data->link',
            'type' => 'raw',
        ),
    ),
    'massActions' => array(
        'MassDelete', 'MassTag', 'MassTagRemove', 'MassUpdateFields', 
        'MergeRecords', 'MassPublishNote', 'MassPublishCall', 'MassPublishTime', 
        'MassPublishAction', 'MassAddRelationship', 
        'MassAddToList', 'NewListFromSelection', 'MassExecuteMacro'
    ),
    'enableControls' => true,
    'enableTags' => true,
    'fullscreen' => true,
));
?>
