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




Yii::import('application.components.WebFormDesigner.WebFormDesigner');
class ServiceWebFormDesigner extends WebFormDesigner {

    public $type = 'service';

    public $protoName = 'ServiceWebFormDesigner';

    public $url = '/services/services/webForm';

    public $saveUrl = '/services/createWebForm';

    public $defaultList = array('firstName', 'lastName', 'email', 'phone');

    
    public $excludeList = array('description');
    

    public $modelName = 'Services';

    public function getPackages () {
        // Specific Packages 
        $packages = array( 'js/WebFormDesigner/ServiceWebFormDesigner.js');
        
        
        if($this->edition == 'pro') {
           $packages[] = 'js/WebFormDesigner/ServiceWebFormDesignerPro.js';
       }
        

        // Default Packages
        $this->_packages = array_merge ( parent::getPackages(), array(
                'ServiceWebFormJS' => array (
                    'baseUrl' => Yii::app()->baseUrl, 
                    'js' => $packages,
                    'depends' => array('WebFormDesignerJS')
                ),
            )
        );

        return $this->_packages;
    }

    
    public function getActiveFields ($fields=null) {
        $fields = $this->getFields ('Contacts');
        
        parent::getActiveFields($fields);

        $field = Fields::model()->findAllByAttributes(
            array('modelName'=>'Services', 'fieldName'=>'description'));

        $field = $field[0];
        $type = 'textIcon';

        $this->displayCustomField ($field, $type, null, true);
    }
    

    public function getDescription() {
        return Yii::t('marketing',
            'Create a public form to receive new services cases. When the form is submitted, a new '.
            'service case will be created, and the case # will be sent to the email address '.
            'provided in the form.'); 
    }
}

?>
