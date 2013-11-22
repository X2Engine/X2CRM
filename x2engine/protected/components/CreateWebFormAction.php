<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */


class CreateWebFormAction extends CAction {

    /**
     * Create a web lead form with a custom style
     *
     * Currently web forms have all options passed as GET parameters. Saved web forms
     * are saved to the table x2_web_forms. Saving, retrieving, and updating a web form
     * all happens in this function. Someday this should be updated to be it's own module.
     *
     * 
     * This get request is for weblead/service type only, marketing/weblist/view supplies 
     * the form that posts for weblist type 
     *  
     */
    public function run(){
        $modelClass = $this->controller->modelClass;
        if ($modelClass === 'Campaign') $modelClass = 'Contacts';

        if($_SERVER['REQUEST_METHOD'] === 'POST'){ // save a web form
            if(empty($_POST['name'])){
                if ($modelClass === 'Contacts') {
                    echo json_encode(array(
                        'errors' => array(
                            'name' => Yii::t('marketing', 'Name cannot be blank.'))));
                } elseif ($modelClass === 'Services') {
                    echo json_encode(array(
                        'errors' => array(
                            'name' => Yii::t('marketing', 'Name cannot be blank.'))));
                }
                return;
            }

            if ($modelClass === 'Contacts')
                $type = !empty($_POST['type']) ? $_POST['type'] : 'weblead';
            elseif ($modelClass === 'Services')
                $type = 'serviceCase';
            
            $model = WebForm::model()->findByAttributes(
                array('name' => $_POST['name'], 'type' => $type));

            // check if we are updating an existing web form
            if(!isset($model)){
                $model = new WebForm;
                $model->name = $_POST['name'];
                $model->type = $type;
                $model->modelName = $modelClass;
                $model->visibility = 1;
                $model->assignedTo = Yii::app()->user->getName();
                $model->createdBy = Yii::app()->user->getName();
                $model->createDate = time();
            }

            //grab web lead configuration and stash in 'params'
            $whitelist = array('fg', 'bgc', 'font', 'bs', 'bc', 'tags');
            $config = array_filter(array_intersect_key($_POST, array_flip($whitelist)));
            //restrict param values, alphanumeric, # for color vals, comma for tag list
            $config = preg_replace('/[^a-zA-Z0-9#,]/', '', $config);
            if(!empty($config))
                $model->params = $config;
            else
                $model->params = null;

            /* x2prostart */
            if (PRO_VERSION) {
                if(isset($_POST['css'])) {
                    $model->css = $_POST['css'];
                }
                if(isset($_POST['fieldList'])) { 
                    $model->fields = urldecode ($_POST['fieldList']);
                }
                if ($modelClass === 'Contacts') {
                    if(isset($_POST['header'])) {
                        $model->header = $_POST['header'];
                    }
                    if(isset($_POST['user-email-template'])) {
                        $model->userEmailTemplate = $_POST['user-email-template'];
                    }
                    if(isset($_POST['weblead-email-template'])) {
                        $model->webleadEmailTemplate = $_POST['weblead-email-template'];
                    }
                }
            }
            /* x2proend */

            $model->updatedBy = Yii::app()->user->getName();
            $model->lastUpdated = time();

            if($model->save()){
                echo json_encode($model->attributes);
            }else{
                echo json_encode(array('errors' => $model->getErrors()));
            }
        }else{
            if ($modelClass === 'Contacts') {
                $condition = X2Model::model('Marketing')->getAccessCriteria()->condition;

                $forms = WebForm::model()->findAll('type="weblead" AND '.$condition);

                $this->controller->render(
                    'application.modules.marketing.views.marketing.webleadForm', 
                    array('forms' => $forms));
            } else if ($modelClass === 'Services') {
                $condition = X2Model::model('Services')->getAccessCriteria()->condition;

                // get service web forms (other option is 'weblead' used by marketing module)
                $forms = WebForm::model()->findAll('type="serviceCase" AND '.$condition); 

                $this->controller->render(
                    'application.modules.services.views.services.createWebFormView', 
                    array('forms' => $forms));
            }

        }
    }

}

?>
