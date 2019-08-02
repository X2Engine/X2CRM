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






Yii::app()->clientScript->registerResponsiveCss('responsiveWebTrackerCss',"

@media (max-width: 439px) {
    #web-tracker-cooldown-setting {
        width: auto !important;
    }
}

");

Yii::app()->clientScript->registerCss('webTrackerCss',"

#thresholdSlider {
    width:300px;
    margin-left:15px;
    display:inline-block;
}

");

$this->pageTitle = Yii::t('marketing','Web Tracker Settings');
$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead',
    
    'webtracker', 'x2flow',
);

$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);

$this->insertMenu($menuOptions);


$timeLengths = array(
    1=>Yii::t('app','{n} sec',1),
    5=>Yii::t('app','{n} sec',5),
    15=>Yii::t('app','{n} sec',15),
    30=>Yii::t('app','{n} sec',30),
    60=>Yii::t('app','{n} min',1),
    300=>Yii::t('app','{n} min',5),
    900=>Yii::t('app','{n} min',15),
    1800=>Yii::t('app','{n} min',30),
    3600=>Yii::t('app','{n} hour',1),
    7200=>Yii::t('app','{n} hours',2),
    28800=>Yii::t('app','{n} hours',8),
    86400=>Yii::t('app','{n} day',1),
);
// 1-based indeces
$cooldownIndex = array_search($admin->webTrackerCooldown,array_keys($timeLengths));
if($cooldownIndex === false)
    $cooldownIndex = count($timeLengths);    // default to last value (unlimited)
else
    $cooldownIndex++;


Yii::app()->clientScript->registerScript('updateCooldownSlider','

$("#cooldown, #enableTracker").change(function(){ $("#save-button").addClass("highlight"); });

$("#cooldown").change(function(){ 
    $("#cooldownSlider").slider("value",$(this).prop("selectedIndex")+1); 
});

',CClientScript::POS_READY);

// Module titles to be used in descriptions
$modTitles = array(
    'contact' => Modules::displayName(false, "Contacts"),
    'contacts' => Modules::displayName(true, "Contacts"),
);

Tours::loadTips('marketing.webTracker');
?>
<div class="page-title icon marketing">
    <h2><?php echo Yii::t('marketing','Web Tracker Code'); ?></h2>
</div>
<div class="form">
    <?php 
    echo Yii::t('marketing','If you want to track {contacts} on a website with a domain that is '.
        'different from the domain on which X2 is hosted, you\'ll have to configure your {link}.',
        array(
            '{link}' => CHtml::link(
                Yii::t('marketing','Public Info Settings'), Yii::app()->createUrl ('/admin/publicInfo')),
            '{contacts}' => lcfirst($modTitles['contacts']),
        )
    ); 
    ?>
</div>
<div class="form">
<h4><?php echo Yii::t('marketing','Web Tracker Code') .':'; ?></h4>
<?php echo Yii::t('marketing','This HTML tag will allow you to track {contacts} who visit your '.
    'website.', array('{contacts}' => lcfirst($modTitles['contacts']))); ?><br>
<?php echo Yii::t('marketing','Paste this code into the body section of every page of your '.
    'website.'); ?><br>

<?php
// Append a slash to the external base URL if one is not already present
$absBaseUrl = Yii::app()->getExternalAbsoluteBaseUrl ();
if ($absBaseUrl[strlen($absBaseUrl)-1] !== '/')
    $absBaseUrl .= '/';
?>
<textarea id="embedcode" style="background:#eee" class='x2-extra-wide-input'>
<script src="<?php 
    //echo Yii::app()->request->getHostInfo (),Yii::app()->getBaseUrl (); 
    echo $absBaseUrl;
    ?>webTracker.php"></script>
</textarea>
<?php
    echo CHtml::link (
        X2Html::fa('share').Yii::t('marketing', 'Export'),
        array('marketing/exportWebTracker'),
        array('class' => 'x2-button')
    );
    echo X2Html::hint (Yii::t ('marketing',
        'Generate and export the web tracker JavaScript. This can be uploaded to your site in '.
        'place of the standard web tracker embed code, which is useful when using tracking under '.
        'SSL. Please note: the code that is generated is specific to your X2CRM installation.'
    ));
?>
<?php //echo Yii::t('marketing','Copy and paste this code into your website to include the web lead form.'); ?><br>

<?php echo Yii::t('marketing','<b>Note:</b> {contacts} can be tracked only if they filled out the '.
    'web lead capture form, or clicked on a tracking link in an email campaign.',
    array('{contacts}'=>$modTitles['contacts'])); ?><br><br>

<?php echo CHtml::beginForm(); ?>
<h4><b><?php echo Yii::t('marketing','Web Tracker Settings'); ?></b></h4>
<div class="row">
    <?php 
    echo Yii::t('marketing','You can enable or disable the web tracker. The tracker will '.
        'ignore repeat hits from a given {contact} during the cooldown period.', array('{contact}'=>lcfirst($modTitles['contact']))); 
    echo Yii::t('marketing','If a {contact} visits several pages in a short time, you '.
        'will only get one notification.', array('{contact}'=>lcfirst($modTitles['contact']))); 
    echo Yii::t('marketing','Turn it down all the way to receive notifications about '.
        'every page hit.'); 
    ?>
</div><br>
<div class="row">
    <div class="cell" style="width:120px;">
        <?php echo CHtml::activeLabel($admin,'enableWebTracker'); ?>
        <?php echo CHtml::activeDropDownList(
            $admin,'enableWebTracker',array(1=>Yii::t('app','Enable'),0=>Yii::t('app','Disable')),
            array('id'=>'enableTracker','style'=>'')); ?>
    </div>
    <div id='web-tracker-cooldown-setting' class="cell" style="width:396px;">
        <?php 
        echo CHtml::activeLabel($admin,'webTrackerCooldown'); 
        echo CHtml::activeDropDownList(
            $admin,'webTrackerCooldown',$timeLengths,
            array('id'=>'cooldown','style'=>'float:left;')); 
        $this->widget('zii.widgets.jui.CJuiSlider', array(
            'value' => $cooldownIndex,
            // additional javascript options for the slider plugin
            'options' => array(
                'min'=>1,
                'max'=>count($timeLengths),
                'slide'=>'js:function(event,ui) {
                    $("#save-button").addClass("highlight");
                    $("#cooldown>option:nth-child("+ui.value+")").attr("selected",true);
                }',
            ),
            'htmlOptions' => array(
            'style' => 'width:300px;margin:10px;display:inline-block;',
            'id' => 'cooldownSlider'
            ),
        ));
        ?>
    </div>
</div><br>
<div class="row">
    <div class="cell" style="width:120px;">
        <?php echo CHtml::activeLabel($admin,'enableGeolocation'); ?>
        <?php echo CHtml::activeDropDownList(
            $admin,'enableGeolocation',array(1=>Yii::t('app','Enable'),0=>Yii::t('app','Disable')),
            array('id'=>'enableGeolocation','style'=>'')); ?>
    </div>
</div><br>
<?php 
if (Yii::app()->contEd('pla')) { ?>
<h4><b><?php echo Yii::t('marketing','X2Identity Settings'); ?></b></h4>
<div class="row">
    <?php
    echo Yii::t('marketing', "X2Identity uses browser fingerprinting as an alternative ".
            "method to the traditional web tracker. Browser fingerprinting is not reliant ".
            "on cookies and instead identifies a {contact} based on certain browser attributes ".
            "that make them unique. ", array('{contact}'=>lcfirst($modTitles['contact'])));
    echo Yii::t('marketing', "If browser fingerprinting is enabled, the tracker will ".
            "attempt to identify a {contact} based on their browser settings. Note that ".
            "this is probabilistic by nature, and will not always be completely accurate.",
                array('{contact}'=>lcfirst($modTitles['contact']))).
            "<br /><br />";
    echo Yii::t('marketing', "You can set the threshold for the minimum number of attributes that ".
            "must be equal in order to constitute a partial match. It is recommended to keep ".
            "higher values to prevent false positives. You can also tune the ".
            "maximum number of anonymous contacts and actions associated with them in order ".
            "to limit flooding to the database. The fingerprint tracker will obey the same ".
            "cooldown period as the web tracker.").
            "<br /><br />";
    echo Yii::t('marketing', "Anonymous contacts are created upon submission of the newsletter ".
            "form, or when a user who has not yet been associated with a fingerprint visits a ".
            "page embedded with the web tracker. Once these anonymous contacts submit the ".
            "web lead form, their lead score and activity history will be migrated over to an ".
            "ordinary {contact} record.", array('{contact}'=>lcfirst($modTitles['contact'])));
    ?>
</div><br>
<div class="row">
    <div class="cell" style="width:120px;">
        <?php
            Yii::app()->clientScript->registerScript('updateThresholdSlider','
                $("#threshold, #enableFingerprinting").change(function(){
                    $("#save-button").addClass("highlight");
                });

                $("#threshold").change(function(){ 
                    $("#thresholdSlider").slider("value",$(this).prop("selectedIndex") + 1); 
                });
            ',CClientScript::POS_READY);

            echo CHtml::activeLabel($admin, 'enableFingerprinting');
            echo CHtml::activeDropDownList($admin, 'enableFingerprinting',
                array(1=>Yii::t('app','Enable'),0=>Yii::t('app','Disable')),
                array('id'=>'enableFingerprinting','style'=>''));
        ?>
    </div>
<div class="row">
    <div class="cell">
        <?php
            $hostnameLookupHint = Yii::t('marketing',
                "Resolve hostnames into IP addresses. This incurs a slight penalty while "
                ."performing DNS resolution, and it may be preferrable to disable hostname "
                ."lookups for performance reasons.");
            echo CHtml::activeLabel($admin, 'performHostnameLookups');
            echo CHtml::activeCheckBox(
                $admin,'performHostnameLookups',array(
                    'id'=>'performHostnameLookups',
            ));
            echo X2Html::hint($hostnameLookupHint, false, null, true);
        ?>
    </div>
</div>
<div class="row">
    <div class="cell">
        <?php
            $disableAnonNotifsHint = Yii::t('marketing',
                "This will filter notifications for AnonContact web activity without affecting "
                ."the total number of notifications. These can be reenabled at any time to reveal "
                ."past web activity visits in your notifications.");
            echo CHtml::activeLabel($admin, 'disableAnonContactNotifs');
            echo CHtml::activeCheckBox(
                $admin,'disableAnonContactNotifs',array(
                    'id'=>'disableAnonContactNotifs',
            ));
            echo X2Html::hint($disableAnonNotifsHint, false, null, true);
        ?>
    </div>
</div>
<div class="row">
    <div class="cell">
        <?php 
            echo CHtml::activeLabel($admin, 'identityThreshold');
            $validThresholds = array();
            $fingerprintAttrNumber = sizeof (Fingerprint::getFingerprintAttributeNames ());
            for($i = 1; $i <= $fingerprintAttrNumber; $i++)
                $validThresholds[$i] = $i;
            echo CHtml::activeDropDownList(
                $admin,'identityThreshold',$validThresholds,
                array(
                    'id'=>'threshold',
                )); 
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => $admin->identityThreshold,
                // additional javascript options for the slider plugin
                'options' => array(
                    'min' => 1,
                    'max' => $fingerprintAttrNumber,
                    'slide' => 
                        'js:function(event,ui) {
                            $("#save-button").addClass("highlight");
                            $("#threshold>option:nth-child("+ui.value+")").attr("selected",true);
                        }',
                ),
                'htmlOptions' => array(
                    'id' => 'thresholdSlider'
                ),
            ));
        ?>
    </div>
</div>
</div><br>
<div class="row">
    <div class="cell" style="width:160px;">
        <?php
            Yii::app()->clientScript->registerScript('updateMaxAnonContactsSlider','
                $("#maxAnonContacts").change(function(){
                    $("#save-button").addClass("highlight");
                    $("#maxAnonContactsSlider").slider("value", $(this).val()); 
                });
            ',CClientScript::POS_READY);
            echo CHtml::activeLabel($admin, 'maxAnonContacts');
            echo CHtml::activeTextField(
                $admin,'maxAnonContacts',
                array('id'=>'maxAnonContacts','style'=>'float:left;'));
        ?>
    </div>
    <div class="cell" style="width:80px">
        <?php
            Yii::app()->clientScript->registerScript('toggleMaxAnonContactsSlider', '
                $("#overwriteMaxAnonContacts").change(function() {
                    $("#maxAnonContactsSlider").toggle();
                });
            ');
            echo CHtml::label(Yii::t('marketing', 'Disable Slider'), 'overwriteMaxAnonContacts');
            echo CHtml::checkBox('overwriteMaxAnonContacts', false, array('id'=>'overwriteMaxAnonContacts'));
        ?>
    </div>
    <div class="cell">
        <?php 
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => $admin->maxAnonContacts,
                // additional javascript options for the slider plugin
                'options' => array(
                    'min' => 100,
                    'max' => 20000,
                    'step' => 100,
                    'slide' => 'js:function(event,ui) {
                                $("#save-button").addClass("highlight");
                                $("#maxAnonContacts").val($(this).slider("value"));
                            }',
                ),
                'htmlOptions' => array(
                    'style' => 'width:300px;margin:10px;display:inline-block;',
                    'id' => 'maxAnonContactsSlider'
                ),
            ));
        ?>
    </div>
</div><br>
<div class="row">
    <div class="cell" style="width:160px;">
        <?php
            Yii::app()->clientScript->registerScript('updateMaxAnonActionsSlider','
                $("#maxAnonActions").change(function(){
                    $("#save-button").addClass("highlight");
                    $("#maxAnonActionsSlider").slider("value",$(this).val()); 
                });
            ',CClientScript::POS_READY);
            echo CHtml::activeLabel($admin, 'maxAnonActions');
            echo CHtml::activeTextField(
                $admin,'maxAnonActions',
                array('id'=>'maxAnonActions','style'=>'float:left;'));
        ?>
    </div>
    <div class="cell" style="width:80px">
        <?php
            Yii::app()->clientScript->registerScript('toggleMaxAnonActionsSlider', '
                $("#overwriteMaxAnonActions").change(function() {
                    $("#maxAnonActionsSlider").toggle();
                });
            ');
            echo CHtml::label(Yii::t('marketing', 'Disable Slider'), 'overwriteMaxAnonActions');
            echo CHtml::checkBox('overwriteMaxAnonActions', false, array('id'=>'overwriteMaxAnonActions'));
        ?>
    </div>
    <div class="cell">
        <?php 
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => $admin->maxAnonActions,
                // additional javascript options for the slider plugin
                'options' => array(
                    'min' => 100,
                    'max' => 40000,
                    'step' => 100,
                    'slide' => 'js:function(event,ui) {
                                $("#save-button").addClass("highlight");
                                $("#maxAnonActions").val($(this).slider("value"));
                            }',
                ),
                'htmlOptions' => array(
                    'style' => 'width:300px;margin:10px;display:inline-block;',
                    'id' => 'maxAnonActionsSlider'
                ),
            ));
        ?>
    </div>
</div><br>
<?php }
 ?>

<?php 
echo CHtml::submitButton(
    Yii::t('app','Save'),array('class'=>'x2-button left','id'=>'save-button','style'=>'')); 
echo CHtml::endForm(); 
?>

</div>
