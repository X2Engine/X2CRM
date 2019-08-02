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






require_once('protected/components/util/CrontabUtil.php');
?>
<h2><?php echo installer_t('Schedule a Cron Task for Automation (optional)'); ?></h2><hr>
    <?php echo installer_t('To trigger scheduled or delayed tasks automatically in X2CRM requires adding a task to the local cron table.');?><br />
    <input type="checkbox" id="startCron" name="startCron" value="1" /> <?php echo installer_t('Create a cron task for X2CRM'); ?>

    <br>
    <div id="start-cron" style="display: none">

        <div id="top-form">
        </div>
        <?php
        $cronRunnerUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['SERVER_NAME'].str_replace('install.php', '', $_SERVER['REQUEST_URI']).'index.php/api/x2cron';
        $config['cron']['cmd'] = "curl $cronRunnerUrl &>/dev/null";
        $config['cron']['tag'] = 'default';
        $config['cron']['desc'] = installer_t('Run delayed or recurring tasks within X2CRM');
        $data = CrontabUtil::cronJobToForm($config['cron']);
        echo CrontabUtil::schedForm($data, 'cron', $config['cron']['cmd'], 'default', $config['cron']['desc']);
        ?>
    </div>
    <div id="cron-response-box" style="color:red"></div>
    <br>

 <script>
 function testCron(callback){
     $.ajax({
         type: "post",
         url: "initialize.php",
     data: "testCron=1",
         dataType: 'json'
     }).done(function(r) {
        if(r.error){
         $('#cron-response-box').html(r.message);
         $('#startCron').prop('checked',false);
    } else {
        callback();
    }

     });
 }     

$(function(){
    $("#startCron").click( function() {
         if($("#startCron").is(":checked")) {
             testCron(function(){$("#start-cron").slideDown(300);});
         } else {
            $("#start-cron").slideUp(300);
         }
    });
});
 </script>
