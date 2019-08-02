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





Yii::app()->clientScript->registerCss('publicInfoCss',"
#domain-alias-explanation {
    background-color: rgb(223, 223, 223);
    padding: 11px;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -o-border-radius: 4px;
    border: 1px solid #C0C0C0;
    margin:10px;
}
#cname-record-example td {
    border-bottom: 1px solid rgb(194,194,194);
    padding: 4px;
}
");


?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Public Info Settings'); ?></h2></div>
<div class="admin-form-container">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
            ));
    ?>
    <div class="form">
        <?php echo $form->labelEx($model, 'externalBaseUrl'); ?><br />
        <p><?php 
        echo Yii::t('admin', 'This will be the web root URL to use for generating URLs to '.
            'public-facing resources, i.e. email tracking images, the web tracker, targeted '.
            'content etc.');
        ?></p>
        <?php

        ?>
        <p><?php 
        echo Yii::t('admin','If you want to track contacts on a website with a domain that is '.
            'different from the domain on which X2 is hosted, you\'ll have to set this to your '.
            'website domain alias (see below for information on setting up a domain alias).');
        ?>
        </p>
<?php

?>
        <p><?php 
        echo Yii::t('admin', 'You ');
          
        echo Yii::t('admin', ' also ');
         
        echo Yii::t('admin', 'should use this if the CRM is behind a firewall and you access X2Engine using a different URL than one would use to access it from the internet (i.e. a host name / IP address on a private subnet or VPN).'); 
        ?></p>
        <?php echo $form->textField($model, 'externalBaseUrl',array('style' => 'width: 90%')); ?>
        <?php echo CHtml::error($model, 'externalBaseUrl');?>

        <br /><br />
        
        <?php echo $form->labelEx($model,'externalBaseUri'); ?><br />
        <p><?php echo Yii::t('admin','If the relative path from the web root differs between how you are accessing it now and how it will be accessed through public-facing URLs, enter it here. For example, if the CRM is accessed within {samplePrivateUrl}, and public assets will be accessed within {samplePublicUrl}, set this value to {samplePublicUri}.',array(
            '{samplePrivateUrl}' => 'http://internaldomain.net/x2',
            '{samplePublicUrl}' => 'http://publicsite.com/crm',
            '{samplePublicUri}' => '/crm'
        )) ?></p>
        <?php echo $form->textField($model,'externalBaseUri'); ?>
        <?php echo CHtml::error($model, 'externalBaseUri');?>
<?php

?>
        <div id="domain-alias-explanation">
            <p><?php
        echo Yii::t('admin','To set up a website domain alias for tracking, you\'ll need to create a'.
            ' CNAME DNS resource record through your domain name registrar. Your CNAME record\'s name should'. 
            ' refer to a subdomain of your website and should point to the domain of your CRM.'); ?>
            </p>
            <p>
        <?php
        echo Yii::t('admin','For example, '.
            'if your website is on the domain {websiteDomain}, your domain alias could be {domainAlias}.'.
            ' If your CRM is hosted at {crmDomain}, {domainAlias} would be an alias for '.
            '{crmDomain}. Your CNAME record would then look as follows:', array (
                '{websiteDomain}' => 'www.example.com',  
                '{crmDomain}' => 'www.examplecrm.com',  
                '{domainAlias}' => 'www2.example.com',  
            )); ?>
            </p>
            <p>
              <table id="cname-record-example">
                  <tr>
                      <td>Name</td>
                      <td>Type</td>
                      <td>Value</td>
                  </tr>
                  <tr>
                      <td>www2.example.com</td>
                      <td>CNAME</td>
                      <td>www.examplecrm.com</td>
                  </tr>
              </table>
            </p>
        </div>
<?php

        echo CHtml::label(Yii::t('admin', 'Asset Domain Sharding'), 'Admin_enableAssetDomains'); ?><br />
        <p><?php
        echo Yii::t('admin', 'This will be the web root URL to use for generating URLs to '.
            'public-facing static assets, such as JavaScript and CSS stylesheets. This has '.
            'the benefit of allowing your browser to use more connections to fetch assets '.
            'and avoid the overhead of sending unnecessary cookies on each request.');
        ?></p>
        <p><?php
        echo Yii::t('admin', 'To configure your server to support a static asset domains, '.
            'first create a CNAME alias that resolves to your CRM, such as static1.example.com. '.
            'Then, ensure that your web server is accepting requests for these new domains, '.
            'for example, through Apache name-based virtual hosts.');
        ?></p>
        <p><?php
        echo Yii::t('admin', 'Note: the benefits of asset domain sharding are being superceded '.
            'with newer technologies such as SPDY and HTTP2 pipelining. Asset domain sharding '.
            'will provide the most benefit on a server using HTTP1.1, as it is designed to '.
            'work around limitiations in the protocol.');
        ?></p>

        <div id="assetDomainWarning" class="flash-notice hide"><?php
        echo X2Html::fa ('warning').' '.
            Yii::t('admin', 'Warning: enabling more than three asset domains for sharding '.
            'leads to diminishing returns. It is best to measure performance while adjusting '.
            'the number of asset domains to determine the most effective configuration.');
        ?></div>
            <?php echo $form->checkbox($model, 'enableAssetDomains'); ?>
            <?php echo $form->label($model, 'enableAssetDomains',
                array('style' => 'display: inline-block')); ?>
        <br />

        <?php
            $pendingAssetUrls = CJSON::encode ($model->assetBaseUrls);
            if (array_key_exists ('Admin', $_POST) && array_key_exists ('assetBaseUrls', $_POST['Admin'])) {
                $pendingAssetUrls =  $_POST['Admin']['assetBaseUrls'];
            }
            echo CHtml::hiddenField('Admin[assetBaseUrls]', $pendingAssetUrls);
        ?>
        <?php echo CHtml::error($model, 'assetBaseUrls');?>
        <div id='assetUrls'>
        </div>
        <?php echo CHtml::htmlButton (
            X2Html::fa ('plus').' '.Yii::t('admin', 'Add Asset Domain'),
            array(
                'id' => 'newAssetBaseUrl',
                'class' => 'x2-button small',
                'style' => 'padding: 1px;',
            )
        ); ?>

        <?php
            $fa_times = X2HTML::fa('times', array(
                'class' => 'removeAssetUrl',
                'style' => 'cursor: pointer'
            ));
            $assetDomainErrors  = array();
            if (isset($model->errors['assetBaseUrls']))
                $assetDomainErrors = $model->errors['assetBaseUrls'];
            Yii::app()->clientScript->registerScript ('assetBaseUrlJs', '
                if (typeof x2 == "undefined")
                    x2 = {};
                if (typeof x2.assetBaseUrl == "undefined")
                    x2.assetBaseUrl = {};
                x2.assetBaseUrl.removeAssetUrlButton = '.CJavaScript::encode($fa_times).';
                x2.assetBaseUrl.pendingAssetUrls = '.CJSON::encode ($model->assetBaseUrls).';

                /**
                 * Click handler to remove URL entry links
                 */
                x2.assetBaseUrl.removeAssetUrl = function() {
                    $(this).parent().remove();
                    var numDomains = $(".assetBaseUrlEntry").length;
                    if (numDomains < 4)
                        $("#assetDomainWarning").slideUp(333);
                };

                /**
                 * Create a new text input element for asset URLs
                 */
                x2.assetBaseUrl.createAssetUrlEntry = function() {
                    var urlEntry = $("<input>", {class: "assetBaseUrlInput"});
                    var urlDiv = $("<div>", {class: "assetBaseUrlEntry"})
                        .append (urlEntry)
                        .append (x2.assetBaseUrl.removeAssetUrlButton);
                    return urlDiv;
                }

                /**
                 * Create URL entry text fields for each existing asset domain
                 */
                x2.assetBaseUrl.renderExistingAssetUrls = function(entries) {
                    if(entries) {
                        $.each (entries, function(index, url) {
                            var urlDiv = x2.assetBaseUrl.createAssetUrlEntry();
                            $(urlDiv).find (".assetBaseUrlInput").val (url);
                            $("#assetUrls").append(urlDiv);
                            $(".assetBaseUrlEntry > .removeAssetUrl").click (x2.assetBaseUrl.removeAssetUrl);
                        });
                    }
                }

                /**
                 * Prepare click handler for adding asset domain entries
                 */
                $("#newAssetBaseUrl").click (function() {
                    var urlDiv = x2.assetBaseUrl.createAssetUrlEntry();
                    $("#assetUrls").append(urlDiv);
                    var numDomains = $(".assetBaseUrlEntry").length;
                    if (numDomains > 3)
                        $("#assetDomainWarning").slideDown(333);
                    $(".assetBaseUrlEntry > .removeAssetUrl").click (x2.assetBaseUrl.removeAssetUrl);
                });

                /**
                 * Set up click handler on form save to pack asset URLs into a JSON
                 * string in the hidden field #Admin_assetBaseUrls
                 */
                $("#save-button").click (function() {
                    var baseUrls = [];
                    $(".assetBaseUrlInput").each (function() {
                        baseUrls.push($(this).val());
                    });
                    $("#Admin_assetBaseUrls").val (JSON.stringify (baseUrls));
                });

                var assetBaseUrl = $("#Admin_assetBaseUrls").val();
                if (assetBaseUrl)
                    x2.assetBaseUrl.renderExistingAssetUrls (JSON.parse (assetBaseUrl));
            ', CClientScript::POS_READY);
        ?>
        <br /><br />
    </div><!-- .form -->

    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div><!-- .span-16 -->
