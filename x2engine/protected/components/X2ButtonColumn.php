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




Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Display column for attributes of X2Model subclasses.
 */
class X2ButtonColumn extends CButtonColumn {

    public $viewButtonImageUrl = false; 
    public $updateButtonImageUrl = false; 
    public $deleteButtonImageUrl = false; 
    public $name;
    
    public $viewButtonUrl='
        $data instanceof X2Model ? 
            Yii::app()->createUrl(
                "/".lcfirst (X2Model::getModuleName (get_class ($data)))."/view",
                array("id"=>$data->primaryKey)) :
            Yii::app()->controller->createUrl("view", array("id"=>$data->primaryKey))';
    public $updateButtonUrl='
        $data instanceof X2Model ? 
            Yii::app()->createUrl(
                "/".lcfirst (X2Model::getModuleName (get_class ($data)))."/update",
                array("id"=>$data->primaryKey)) :
            Yii::app()->controller->createUrl("update", array("id"=>$data->primaryKey))';
    public $deleteButtonUrl='
        $data instanceof X2Model ? 
            Yii::app()->createUrl(
                "/".lcfirst (X2Model::getModuleName (get_class ($data)))."/delete",
                array("id"=>$data->primaryKey)) :
            Yii::app()->controller->createUrl("delete", array("id"=>$data->primaryKey))';

    /**
	 * Registers the client scripts for the button column.
	 */
	protected function registerClientScript()
	{
		$js=array();
		foreach($this->buttons as $id=>$button)
		{
			if(isset($button['click']))
			{
				$function=CJavaScript::encode($button['click']);
				$class=preg_replace('/\s+/','.',$button['options']['class']);
                /* x2modstart */ 
				$js[]= "
                    $(document).unbind ('click.CButtonColumn".$id."');
                    $(document).on (
                        'click.CButtonColumn".$id."','#{$this->grid->id} a.{$class}',$function);
                ";
                /* x2modend */ 
			}
		}

		if($js!==array())
			Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id, implode("\n",$js));
	}


	/**
	 * Initializes the default buttons (view, update and delete).
	 */
	protected function initDefaultButtons()
	{
        $this->viewButtonLabel = 
            "<span class='fa fa-search' title='".CHtml::encode (Yii::t('app', 'View record'))."'>
             </span>";
        $this->updateButtonLabel = 
            "<span class='fa fa-edit' title='".CHtml::encode (Yii::t('app', 'Edit record'))."'>
             </span>";
        $this->deleteButtonLabel = 
            "<span class='fa fa-times x2-delete-icon' 
              title='".CHtml::encode (Yii::t('app', 'Delete record'))."'></span>";
        parent::initDefaultButtons ();
	}

}
