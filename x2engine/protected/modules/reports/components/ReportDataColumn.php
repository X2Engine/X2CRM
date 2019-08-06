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




class ReportDataColumn extends CDataColumn {

    /**
     * @var string $attribute
     */
    public $attribute; 

    /**
     * @var string $modelType
     */
    public $modelType; 

    /**
     * @var $fns
     */
    public $fns = array (); 

    /**
     * @var CModel $_model
     */
    private $_model; 

	/**
	 * Renders a data cell.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
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

    public function getModel () {
        if (!isset ($this->_model)) {
            $modelType = $this->modelType;
            if (isset ($modelType)) {
                $this->_model = $modelType::model ();
            }
        }
        return $this->_model;
    }

    public function renderDate ($value, $dateFn) {
        switch ($dateFn) {
            case 'second':
            case 'minute':
            case 'hour':
            case 'day':
            case 'year':
                return $value;
            case 'month':
                return Yii::app()->locale->getMonthName ($value, 'wide');
        }
    }

    private $_dateFn; 
    public function getDateFn () {
        if (!isset ($this->_dateFn)) {
            foreach ($this->fns as $fn) {
                if (in_array ($fn, array ('second', 'minute', 'hour', 'day', 'year', 'month'))) {
                    $this->_dateFn = $fn;
                    break;
                }
            }
        }
        return $this->_dateFn;
    }

	/**
     * Overrides {@link parent::renderDataCellContent} to add model-based rendering
	 * @param integer $row
	 * @param mixed $data
	 */
	protected function renderDataCellContent ($row, $data) {
        $model = $this->getModel ();
        $value = null;
        //AuxLib::debugLogR ('rendering attr '.$this->attribute);
        //AuxLib::debugLogR ('name: '.$this->name);
        if (isset ($data[$this->name]) && $data[$this->name] === X2Report::EMPTY_ALIAS) {
            echo $this->grid->nullDisplay;
            return;
        } elseif (isset ($data[$this->name]) && $model !== null && $this->attribute !== null && 
            $this->name !== null && $this->getDateFn () === null) {

            $attr = $this->attribute;
            $model->$attr = $data[$this->name];
            if ($attr === 'name') {
                //$model->id = $data[X2Report::HIDDEN_ID_ALIAS];
                $modelRecord = $model::model()->findByAttributes(array('name' => $model->$attr));
                $model->id = $modelRecord->id;
                $value = $model->link;
            } else {
                $value = $model->renderAttribute ($attr);
                if($attr === "stageNumber") {
                    $records = Yii::app()->db->createCommand('SELECT * FROM x2_workflow_stages WHERE id='.(string)$model->$attr)->queryAll();
                    if (!empty($records))
                        $value = $records[0]['name'];
                }
            }
        } elseif (isset ($data[$this->name])) {
            if ($this->getDateFn ()) {
                $value = ReportsFormatter::renderDate ($data[$this->name], $this->getDateFn ());
            } else {
                $value = CHtml::encode ($data[$this->name]);  
            }
        } else {
            parent::renderDataCellContent ($row, $data);
            return;
        }
		echo $value === null ? $this->grid->nullDisplay : $value;
	}
}

?>
