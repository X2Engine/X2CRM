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

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Display column for attributes of X2Model subclasses.
 */
class X2DataColumn extends CDataColumn {

    protected $_data;

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data){
        $this->data = $data;
        if($this->value !== null)
            $value = $this->evaluateExpression($this->value, array('data' => $this->data, 'row' => $row));
        elseif($this->name !== null){
            $value = $this->data->renderAttribute($this->name, false, true); // CHtml::value($data,$this->name);
        }
        echo $value === null ? $this->grid->nullDisplay : $value; //  $this->grid->getFormatter()->format($value,$this->type);
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

}

