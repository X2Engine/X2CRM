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




Yii::app()->clientScript->registerCssFile($this->module->assetsUrl . '/css/rsvp.css');
?>
<div class="page-title">
    <h2><?php echo Yii::t('calendar', 'Event RSVP'); ?></h2>
</div>
<div class="form">
    <div>
        <?php echo Yii::t('calendar', 'You are RSVPing for the following event:'); ?><br><br>
        <b><?php echo Yii::t('calendar', 'What:'); ?> </b><?php echo $action->actionDescription; ?><br>
        <b><?php echo Yii::t('calendar', 'When:'); ?> </b><?php echo Formatter::formatDueDate($action->dueDate, 'long', 'long') ?><br><br>
        <?php echo Yii::t('calendar', 'Please select one of the options below to confirm your status.'); ?>
        <div id="rsvp-status"></div>
    </div>
    <div>
        <?php echo X2Html::button(Yii::t('calendar', 'Yes'), array('data-value' => 'Yes', 'class' => 'x2-button left rsvp-button' . ($invite->status === 'Yes' ? ' disabled' : ''))); ?>
        <?php echo X2Html::button(Yii::t('calendar', 'Maybe'), array('data-value' => 'Maybe', 'class' => 'x2-button left rsvp-button' . ($invite->status === 'Maybe' ? ' disabled' : ''))); ?>
        <?php echo X2Html::button(Yii::t('calendar', 'No'), array('data-value' => 'No', 'class' => 'x2-button left rsvp-button' . ($invite->status === 'No' ? ' disabled' : ''))); ?>
    </div>
</div>

<?php
$confirmationMessage = Yii::t('calendar', 'Thanks for RSVPing! Your attendance has been updated to ');
$colors = ThemeGenerator::generatePalette(Yii::app()->params->profile->getTheme());
if ($colors['themeName'] === 'Default') {
    $highlightColor = 'lightblue';
    $backgroundColor = '';
} else {
    $highlightColor = $colors['highlight2'];
    $backgroundColor = $colors['content'];
}

Yii::app()->clientScript->registerScript('rsvp-buttons', "
    $('.rsvp-button').on('click',function(){
        var that = this;
        $.ajax({
            url: window.location,
            type: 'POST',
            data: {
                status: $(this).attr('data-value'),
                geoCoords: $('#geoCoords').val()
            },
            success: function(){
                $('.rsvp-button').removeClass('disabled');
                $(that).addClass('disabled');
                $('#rsvp-status').show()
                    .html('<p>".$confirmationMessage." \"' + $(that).attr('data-value') + '\"</p>')
                    .css({'background-color':'".$highlightColor."'})
                    .animate({'background-color':'".$backgroundColor."'}, 750);
            }
        });
    });
");

Yii::app()->clientScript->registerGeolocationScript(); ?>
<input type="hidden" name="geoCoords" id="geoCoords"></input>
