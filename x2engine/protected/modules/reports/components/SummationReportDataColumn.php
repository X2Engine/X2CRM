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
 * Adds column header dropdown menu for grouping and aggregate operations
 */

class SummationReportDataColumn extends ReportDataColumn {

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
			$class=$this->evaluateExpression(
                $this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
        /* x2modstart */       
        if ($this->name === 'subgrid-expand-button-column') {
            $this->renderSubgridExpandButton ($data);
        } else {
		    $this->renderDataCellContent($row,$data);
        }
        /* x2modend */ 
		echo '</td>';
	}

    /**
     * Renders button which expands/hides sub grid 
     * @param array $data
     */
    public function renderSubgridExpandButton ($data) {
        // group by attribute values are stored in html attribute so that they can be retrieved
        // in JS and sent to the server when the button is clicked
        echo '<button class="x2-button subgrid-expand-button" 
            title="'.Yii::t('reports', 'Expand').'"
            data-group-attr-values="'.
                CHtml::encode (CJSON::encode ($this->getGroupAttrValues ($data))).'">+</button>';
        echo '<button class="x2-button subgrid-collapse-button" 
            title="'.Yii::t('reports', 'Collapse').'" style="display: none;">-</button>';
    }

    /**
     * @param array $data
     * @return array group attr values indexed by attribute 
     */
    protected function getGroupAttrValues ($data) {
        $groupAttrs = $this->grid->groupAttrs;
        $groupAttrValues = array ();
       AuxLib::debugLogR ('$data = ');
        AuxLib::debugLogR ($data);

        foreach ($groupAttrs as $attr) {
            $groupAttrValues[$attr] = $data[$attr];
        }
        return $groupAttrValues;
    }

}
