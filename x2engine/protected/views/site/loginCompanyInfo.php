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





if (X2_PARTNER_DISPLAY_BRANDING) {
?>
<div id="login-x2engine-partner-content">
    <?php
        $brandingFile = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'login.php';
        $brandingFileTemplate = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'login_example.php';
        if(file_exists($brandingFile)){
            require_once $brandingFile;
        }else{
            require_once $brandingFileTemplate;
        }
    ?>
</div>
<?php
}

?>
<div id="login-x2engine"<?php echo (X2_PARTNER_DISPLAY_BRANDING ? 'class="with-partner-branding"' : ''); ?> >
    <div class="cell company-logo-cell">
        <?php echo CHtml::image(Yii::app()->loginLogoUrl, 'X2Engine', array('id' => 'login-logo', 'width' => 60, 'height' => 60)); ?>
    </div>
    <div id='x2-info'>
        <div id="login-version">
            <span>Version <?php echo Yii::app()->params->version; ?>, <a href="http://www.x2engine.com">X2Engine, Inc.</a></span>
            <span><?php echo strtoupper(Yii::app()->getEditionLabel(true)); ?>
            </span>
        </div>
        
        <div>
        <?php 
        if(Yii::app()->settings->edition == 'opensource'){
            echo '&nbsp;&bull;&nbsp;'.CHtml::link("LICENSE", Yii::app()->baseUrl.'/LICENSE.txt');
        } ?>
        </div>
    </div>
</div>
