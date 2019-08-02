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






$locked = Yii::app()->locked;
if(is_int($locked) && $locked == 0) {
    $locked = filemtime(Yii::app()->lockFile);
}
$isLocked = is_int($locked);

?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Lock or Unlock X2Engine'); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <div class="row">
            <p><?php echo Yii::t('admin', 'This feature is for shutting down X2Engine during periods of maintenance or whenever it might be favorable to prevent access and data entry. When the application is locked, it cannot be used except by the administrator, and all services (such as the web lead form and API) will be unavailable.'); ?></p>
            <?php if($isLocked): ?>
                <p><strong><?php echo Yii::t('admin', 'X2Engine is currently locked. Time it was locked: {time}', array('{time}' => Formatter::formatDateTime($locked))); ?></strong></p>
            <?php else: ?>
                <p><strong><?php echo Yii::t('admin', 'X2Engine is not currently locked.'); ?></strong></p>
            <?php
            endif;
            echo CHtml::link($isLocked ? Yii::t('admin', 'Unlock X2Engine') : Yii::t('admin', 'Lock X2Engine'), array('/admin/lockApp', 'toggle' => (string) (int) !$isLocked), array('class' => 'x2-button'));
            if($isLocked):
                ?>
                <br /><br /><p><?php echo Yii::t('admin', 'You can manually unlock the application by deleting the file {file} in {dir}', array('{file}' => '<em>"X2Engine.lock"</em>','{dir}'=>'protected/runtime'));
                ?></p>
<?php endif; ?>

        </div><!-- .row -->
    </div><!-- .form -->
</div><!-- .span-16 -->
