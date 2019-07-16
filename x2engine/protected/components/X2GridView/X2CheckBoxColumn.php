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




class X2CheckBoxColumn extends CCheckBoxColumn {

    /**
     * @var array $headerHtmlOptions  
     */
    public $headerHtmlOptions =  array ();
    public $headerCheckBoxHtmlOptions =  array ();

    public $width;

    public function init () {
        // allows width to be set for column using width property
        if ($this->width) {
            $this->headerHtmlOptions['style'] = isset ($this->headerHtmlOptions['style']) ? 
                $this->headerHtmlOptions['style'] : '';
            $this->htmlOptions['style'] = isset ($this->htmlOptions['style']) ? 
                $this->htmlOptions['style'] : '';
            $this->headerHtmlOptions['style'] .= 'width: '.$this->width.';';
            $this->htmlOptions['style'] .= 'width: '.$this->width.';';
        }
        return parent::init ();
    }

    /**
	 * Renders the header cell content.
	 * This method will render a checkbox in the header when {@link selectableRows} is greater than 1
	 * or in case {@link selectableRows} is null when {@link CGridView::selectableRows} is greater than 1.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function getHeaderCellContent()
	{
		if(trim($this->headerTemplate)==='')
		{
			return $this->grid->blankDisplay;
		}

		$item = '';
		if($this->selectableRows===null && $this->grid->selectableRows>1)
            /* x2modstart */ 
			$item = CHtml::checkBox(
                $this->id.'_all',false,
                array_merge (
                    array('class'=>'select-on-check-all'), $this->headerCheckBoxHtmlOptions));
            /* x2modend */
		elseif($this->selectableRows>1)
            /* x2modstart */    
			$item = CHtml::checkBox(
                $this->id.'_all',false, $this->headerCheckBoxHtmlOptions);
            /* x2modend */ 
		else
		{
			$item = parent::getHeaderCellContent();
		}

		return strtr($this->headerTemplate,array(
			'{item}'=>$item,
		));
	}

	/**
	 * Renders the data cell content.
	 * This method renders a checkbox in the data cell.
	 * @param integer $row the row number (zero-based)
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function getDataCellContent($row)
	{

		$data=$this->grid->dataProvider->data[$row];
		if($this->value!==null)
			$value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
		elseif($this->name!==null)
			$value=CHtml::value($data,$this->name);
		else
			$value=$this->grid->dataProvider->keys[$row];

		$checked = false;
		if($this->checked!==null)
			$checked=$this->evaluateExpression($this->checked,array('data'=>$data,'row'=>$row));

		$options=$this->checkBoxHtmlOptions;
		if($this->disabled!==null)
			$options['disabled']=$this->evaluateExpression($this->disabled,array('data'=>$data,'row'=>$row));

		$name=$options['name'];
		unset($options['name']);
		$options['value']=$value;
        /* x2modstart */ 
        // made id customizable through interface
        if (isset ($options['id'])) {
            $options['id'] = $this->evaluateExpression (
                $options['id'], array ('data' => $data, 'row' => $row));
        }
        if (!isset ($options['id']))
        /* x2modend */ 
		    $options['id']=$this->id.'_'.$row;
		return CHtml::checkBox($name,$checked,$options);
	}
}

?>
