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



 
Yii::app()->clientScript->registerScript('topContacts',"
;(function () {

x2.topContacts = (function () {

function TopContacts (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

TopContacts.prototype.addTopContact = function addTopContact(contactId, modelClass) {
    $.ajax({
        url: '" . CHtml::normalizeUrl(array('/users/users/addTopContact')) . "',
        type: 'GET',
        data: {
            recordId: contactId,
            modelClass: modelClass
        },
        success: function(response) {
            if(response!='')
                $('#top-contacts-list').html(response);
                $('#sidebar-left-box').height($('#sidebar-left').height());
            }
    });
};

TopContacts.prototype.removeTopContact = function (contactId, modelClass) {
    $.ajax({
        url: '" . CHtml::normalizeUrl(array('/users/users/removeTopContact')) . "',
        type: 'GET',
        // data: 'contactId='+contactId+'&viewId='+viewId,
        data: {
            recordId: contactId,
            modelClass: modelClass
        },
        success: function(response) {
            if(response!='')
                $('#top-contacts-list').html(response);
                $('#sidebar-left-box').height($('#sidebar-left').height());
            }
    });
    //$('#contact'+id).remove();
};

return new TopContacts;

}) ();

}) ();",CClientScript::POS_HEAD);

$actionParams = Yii::app()->controller->getActionParams();
//if(!isset($viewId) || $viewId == null)
    $viewId = isset($actionParams['id'])? $actionParams['id'] : null;

?>
<ul id="top-contacts-list">
<?php
$bookmarkInfo = array();
foreach($bookmarkRecords as $record) {
    $bookmarkInfo[get_class ($record)][] = $record->id;
    echo '<li id="contact' . $record->id . '">';
    if ($record instanceof Contacts) {
        $link = '<strong>'.CHtml::encode($record->firstName).' '.CHtml::encode($record->lastName).'</strong><br />'.CHtml::encode($record->phone);
    } elseif (isset ($record->name)) {
        $link = '<strong>'.CHtml::encode($record->name).'</strong><br />';
    }
    if (isset ($link) && $record->asa ('LinkableBehavior')) {
        echo CHtml::link($link, $record->url);
    }
    unset ($link);
    
    echo CHtml::link(X2Html::fa('fa-times'),'#',array(
        'class'=>'delete-link',
        'onclick'=>"
            x2.topContacts.removeTopContact ('".$record->id."', ".CJSON::encode (
                get_class ($record)
            )."); 
            return false;
        "
    ));
    echo "</li>\n";
}

if(isset (Yii::app()->controller->modelClass)
    && (is_subclass_of (Yii::app()->controller->modelClass, 'X2Model'))
    && Yii::app()->controller->action->id=='view'    // must be viewing it
    && $viewId != null                            // must have an actual ID value
    && (!isset ($bookmarkInfo[Yii::app()->controller->modelClass])
    || !in_array($viewId,$bookmarkInfo[Yii::app()->controller->modelClass]))) {// must not already be in Top Contacts

    $currentRecord = X2Model::model(Yii::app()->controller->modelClass)->findByPk($viewId);

    if ($currentRecord instanceof Contacts) {
        $name = CHtml::encode($currentRecord->firstName).' '.CHtml::encode($currentRecord->lastName);
    } elseif (isset ($currentRecord->name)) {
        $name = CHtml::encode($currentRecord->name);
    } else {
        $name = '';
    }

    echo '<li>';
    echo CHtml::link(
        Yii::t('app','Add {name}',array('{name}'=>$name)),
        '#',
        array(
            'onclick'=>"
                x2.topContacts.addTopContact('".$viewId."', ".CJSON::encode (
                    Yii::app()->controller->modelClass
                )."); 
                return false;"
        )
    );
    echo "</li>\n";;
}
?>
</ul>
