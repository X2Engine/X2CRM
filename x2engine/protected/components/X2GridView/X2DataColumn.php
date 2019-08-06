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
class X2DataColumn extends CDataColumn {

    private $_fieldType;

    protected $_data;

    public $fieldModel;

    public function getFieldType() {
        return isset($this->fieldModel['type']) ? $this->fieldModel['type'] : null;
    }

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data){
        $this->data = $data;
        $value = null;
        if($this->value !== null) {
            $value = $this->evaluateExpression(
                $this->value, array('data' => $this->data, 'row' => $row));
        } elseif($this->name !== null){
            $value = $this->data->renderAttribute(
                $this->name, false, true); 
            if($this->data->getField($this->name)->type == 'text')
                $value = preg_replace("/\<br ?\/?\>/"," ",$value);
        }
        echo $value === null ? $this->grid->nullDisplay : $value; 
    }

    /**
     * Overridden to allow title attribute to be evaluated.
     * TODO: remove this and use method in X2DataColumnGeneric instead. Refactor will involve 
     *  prepending 'php:' to all titles that need to be evaluated.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
        /* x2modstart */  
        if (isset ($options['title'])) 
            $options['title'] = Expression::evaluate (
                $options['title'], array('row'=>$row,'data'=>$data));
        /* x2modend */ 
		if($this->cssClassExpression!==null)
		{
			$class=$this->evaluateExpression($this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
		$this->renderDataCellContent($row,$data);
		echo '</td>';
	}
    public function getData(){
        return $this->_data;
    }

    public function setData($data){
        if(is_array($data)){
            if(isset($this->grid, $this->grid->modelName)){
                $model = X2Model::model($this->grid->modelName);
                foreach($data as $key=>$value){
                    if($model->hasAttribute($key)){
                        $model->$key=$value;
                    }
                }
                $this->_data = $model;
            }
        }else{
            $this->_data = $data;
        }
    }

    public function getFilterCellContent () {
        switch($this->fieldType){
            case 'boolean':
                return CHtml::activeDropdownList(
                    $this->grid->filter, $this->name, 
                    array(
                        '' => '- '.Yii::t('app', 'Select').' -', 
                        '1' => Yii::t('app', 'Yes'),
                        'false' => Yii::t('app', "No")
                    ), 
                    array(
                        'class' => 'x2-minimal-select-filtercol'
                    )
                );
                break;
            case 'dropdown':
                $dropdown = Dropdowns::model()->findByPk($this->fieldModel['linkType']);
                if($dropdown instanceof Dropdowns) {
                    $options = json_decode($dropdown->options,1);
                    if (!$dropdown->multi) {
                    $defaultOption = array('' => '- '.Yii::t('app', 'Select').' -');
                    $options = is_array($options) ? 
                        array_merge($defaultOption,$options) : $defaultOption;
                    }
                    //$selected = isset($options[$this->grid->filter->{$this->name}]) ? 
                        //$this->grid->filter->{$this->name} : '';
                    $dropdownList = CHtml::listData( // Used for filtering where keys are numeric
                        $options,
                        function($model){ // Generate value attr for option elem
                            return $model === "- ".Yii::t('app', 'Select')." -" ? '' : $model;
                        },
                        function($model){ // Generate text for option elem
                            return $model;
                        }
                    );
                    return CHtml::activeDropdownList(
                        $this->grid->filter, $this->name, $dropdownList,
                        array(
                            'class' => 'x2-minimal-select-filtercol'.
                                ($dropdown->multi ? 
                                 ' x2-multiselect-dropdown x2-datacolumn-multiselect' : ''),
                            'multiple' => $dropdown->multi ? 'multiple' : '',
                            'data-selected-text' => $dropdown->multi ? 'option(s)' : '',
                        )
                    );
                } else {
                    parent::renderFilterCellContent();
                }
                break;
            case 'visibility':
                return CHtml::activeDropDownList($this->grid->filter, $this->name,
                    array(
                        '' => '- '.Yii::t('app', 'Select').' -', 1 => Yii::t('app', 'Public'),
                        0 => Yii::t('app', 'Private'), 2 => Yii::t('app', 'User\'s Groups')),
                    array('class' => 'x2-minimal-select-filtercol'));
                break;
            case 'dateTime':
            case 'date':
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                $that = $this;
                $renderWidget = function () use ($that) {
                    echo Yii::app()->controller->widget('CJuiDateTimePicker', 
                        array(
                            'model' => $that->grid->filter, //Model object
                            'attribute' => $that->name, //attribute name
                            'mode' => 'date', //use "time","date" or "datetime" (default)
                            'options' => array(// jquery options
                                'dateFormat' => Formatter::formatDatePicker ('medium'),
                            ),
                            'htmlOptions' => array(
                                'id' => 'datePicker'.$that->name,
                                'class' => 'datePicker x2-gridview-filter-datepicker'
                            ),
                            'language' => (Yii::app()->language == 'en') ? 
                                '' : Yii::app()->getLanguage(),
                        ), true);
                };
                if ($this->grid->ajax) {
                    return X2Widget::ajaxRender ($renderWidget, true);
                } else {
                    return $renderWidget ();
                }
                break;
            default:
                return parent::getFilterCellContent ();
        }
    }
}
