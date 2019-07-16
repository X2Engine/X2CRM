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





?>
<div class="page-title"><h2><?php echo $model->emailDropbox->modelLabel(); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <div class="row">
            <h3><?php echo Yii::t('admin', 'Introduction'); ?></h3>
            <?php echo Yii::t('admin', 'This feature allows X2Engine to receive and record emails at a special address.'); ?><br /><br />
            <?php echo Yii::t('admin', 'Before beginning, please see {emailConfig} on the X2Engine Wiki.', array('{emailConfig}' => CHtml::link(Yii::t('admin', 'Email Dropbox Configuration'), 'http://wiki.x2engine.com/wiki/E-Mail_Configuration#Configuring_the_.22Email_Dropbox.22_For_Receiving_Emails'))); ?>
        </div>
        <br /><hr />
        <div class="row">
            <h3><?php echo Yii::t('admin', 'Filesystem ownership and permissions'); ?></h3>
            <?php echo Yii::t('admin','This is important if using the direct integration method.').' '
                    .Yii::t('admin', 'The mail transfer agent must have read and write permissions to the directory "{path}". The ownership and permissions of {path} are as follows:', array('{path}' => 'protected/runtime')); ?><br /><br />
            <div class="cell span-3">
                <h4><?php echo Yii::t('admin', 'Ownership'); ?></h4>
                <?php
                $runtime = realpath(Yii::app()->basePath . '/runtime');
                echo "<strong>" . Yii::t('admin', 'User') . "</strong>:" . fileowner($runtime) . '<br />';
                echo "<strong>" . Yii::t('admin', 'Group') . "</strong>:" . filegroup($runtime) . '<br />';
                ?>
            </div>
            <div class="cell span-6">
                <h4><?php echo Yii::t('admin', 'Permissions'); ?></h4>
                <?php
                // Perform a preliminary check of the runtime folder
                //
                // The following code prints out the permissions of said folder.
                $mode = fileperms($runtime);
                $perms = array_fill_keys(array('Owner', 'Group', 'Any'), array());

                $info = '';
                $read = Yii::t('admin','read');
                $write = Yii::t('admin','write');
                $execute = Yii::t('admin','execute');
                $permTypes = array_fill_keys(array('Owner', 'Group', 'Any'), null);
                $permLabels = array_fill_keys(array($read, $write, $execute), false);
                $permBits = array(
                    'Owner' => array(
                        $read => 0x0100,
                        $write => 0x0080,
                        $execute => array(0x0040, 0x0800),
                    ),
                    'Group' => array(
                        $read => 0x0020,
                        $write => 0x0010,
                        $execute => array(0x0008, 0x0400),
                    ),
                    'Any' => array(
                        $read => 0x0004,
                        $write => 0x0002,
                        $execute => array(0x0001, 0x0200),
                    ),
                );

                foreach (array_keys($permTypes) as $type) {
                    $permTypes[$type] = Yii::t('admin', $type);
                    foreach (array_keys($permLabels) as $label) {
                        if (!$permLabels[$label])
                            $permLabels[$label] = Yii::t('admin', $label);
                        if ($label == $execute) {
                            if (($mode & $permBits[$type][$label][0]) && !($mode & $permBits[$type][$label][1])) {
                                $perms[$type][] = $label;
                            }
                        } else {
                            if ($mode & $permBits[$type][$label]) {
                                $perms[$type][] = $label;
                            }
                        }
                    }
                    if (empty($perms[$type]))
                        $perms[$type][] = '(' . Yii::t('admin', 'no permission') . ')';
                }
                $permInfo = '';
                foreach ($perms as $type => $rwx) {
                    $permInfo .= "<strong>{$permTypes[$type]}:</strong> " . implode(',', $rwx) . '<br />';
                }
                echo $permInfo;
                ?>
            </div>
        </div>
        <br /><hr />
        <div class="row">
            <h3><?php echo Yii::t('admin', 'Settings'); ?></h3>
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'settings-form',
            ));
            $model->emailDropbox->renderInputs();
            ?>
            <br />
            <p><?php echo Yii::t('admin','To control how error notification emails will be sent: see "{notif}".',array('{notif}'=>CHtml::link(Yii::t('admin','Notification Email Settings'),array('/admin/emailSetup')))); ?></p>
            <hr />
            <?php
            echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n";
            $this->endWidget();
            ?>
        </div>
    </div>
</div>
