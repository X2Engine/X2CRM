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




Yii::import('application.components.WebFormDesigner.*');
class WebLeadFormDesigner extends WebFormDesigner {

    public $type = 'weblead';

    public $protoName = 'WebleadFormDesigner';

    public $url = '/contacts/contacts/weblead';

    public $saveUrl = '/marketing/marketing/webleadForm';

    public $modelName = 'Contacts';

    public $defaultList = array ('firstName',
        'lastName',
        'email',
        'phone',
        'backgroundInfo'
    );
   
    public $excludeList = array (
        'account',
        'assignedTo',
        'dupeCheck',
        'id',
        'visibility',
        'trackingKey' 
    );

    public function getPackages () {
        // Specific Packages 
        $packages = array(
            'js/WebFormDesigner/WebleadFormDesigner.js',

        );

        
        if ($this->edition=='pro') {
            $packages[] = 'js/WebFormDesigner/WebleadFormDesignerPro.js';
        }
        

        // Default Packages
        $this->_packages = array_merge ( parent::getPackages(), array(
                'WebLeadFormJS' => array (
                    'baseUrl' => Yii::app()->baseUrl, 
                    'js' => $packages,
                    'depends' => array('WebFormDesignerJS')
                ),
            )
        );

        return $this->_packages;
    }

    
    public function getTranslations () {
        return array_merge(parent::getTranslations(), array(
            "Custom HTML cannot be added to the web form until it has been saved." => 
            Yii::t('marketing', "Custom HTML cannot be added to the web form until it has been saved."),
            "HTML cannot be empty." => Yii::t('marketing', "HTML cannot be empty."),
            "HTML saved" => Yii::t('marketing', "HTML saved"),
            "HTML removed" => Yii::t('marketing', "HTML removed")
        ));
    }
    

    public function getDescription() {
        $desc = Yii::t('marketing','Create a public form to receive new {module}.', array('{module}'=>lcfirst(Modules::displayName(true, "Contacts")))).'<br/>'.'<br/>';
        $desc .= Yii::t('marketing','If no lead routing has been configured, all new {module} will be assigned to "Anyone".', array('{module}'=>lcfirst(Modules::displayName(false, "Contacts")))).'<br/>'.'<br/>'; 

        $desc .= Yii::t('marketing','If you want to keep your current HTML forms but still get web leads into X2, please see the wiki article located here: {link}',array(
                '{link}' => CHtml::link(Yii::t('marketing','Web Lead API'),'http://wiki.x2engine.com/wiki/Web_Lead_API_(new)', array('target'=>'_blank')),
            ));

        return $desc;

    }


}

?>