<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Settings for the email dropbox.
 * 
 * @package X2CRM.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailDropboxSettings extends JSONEmbeddedModel {

    public $alias = null;
    public $createContact = 1;
    public $zapLineBreaks = 0;
    public $emptyContact = 1;
    public $logging = 0;
    public $ignoreEmptyName = 0;

    public function attributeLabels() {
        return array(
            'alias' => Yii::t('admin', 'Email capture address'),
            'createContact' => Yii::t('admin', "Create contacts from emails"),
            'zapLineBreaks' => Yii::t('admin', 'Zap line breaks'),
            'emptyContact' => Yii::t('admin', 'Create contacts when first and last name are missing'),
            'logging' => Yii::t('admin', 'Enable logging'),
            'ignoreEmptyName' => Yii::t('admin','Ignore empty sender name in forwarded message parse')
        );
    }

    public function rules() {
        return array(
            array('alias','email','allowEmpty'=>true),
            array('createContact,zapLineBreaks,emptyContact,logging,ignoreEmptyName','boolean'),
            array('alias,createContact,zapLineBreaks,emptyContact,logging,ignoreEmptyName','safe'),
        );
    }

    public function attributeNames() {
        return array_keys($this->attributeLabels());
    }

    public function detailView() {

    }

    /**
     * Generate HTML options for attribute labels.
     * @param type $name
     * @param type $htmlOptions
     * @return type
     */
    public function labelOptions($name,$htmlOptions) {
        return array_merge(array('for'=>$this->resolveName($name)),$htmlOptions);
    }

    public function modelLabel() {
        return Yii::t('admin','Email Dropbox Settings');
    }

    public function renderInputs() {
        $htmlOptions = array('style'=>'display:inline-block;margin-right: 10px;');
        echo CHtml::activeLabel($this,'alias',$this->labelOptions('alias',$htmlOptions));
        echo CHtml::activeTextField($this,'alias',$this->htmlOptions('alias',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', 'The address to use as the sender when sending error notification emails. By default, if left blank, the email dropbox will use the first addresses in the {tohf} or {cchf} field that contains {dbat}.', array('{tohf}' => 'To:', '{cchf}' => 'CC:', '{dbat}' => '"dropbox@"'))), '[?]',$htmlOptions);
        echo '<br />';
        echo CHtml::activeCheckBox($this,'createContact',$this->htmlOptions('createContact',$htmlOptions));
        echo CHtml::activeLabel($this,'createContact',$this->labelOptions('createContact',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', 'If disabled, the email dropbox will ignore any emails that are to or from addresses not matching any contacts in X2CRM. If enabled, new contacts will be created automatically using name info contained in the email.')), '[?]',$htmlOptions);
        echo '<br />';
        echo '<div style="margin-left:20px;'.((bool) $this->createContact ? '' : 'display:none').'" id="empty-contact">';
        echo CHtml::activeCheckBox($this,'emptyContact',$this->htmlOptions('emptyContact',$htmlOptions));
        echo CHtml::activeLabel($this,'emptyContact',$this->labelOptions('emptyContact',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', "If enabled, the email dropbox will create a new contact record associated with a new unique email address even if the first and last name cannot be found in the email. If disabled, it ignores all email that does not contain contacts' first and last names. This setting has no effect if {ccfe} is disabled.", array('{ccfe}' => '"' . Yii::t('admin', 'Create contacts from emails') . '"'))), '[?]',$htmlOptions);
        echo '</div>';
        echo CHtml::activeCheckBox($this,'zapLineBreaks',$this->htmlOptions('zapLineBreaks',$htmlOptions));
        echo CHtml::activeLabel($this,'zapLineBreaks',$this->labelOptions('zapLineBreaks',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', 'If enabled, the mail parser will (when extracting the body of an email) attempt to clear the text of artificial line breaks induced by RFC email format specifications (which limit lines to 78 characters). If disabled, the email parser will not do this.')), '[?]',$htmlOptions);
        echo '<br />';
        echo CHtml::activeCheckBox($this,'logging',$this->htmlOptions('logging',$htmlOptions));
        echo CHtml::activeLabel($this,'logging',$this->labelOptions('logging',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', 'If enabled, the email dropbox will record email capture events in a log file in protected/runtime. This option is useful for troubleshooting but will take up some extra disk space on a system that captures a high volume of emails.')), '[?]',$htmlOptions);
        echo '<br />';
        echo CHtml::activeCheckBox($this,'ignoreEmptyName',$this->htmlOptions('ignoreEmptyName',$htmlOptions));
        echo CHtml::activeLabel($this,'ignoreEmptyName',$this->labelOptions('ignoreEmptyName',$htmlOptions));
        echo CHtml::tag('span', array('class' => 'x2-hint','style'=>$htmlOptions['style'], 'title' => Yii::t('admin', "If disabled, the import will exit and send an error message email if the forwarded message header does not contain the sender's full name.")), '[?]',$htmlOptions);
        echo '<br />';
        echo "<script type=\"text/javascript\">
                (function($) {
                    $(\"[name='".$this->resolveName('createContact')."']\").change(function() {
                        if($(this).is(':checked'))
                            $('#empty-contact').fadeIn(300);
                        else
                            $('#empty-contact').fadeOut(300);
                    }).each(function(){
                        if(!$(this).is(':checked'))
                            $('#empty-contact').hide();
                        else
                            $('#empty-contact').show();
                    });
                })(jQuery);
              </script>";
    }

}

?>
