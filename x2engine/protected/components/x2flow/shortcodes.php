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






// Each comment line goes with the shortcode that comes immediately after it.

return array(
    /* Action Description is a weird case where it won't be matched by 'hasAttribute'
       and must be provided manually so that we can access the property */
    'actionDescription'=>'
        if($model instanceof Actions){
            return $model->actionDescription;
        }else{
            return null;
        }',

    'actionAssociatedRecord' => '
        if ($model instanceof Actions) {
            return X2Model::getModelOfTypeWithId (
                $model->associationType, $model->associationId, true);
        } else {
            return null;
        }
    ',

    /* Generate a link to the record */
    'link'=>'
        if($model->asa("LinkableBehavior")){
            if($model instanceof Actions){
                return $model->getLink(30, false);
            }else{
                return $model->getLink();
            }
        }else{
            return null;
        }',

    /* Return the current date, properly formatted */
    'date'=>'return Formatter::formatDate(time(), "long", false);',

    /* Return the current time, properly formatted */
    'time'=>'return Formatter::formatTime(time());',

    'timestamp' => 'return time ();',

    /* Return a combination date/time string, properly formatted. */
    'dateTime'=>'return Formatter::formatLongDateTime(time());',

    /* Return the Profile record of the current user */
    'user'=>'return X2Model::model("Profile")->findByAttributes(array("username"=>Yii::app()->suName));',

    /* Creates an unsubscribe link, used by Marketing emails */
    'unsub'=>'return \'<a href="\'.Yii::app()->createAbsoluteUrl(\'/marketing/marketing/click\',array(\'uid\' => "", \'type\' => \'unsub\', \'email\' => $model->email)).\'">\'.Yii::t(\'marketing\', \'unsubscribe\').\'</a>\';',

    /* Validate that a phone number only contains digits. */
    'validphone'=>'
        if($model->hasAttribute("phone")){
            if(preg_match(\'/^[0-9\-\(\)]+$/\',$model->phone)){
                return $model->phone;
            }
        }
        return null;',

    'returnValue' => '
        if (isset ($params["returnValue"]) && is_scalar ($params["returnValue"])) 
            return $params["returnValue"];
    ',
    'originalRecord'=>'
        if(isset($params["originalModel"]) && $params["originalModel"] instanceof X2Model){
            return $params["originalModel"];
        }
    '
);
?>
