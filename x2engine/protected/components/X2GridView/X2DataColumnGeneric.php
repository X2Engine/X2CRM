<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

class X2DataColumnGeneric extends CDataColumn {

    /**
     * @var string $filterType
     */
    public $filterType; 

	/**
	 * Renders the filter cell content.
	 * This method will render the {@link filter} as is if it is a string.
	 * If {@link filter} is an array, it is assumed to be a list of options, and a dropdown selector will be rendered.
	 * Otherwise if {@link filter} is not false, a text field is rendered.
	 * @since 1.1.1
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	protected function renderFilterCellContent()
	{
		if(is_string($this->filter)) {
			echo $this->filter;
		} elseif($this->filter!==false && $this->grid->filter!==null && $this->name!==null && 
            strpos($this->name,'.')===false) {

            /* x2modstart */ 
            if (isset ($this->filterType)) {
                echo $this->renderFilterCellByType ();
            /* x2modend */ 
			} elseif(is_array($this->filter)) {
                /* x2modstart */ 
                // removed prompt
				echo CHtml::activeDropDownList(
                    $this->grid->filter, $this->name, $this->filter,
                    array('id'=>false));
                /* x2modend */
			} elseif($this->filter===null) {
				echo CHtml::activeTextField($this->grid->filter, $this->name, array('id'=>false));
            }
		} else {
			parent::renderFilterCellContent();
        }
	}

    public function renderFilterCellByType () {
        $model = $this->grid->filter;
        switch ($this->filterType) {
            case 'date':
                return X2Html::activeDatePicker ($model, $this->name);
                break;
            case 'dateTime':
                return X2Html::activeDatePicker ($model, $this->name, array (), 'datetime');
                break;
        }
    }
}

?>
