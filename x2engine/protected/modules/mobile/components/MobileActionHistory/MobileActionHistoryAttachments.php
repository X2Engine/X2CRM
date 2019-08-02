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




Yii::import ('application.modules.mobile.components.MobileActionHistory.historyItems.*');

class MobileActionHistoryAttachments extends X2Widget {

    public $model;

    public $refresh = false;
    
    public $type = 'attachments';

    public $instantiateJSClassOnInit = true;

    public $checkIfJSClassIsDefined = true;

    public $JSClass = 'MobileActionHistoryAttachments';

    private $pageSize = 30;


    protected $_packages;
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'MobileActionHistoryAttachments' => array(
                    'baseUrl' => Yii::app()->controller->asa ('MobileControllerBehavior')
                        ->assetsUrl,
                    'js' => array(
                        'js/MobileActionHistoryAttachments.js',
                    ),
                    'depends' => array ('X2Widget')
                ),
            ));
        }
        return $this->_packages;
    }

    public function getHistoryItem (array $data) {
        $className = 'Mobile'.ucfirst ($data['type']).'Item';

        $action = Actions::model()->findByPk($data['id']);
        if(!$action){
            return false;
        }
        if (!class_exists ($className) || 
            !is_subclass_of ($className, 'MobileHistoryItem')) {
            $item = new MobileHistoryItem;
            $item->action = $action;
            return $item;
        } else {
            $item = new $className;
            $item->action = $action;
            return $item;
        }
    }

    public function run () {
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->theme->baseUrl.'/css/actionHistory.css'); 
        $ret = call_user_func_array ('parent::'.__FUNCTION__, func_get_args ()); 
        $this->render ('mobileActionHistory', array (
            'type' => $this->type,
            'dataProvider' => $this->getDataProvider (),
        ));
        return $ret;
    }

    private function getDataProvider () {
        $retArr = History::getCriteria (
            $this->model->id, 
            X2Model::getAssociationType (get_class ($this->model)), 
            false, $this->type);
        return new CSqlDataProvider($retArr['cmd'], array(
            'totalItemCount' => $retArr['count'],
            'params' => $retArr['params'],
            'pagination' => array(
                'pageSize' => $this->pageSize,
            ),
        ));

    }

}

?>
