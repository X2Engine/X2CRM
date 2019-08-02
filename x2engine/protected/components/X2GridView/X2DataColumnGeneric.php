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




class X2DataColumnGeneric extends CDataColumn {

    /**
     * @var string $filterType
     */
    public $filterType; 

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
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public function renderDataCell($row)
    {
        $data=$this->grid->dataProvider->data[$row];
        $options=$this->htmlOptions;
        /* x2modstart */ 
        $options = $this->evaluateHtmlOptions ($options, $data);
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

    /**
     * Allows php snippets to be included in html option values 
     */
    public function evaluateHtmlOptions (array $options, $data) {
        foreach ($options as $attr => $val) {
            if (preg_match ('/^php:/', $val)) {
                $val = preg_replace ('/^php:/', '', $val);
                $options[$attr] = $this->evaluateExpression ($val, array (
                    'data' => $data,
                ));
            }
        }
        return $options;
    }

	/**
	 * Renders the filter cell content.
	 * This method will render the {@link filter} as is if it is a string.
	 * If {@link filter} is an array, it is assumed to be a list of options, and a dropdown selector will be rendered.
	 * Otherwise if {@link filter} is not false, a text field is rendered.
	 * @since 1.1.1
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function getFilterCellContent ()
	{
		if(is_string($this->filter)) {
			return $this->filter;
		} elseif($this->filter!==false && $this->grid->filter!==null && $this->name!==null && 
            strpos($this->name,'.')===false) {

            /* x2modstart */ 
            if (isset ($this->filterType)) {
                return $this->renderFilterCellByType ();
            /* x2modend */ 
			} elseif(is_array($this->filter)) {
                /* x2modstart */ 
                // removed prompt
				return CHtml::activeDropDownList(
                    $this->grid->filter, $this->name, $this->filter,
                    array('id'=>false));
                /* x2modend */
			} elseif($this->filter===null) {
				return CHtml::activeTextField($this->grid->filter, $this->name, array('id'=>false));
            }
		} else {
			return parent::getFilterCellContent ();
        }
	}

    public function renderFilterCellByType () {
        $model = $this->grid->filter;
        $fieldName = $this->name;
        switch ($this->filterType) {
            case 'date':
                $model->$fieldName = Formatter::parseDate ($model->$fieldName);
                return X2Html::activeDatePicker ($model, $this->name);
                break;
            case 'dateTime':
                $model->$fieldName = Formatter::parseDate ($model->$fieldName);
                return X2Html::activeDatePicker ($model, $this->name, array (), 'datetime');
                break;
        }
    }
}

?>
