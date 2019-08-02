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




/**
 * Description of FileSystemObject
 *
 * @package application.modules.docs.components
 */
class FileSystemObject {

    public $id;
    public $parentId;
    public $type;
    public $objId;
    public $name;
    public $createdBy;
    public $lastUpdated;
    public $updatedBy;
    public $visibility;
    public $title;
    public $isParent = false;
    
    public static function getListViewHeader(){
        $linkHeader = X2Html::tag(
            'div', array('class' => 'file-system-object-link',), Yii::t('docs','Name'));
        $attrHeaderContent = X2Html::tag(
            'span', array('class' => 'file-system-object-owner'), Yii::t('docs', 'Owner'))
            . X2Html::tag(
                'span', 
                array('class' => 'file-system-object-last-updated'), Yii::t('docs', 'Last Updated'))
            . X2Html::tag(
                'span', 
                array('class' => 'file-system-object-visibility'), Yii::t('docs', 'Visibility'));
        $attrHeader = X2Html::tag(
            'div', array('class' => 'file-system-object-attributes'), $attrHeaderContent);
        $headerContent = X2Html::tag(
            'div', array('class' => 'file-system-clear-fix'), $linkHeader . $attrHeader);
        return X2Html::tag('div', array('class' => 'file-system-header page-title'), $headerContent);
    }

    public function __construct($options) {
        foreach ($options as $key => $value) {
            if (property_exists(get_class(), $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getIcon() {
        return X2Html::fa($this->type === 'folder' ? 'folder-open' : 'file-text');
    }

    public function getLink() {
        if ($this->type === 'folder') {
            return X2Html::link(CHtml::encode($this->name), '#', array(
                'class'=>'folder-link pseudo-link',
                'data-id'=>$this->objId,
            ));
        } else {
            return X2Html::link(
                CHtml::encode($this->name), 
                Yii::app()->controller->createUrl('/docs/view', array('id' => $this->objId)));
        }
    }

    public function renderName () {
        $options = array (
            "class" => "file-system-object-name",
        );

        if ($this->title && preg_match ('/^[a-zA-Z0-9 \-]+$/', $this->title)) 
            $options['title'] = $this->title;
        return $this->getIcon ().CHtml::tag ("span", $options, $this->getLink ());
    }
    
    public function getOwner(){
        if(!empty($this->createdBy)){
            $user = User::getUserLinks($this->createdBy, false, true);
            if(!empty($user)){
                return $user;
            }
        }
        return '&nbsp;';
    }
    
    public function getVisibility(){
        if(is_null($this->visibility)){
            $this->visibility = 1;
        }
        $visibilities = X2PermissionsBehavior::getVisibilityOptions ();
        return $visibilities[$this->visibility];
    }
    
    public function getLastUpdateInfo(){
        if(!empty($this->updatedBy) && !empty($this->lastUpdated)){
            return Formatter::formatDateTime($this->lastUpdated);
        }
        return '&nbsp;';
    }
    
    public function validDroppable() {
        return $this->type === 'folder' && 
            (is_null($this->objId) || $this->objId > 0 || $this->parentId > 0) && 
            ($this->name != '..' || is_null($this->parentId) || $this->parentId > 0);
    }

    public function validDraggable() {
        return $this->name !== '..' && $this->objId > 0;
    }

}
