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




$this->insertActionMenu();

Yii::app()->clientScript->registerScript('manageCredentialsScript', "

    function validate () {
        auxlib.destroyErrorFeedbackBox ($('#class'));
        if ($('#class').val () === '') {
            auxlib.createErrorFeedbackBox ({
                'prevElem': $('#class'),
                'message': '" . Yii::t('app', 'Account type required') . "'
            });
            return false;
        }
        return true;
    }

", CClientScript::POS_HEAD);
?>

<div class="page-title icon profile">
    <h2><?php echo Yii::t('profile', 'Manage Passwords for Third-Party Applications'); ?></h2>
</div>
<div class="credentials-storage">
<?php
$crit = new CDbCriteria(array(
    'condition' => '(userId=:uid OR userId=-1) AND modelClass != "TwitterApp" AND 
        modelClass != "GoogleProject"',
    'order' => 'name ASC',
    'params' => array(':uid' => $profile->user->id),
)
);
$staticModel = Credentials::model();
$staticModel->private = 0;
if (Yii::app()->user->checkAccess('CredentialsSelectNonPrivate', array('model' => $staticModel)))
    $crit->addCondition('private=0', 'OR');
if (!Yii::app()->params->isAdmin) {
    $crit->addCondition('isBounceAccount=0', 'AND');
}
$dp = new CActiveDataProvider('Credentials', array(
    'criteria' => $crit,
));
$this->widget('zii.widgets.CListView', array(
    'dataProvider' => $dp,
    'itemView' => '_credentialsView',
    'itemsCssClass' => 'credentials-list',
    'summaryText' => '',
    'emptyText' => ''
));
?>

    <?php
    echo CHtml::beginForm(
            array('/profile/createUpdateCredentials'), 'get', array(
        'onSubmit' => 'return validate ();'
            )
    );
    echo CHtml::submitButton(
            Yii::t('app', 'Add New'), array('class' => 'x2-button', 'style' => 'float:left;margin-top:0'));
    $modelLabels = Credentials::model()->authModelLabels;
    unset ($modelLabels['TwitterApp']);
    $types = array_merge(array(null => '- ' . Yii::t('app', 'select a type') . ' -'), $modelLabels);
    echo CHtml::dropDownList(
            'class', 'EmailAccount', $types, array(
        'options' => array_merge(
                array(null => array('selected' => 'selected')), array_fill_keys(array_keys($modelLabels), array('selected' => false))),
        'class' => 'left x2-select'
            )
    );
    echo CHtml::endForm();
    ?>
</div>
