<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

Yii::import('zii.widgets.grid.CGridView');

class X2GridView extends CGridView {
	
	public $modelName;
	public $viewName;
	public $enableGvSettings = true;
	
	public $defaultGvSettings;
	
	public $specialColumns;
	public $enableControls = false;
	
	private $allFields = array();
	private $allFieldNames = array();
	private $specialColumnNames = array();
	private $gvSettings = null;
	private $columnSelectorId;
	
	public function init() {
		
		
		// if(empty($this->modelName))
			// $this->modelName = $this->getId();
		if(empty($this->viewName))
			$this->viewName = $this->modelName;
		
		
		
		$this->columnSelectorId = $this->getId() . '-column-selector';

		if(isset($_GET['gvSettings']) && isset($_GET['viewName']) && $_GET['viewName'] == $this->viewName) {
			$this->gvSettings = json_decode($_GET['gvSettings'],true);
			// unset($_GET['gvSettings']);
			// die(var_dump($this->gvSettings));
			
			ProfileChild::setGridviewSettings($this->gvSettings,$this->viewName);
		} else {
			$this->gvSettings = ProfileChild::getGridviewSettings($this->viewName);
		}
		if($this->gvSettings == null)
			$this->gvSettings = $this->defaultGvSettings;
		// die(var_dump($this->gvSettings));
		// die(var_dump(ProfileChild::getGridviewSettings($this->viewName)));
		
		// load names from $specialColumns into $specialColumnNames
		foreach($this->specialColumns as $columnName => &$columnData) {
			if(isset($columnData['header']))
				$this->specialColumnNames[$columnName] = $columnData['header'];
			else
				$this->specialColumnNames[$columnName] = CActiveRecord::model($this->modelName)->getAttributeLabel($columnName);
		}
		
		// start allFieldNames with the special fields
		if(!empty($this->specialColumnNames))
			$this->allFieldNames = $this->specialColumnNames;
		
		// add controls column if specified
		if($this->enableControls)
			$this->allFieldNames['gvControls'] = Yii::t('contacts','Tools');
		
		
		// load fields from DB
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>ucwords($this->modelName)));
		foreach($fields as $field){
			$this->allFields[$field->fieldName] = $field;
		}
		// die(var_dump(array_keys($this->allFields)));
		
		

		foreach($this->allFields as $fieldName=>&$field) {
			$this->allFieldNames[$fieldName] = $field->attributeLabel;
		}
		
		
		
		// update columns if user has submitted data
		if(isset($_GET['columns']) && isset($_GET['viewName']) && $_GET['viewName'] == $this->viewName) {	// has the user changed column visibility?

			foreach(array_keys($this->gvSettings) as $key) {
				$index = array_search($key,$_GET['columns']);	// search $_GET['columns'] for the column
				if($index === false)							// if it's not in there,
					unset($this->gvSettings[$key]);					// delete that junk
				else											// othwerise, remove it from $_GET['columns']
					unset($_GET['columns'][$index]);			// so the next part doesn't add it a second time
			}
			foreach(array_keys($this->allFieldNames) as $key) {							// now go through $allFieldNames and add any fields that
				if(!isset($this->gvSettings[$key]) && in_array($key,$_GET['columns']))	// are present in $_GET['columns'] but not already in the list
					$this->gvSettings[$key] = 80;										// default width of 80
			}
		}
		
		



		// adding/removing columns changes the total width,
		// so let's scale the columns to match the correct total (590px)
		$totalWidth = array_sum(array_values($this->gvSettings));
		
		if($totalWidth > 0) {
			$widthFactor = (585 ) / $totalWidth; //- count($this->gvSettings)
			$sum = 0;
			$scaledSum = 0;
			foreach($this->gvSettings as $columnName => &$columnWidth) {
				$sum += $columnWidth;
				$columnWidth = round(($sum) * $widthFactor)-$scaledSum;		// map each point onto the nearest integer in the scaled space
				$scaledSum += $columnWidth;
			}
		}
		// die(var_dump($this->gvSettings).' '.$this->viewName);
		ProfileChild::setGridviewSettings($this->gvSettings,$this->viewName);	// save the new Gridview Settings

		// die(var_dump($this->gvSettings));

		$columns = array();

		$datePickerJs = '';

		foreach($this->gvSettings as $columnName => $width) {
			
			// $width = (!empty($width) && is_numeric($width))? 'width:'.$width.'px;' : null;	// make sure width is reasonable, then convert it to CSS
			$width = (!empty($width) && is_numeric($width))? $width : null;	// make sure width is reasonable, then convert it to CSS
			
			$isDate = in_array($columnName,array('createDate','completeDate','lastUpdated','dueDate','timestamp'));
			
			$isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));
			
			$lang = (Yii::app()->language == 'en')? '':Yii::app()->getLanguage();
			
			if($isDate)
				$datePickerJs .= ' $("#'.$columnName.'DatePicker").datepicker('
					.'$.extend({showMonthAfterYear:false}, jQuery.datepicker.regional["'.$lang.'"], {"dateFormat":"yy-mm-dd"})); ';
					// .'{"showAnim":"fold","dateFormat":"yy-mm-dd","changeMonth":"true","showButtonPanel":"true","changeYear":"true","constrainInput":"false"}));';
			
			
			$newColumn = array();

			if(array_key_exists($columnName,$this->specialColumnNames)) {
				
				$newColumn = $this->specialColumns[$columnName];
				// $newColumn['name'] = 'lastName';
				$newColumn['id'] = 'C_'.$columnName;
				// $newColumn['header'] = Yii::t('contacts','Name');
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				// $newColumn['value'] = 'CHtml::link($data->firstName." ".$data->lastName,array("view","id"=>$data->id))';
				// $newColumn['type'] = 'raw';
				// die(print_r($newColumn));
				$columns[] = $newColumn;

			} else if((array_key_exists($columnName, $this->allFields) && $this->allFields[$columnName]->visible == 1)) {

				$newColumn['name'] = $columnName;
				$newColumn['id'] = 'C_'.$columnName;
				$newColumn['header'] = $this->allFields[$columnName]->attributeLabel;
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				
				if($isDate)
					$newColumn['value'] = 'date("Y-m-d",$data->'.$columnName.')';
				else if($isCurrency) {
					$newColumn['value'] = 'Yii::app()->locale->numberFormatter->formatCurrency($data->'.$columnName.',Yii::app()->params->currency)';
					$newColumn['type'] = 'raw';
				} else if($columnName == 'assignedTo')
					$newColumn['value'] = 'empty($data->assignedTo)?Yii::t("app","Anyone"):$data->assignedTo';
				
				
				$newColumn['filter'] = $isDate? $this->widget("zii.widgets.jui.CJuiDatePicker",array(
					'model'=>$this->filter, //Model object
					// 'id'=>$columnName.'DatePicker',
					'attribute'=>$columnName, //attribute name
					// 'mode'=>'datetime', //use 'time','date' or 'datetime' (default)
					// 'htmlOptions'=>array('style'=>'width:80%;'),
					'options'=>array(
						'dateFormat'=>'yy-mm-dd',
					), // jquery plugin options
					'language'=>$lang,
				),true) : null;
				
				$columns[] = $newColumn;
					
			} else if($columnName == 'gvControls') {
				$newColumn['id'] = 'C_'.'gvControls';
				$newColumn['class'] = 'CButtonColumn';
				$newColumn['header'] = Yii::t('app','Tools');
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				if(Yii::app()->user->getName() != 'admin')
					$newColumn['template'] = '{view}{update}';
				$columns[] = $newColumn;
			}

			
		}
		
	
		// $this->afterAjaxUpdate = 'function(id, data) { '.$datePickerJs.' }';
		if(!empty($this->afterAjaxUpdate))
			$this->afterAjaxUpdate = "var callback = ".$this->afterAjaxUpdate."; if(typeof callback == 'function') callback();";
		
		$this->afterAjaxUpdate = " function(id,data) { ".$this->afterAjaxUpdate." ".$datePickerJs;
				
		if($this->enableGvSettings) {
			$this->afterAjaxUpdate.="
			$('#".$this->getId()." table').gvSettings({
				viewName:'".$this->viewName."',
				columnSelector:'".$this->columnSelectorId."',
				ajaxUpdate:true
			});";
		}
		$this->afterAjaxUpdate .= " } ";
		
		

		$this->columns = $columns;
		
		
		parent::init();
	}

	/**
	* Renders the data items for the grid view.
	*/
	public function renderItems() {
		if($this->dataProvider->getItemCount()>0 || $this->showTableOnEmpty) {
			echo "<table class=\"{$this->itemsCssClass}\">\n";
			$this->renderTableHeader();
			ob_start();
			$this->renderTableBody();
			$body=ob_get_clean();
			$this->renderTableFooter();
			echo $body; // TFOOT must appear before TBODY according to the standard.
			echo "</table>";
		} else {
			$this->renderEmptyText();
		}
		
		if($this->enableGvSettings) {
			echo CHtml::beginForm(array('site/saveGvSettings'),'get'); ?>
			<ul class="column-selector" id="<?php echo $this->columnSelectorId; ?>">
			<?php foreach($this->allFieldNames as $fieldName=>&$attributeLabel) {

				$selected = array_key_exists($fieldName,$this->gvSettings);
				echo "<li>";
				echo CHtml::checkbox('columns[]',$selected,array('id'=>$fieldName.'_checkbox','value'=>$fieldName));
				echo CHtml::label($attributeLabel,$fieldName.'_checkbox');
				echo "</li>\n";

			} ?></ul>
			<?php echo CHtml::endForm();
		}
	}

	
	
	
	public function run() {
		parent::run();
	}

	
	public function registerClientScript() {
		parent::registerClientScript();
		
		if($this->enableGvSettings) {
			Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/colResizable-1.2.x2.js');
			Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jquery.dragtable.x2.js');
			Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/gvSettings.js');
			Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId().'_gvSettings',
			"$(function() {
				$('#".$this->getId()." table').gvSettings({
					viewName:'".$this->viewName."',
					columnSelector:'".$this->columnSelectorId."'
				});
			});",CClientScript::POS_HEAD);
		}
	}
	
	public function renderTableHeader() {
		if(!$this->hideHeader) {
			echo "<colgroup>";
			foreach($this->columns as $column) {
				echo '<col width="'.$column->headerHtmlOptions['colWidth'].'">';
				// $column->id = null;
				$column->headerHtmlOptions['colWidth'] = null;
			}
			echo "</colgroup>\n";

			echo "<thead>\n";

			if($this->filterPosition===self::FILTER_POS_HEADER)
					$this->renderFilter();

			echo "<tr>\n";
			foreach($this->columns as $column)
					$column->renderHeaderCell();
			echo "</tr>\n";

			if($this->filterPosition===self::FILTER_POS_BODY)
					$this->renderFilter();

			echo "</thead>\n";
		}
		else if($this->filter!==null && ($this->filterPosition===self::FILTER_POS_HEADER || $this->filterPosition===self::FILTER_POS_BODY)) {
			echo "<colgroup>";
			foreach($this->columns as $column) {
				echo '<col width="'.$column->headerHtmlOptions['colWidth'].'">';
				// $column->id = null;
				$column->headerHtmlOptions['colWidth'] = null;
			}
			echo "</colgroup>\n";
		
		
			echo "<thead>\n";
			$this->renderFilter();
			echo "</thead>\n";
		}
	}
}
?>