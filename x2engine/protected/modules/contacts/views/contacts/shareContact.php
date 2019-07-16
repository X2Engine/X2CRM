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




$authParams['X2Model'] = $model;
$menuOptions = array(
    'all', 'lists', 'create', 'view', 'edit', 'share', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

Yii::app()->clientScript->registerPackage('emailEditor');
Yii::app()->clientScript->registerScript('editorSetup', 'createCKEditor("input");', CClientScript::POS_READY);



?>
<div class="page-title icon contacts">
    <h2><span class="no-bold"><?php echo Yii::t('contacts', 'Share {module}', array('{module}' => Modules::displayName(false))); ?>:</span> <?php echo CHtml::encode($model->firstName . " " . $model->lastName); ?></h2>
</div>
<?php
if (!empty($status)) {
    $index = array_search('200', $status);
    if ($index !== false) {
        unset($status[$index]);
        $email = '';
        $subject = '';
    }
    echo '<div class="form">';
    foreach ($status as &$status_msg) {
        echo $status_msg . " \n";
    }
    echo '</div>';
}
?>

<div class="form" style="padding: 20px;">
    <h4 style="margin-top: 0px;"><?php echo Yii::t('marketing', 'Share Url'); ?></h4>
    <?php  ?>
    <form method="POST" name="share-contact-form">
        <?php
        echo X2Html::getFlashes();
        ?>
        <h4<?php if (in_array('email', $errors)) echo ' class="error"'; ?>><?php echo Yii::t('contacts', 'E-Mail'); ?></h4>
        <input type="text" name="email" placeholder="Email Subject" size="50"<?php if (in_array('email', $errors)) echo ' class="error"'; ?> value="<?php if (!empty($email)) echo $email; ?>">
        <textarea name="body" id="input" style="height:200px;width:558px;"<?php if (in_array('body', $errors)) echo ' class="error"'; ?>><?php echo $body; ?></textarea>
        <input type="submit" class="x2-button" value="<?php echo Yii::t('app', 'Share'); ?>" />
        <?php echo X2Html::csrfToken(); ?>
    </form>
</div>
