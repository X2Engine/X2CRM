<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

Yii::import('zii.widgets.grid.CGridView');

/**
 * Custom grid view display function.
 * 
 * Displays a dynamic grid view that permits save-able resizing and reordering of 
 * columns and also the adding of new columns based on the available fields for 
 * the model.
 * 
 * @package X2CRM.components 
 */
class X2GridView extends CGridView {
	
	public $selectableRows = 0;
	
	public $modelName;
	public $viewName;
	public $enableGvSettings = true;
	
	public $defaultGvSettings;
	
	public $specialColumns;
	public $excludedColumns;
	public $enableControls = false;
	public $enableTags = false;
	
	public $summaryText;
	
	protected $allFields = array();
	protected $allFieldNames = array();
	protected $specialColumnNames = array();
	protected $gvSettings = null;
	protected $columnSelectorId;
	protected $columnSelectorHtml;
	
	protected $ajax = false;
	
	
	public static function getFilterHint() {
	
		$text = Yii::t('app','<b>Tip:</b> You can use the following comparison operators with filter values to fine-tune your search.');
		$text .= '<ul class="filter-hint">';
		$text .= '<li><b>&lt;</b> '		.Yii::t('app','less than')				.'</li>';
		$text .= '<li><b>&lt;=</b> '	.Yii::t('app','less than or equal to')		.'</li>';
		$text .= '<li><b>&gt;</b> '		.Yii::t('app','greater than')			.'</li>';
		$text .= '<li><b>&gt;=</b> '	.Yii::t('app','greater than or equal to')	.'</li>';
		$text .= '<li><b>=</b> '		.Yii::t('app','equal to')					.'</li>';
		$text .= '<li><b>&lt;&gt</b> '	.Yii::t('app','not equal to')				.'</li>';
		$text .= '</ul>';

		return X2Info::hint($text,false);
	}
	
	
	public function init() {
		$this->excludedColumns = empty($this->excludedColumns)?array():array_fill_keys($this->excludedColumns,1);
//		die(var_dump($this->excludedColumns));
		// $this->id is the rendered HTML element's ID, i.e. "contacts-grid"
		$this->ajax = isset($_GET['ajax']) && $_GET['ajax']===$this->id;
		if($this->ajax)
			ob_clean();
		
		// $this->selectionChanged = 'js:function() { console.debug($.fn.yiiGridView.getSelection("'.$this->id.'")); }';
		
		// if(empty($this->modelName))
			// $this->modelName = $this->getId();
		if(empty($this->viewName))
			$this->viewName = $this->modelName;
		if($this->modelName=='Quotes')
			$this->modelName='Quote';
		
		
		$this->columnSelectorId = $this->getId() . '-column-selector';

		// Get gridview settings by looking in the URL:
		if(isset($_GET['gvSettings']) && isset($_GET['viewName']) && $_GET['viewName'] == $this->viewName) {
			$this->gvSettings = json_decode($_GET['gvSettings'],true);
			// unset($_GET['gvSettings']);
			// die(var_dump($this->gvSettings));
			
			ProfileChild::setGridviewSettings($this->gvSettings,$this->viewName);
		} else {
			$this->gvSettings = ProfileChild::getGridviewSettings($this->viewName);
		}
		// Use the hard-coded defaults (note: gvSettings has column name keys:
		if($this->gvSettings == null)
			$this->gvSettings = $this->defaultGvSettings;
		// die(var_dump($this->gvSettings));
		// die(var_dump(ProfileChild::getGridviewSettings($this->viewName)));
		
		// load names from $specialColumns into $specialColumnNames
		foreach($this->specialColumns as $columnName => &$columnData) {
			if(isset($columnData['header']))
				$this->specialColumnNames[$columnName] = $columnData['header'];
			else
				$this->specialColumnNames[$columnName] = X2Model::model($this->modelName)->getAttributeLabel($columnName);
		}
		
		// start allFieldNames with the special fields
		if(!empty($this->specialColumnNames))
			$this->allFieldNames = $this->specialColumnNames;
		
		// add controls column if specified
		if($this->enableControls)
			$this->allFieldNames['gvControls'] = Yii::t('app','Tools');
		
		$this->allFieldNames['gvCheckbox'] = Yii::t('app', 'Checkbox');

		// load fields from DB
		// $fields=Fields::model()->findAllByAttributes(array('modelName'=>ucwords($this->modelName)));
		$fields = X2Model::model($this->modelName)->getFields();
		
		$fieldPermissions = array();
		if(!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
			$rolePermissions = Yii::app()->db->createCommand()
				->select('fieldId, permission')
				->from('x2_role_to_permission')
				->join('x2_fields','x2_fields.modelName="'.$this->modelName.'" AND x2_fields.id=fieldId AND roleId IN ('.implode(',',Yii::app()->params->roles).')')
				->queryAll();
			// var_dump($rolePermissions);

			foreach($rolePermissions as &$permission) {
				if(!isset($fieldPermissions[$permission['fieldId']]) || $fieldPermissions[$permission['fieldId']] < (int)$permission['permission'])
					$fieldPermissions[$permission['fieldId']] = (int)$permission['permission'];
			}
		}
		
		// Begin setting fields
		foreach($fields as $field) {
			if (isset($this->excludedColumns[$field->fieldName]))
				continue;
			if((!isset($fieldPermissions[$field->id]) || $fieldPermissions[$field->id] > 0))
				$this->allFields[$field->fieldName] = $field;
		}
		
//		die(var_dump($this->allFields));
		
		// add tags column if specified
		if($this->enableTags)
			$this->allFieldNames['tags'] = Yii::t('app','Tags');
		
		

		foreach($this->allFields as $fieldName=>&$field) {
			$this->allFieldNames[$fieldName] = X2Model::model($this->modelName)->getAttributeLabel($field->fieldName);
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
		unset($_GET['columns']);	// prevents columns data from ending up in sort/pagination links
		unset($_GET['viewName']);
		unset($_GET['gvSettings']);
		
		



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
			$width = (!empty($width) && is_numeric($width))? $width : null;	// make sure width is reasonable
			
			// $isDate = in_array($columnName,array('createDate','completeDate','lastUpdated','dueDate', 'expectedCloseDate', 'expirationDate', 'timestamp','lastactivity'));
			
			$isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));
			
			$lang = (Yii::app()->language == 'en')? '':Yii::app()->getLanguage();
			
			//if($isDate)
				//$datePickerJs .= ' $("#'.$columnName.'DatePicker").datepicker('
					//.'$.extend({showMonthAfterYear:false}, {"dateFormat":"'.Formatter::formatDatePicker().'"})); ';
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

			} else if((array_key_exists($columnName, $this->allFields))) { // && $this->allFields[$columnName]->visible == 1)) {

				$newColumn['name'] = $columnName;
				$newColumn['id'] = 'C_'.$columnName;
				$newColumn['header'] = X2Model::model($this->modelName)->getAttributeLabel($columnName);
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				
				if($isCurrency) {
					$newColumn['value'] = 'Yii::app()->locale->numberFormatter->formatCurrency($data->'.$columnName.',Yii::app()->params->currency)';
					$newColumn['type'] = 'raw';
				} else if($columnName == 'assignedTo' || $columnName == 'updatedBy') {
					$newColumn['value'] = 'empty($data->'.$columnName.')?Yii::t("app","Anyone"):User::getUserLinks($data->'.$columnName.')';
					$newColumn['type'] = 'raw';
				} elseif($this->allFields[$columnName]->type=='date') {
					$newColumn['value'] = 'empty($data["'.$columnName.'"])? "" : Formatter::formatLongDate($data["'.$columnName.'"])';
				} elseif($this->allFields[$columnName]->type=='percentage') {
					$newColumn['value'] = '$data["'.$columnName.'"]!==null&&$data["'.$columnName.'"]!==""?((string)($data["'.$columnName.'"]))."%":null';
				} elseif($this->allFields[$columnName]->type=='dateTime') {
					$newColumn['value'] = 'empty($data["'.$columnName.'"])? "" : Yii::app()->dateFormatter->formatDateTime($data["'.$columnName.'"],"medium")';
				} elseif($this->allFields[$columnName]->type=='link') {
					$newColumn['value'] = '!is_numeric($data->'.$columnName.')?$data->'.$columnName.':(is_null($data->'.$columnName.'Model)?$data->'.$columnName.':$data->'.$columnName.'Model->getLink())';
					$newColumn['type'] = 'raw';
				} elseif($this->allFields[$columnName]->type=='boolean') {
					$newColumn['value']='$data->'.$columnName.'==1?Yii::t("actions","Yes"):Yii::t("actions","No")';
					$newColumn['type'] = 'raw';
				}elseif($this->allFields[$columnName]->type=='phone'){
                    $newColumn['type'] = 'raw';
                    $newColumn['value'] = 'X2Model::getPhoneNumber("'.$columnName.'","'.$this->modelName.'",$data->id)';
                }
			

				if(Yii::app()->language == 'en') {
					$format =  "M d, yy";
				} else {
		    		$format = Yii::app()->locale->getDateFormat('medium'); // translate Yii date format to jquery
		    		$format = str_replace('yy', 'y', $format);
		    		$format = str_replace('MM', 'mm', $format);
		    		$format = str_replace('M','m', $format);
		    	}
		    	
				/* $newColumn['filter'] = $isDate? $this->widget("zii.widgets.jui.CJuiDatePicker",array(
					'model'=>$this->filter, //Model object
					// 'id'=>$columnName.'DatePicker',
					'attribute'=>$columnName, //attribute name
					// 'mode'=>'datetime', //use 'time','date' or 'datetime' (default)
					// 'htmlOptions'=>array('style'=>'width:80%;'),
					'options'=>array(
						'dateFormat'=>$format,
					), // jquery plugin options
					'language'=>$lang,
				),true) : null; */
				
				$columns[] = $newColumn;
					
			} else if($columnName == 'gvControls') {
				$newColumn['id'] = 'C_gvControls';
				$newColumn['class'] = 'CButtonColumn';
				$newColumn['header'] = Yii::t('app','Tools');
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				if(Yii::app()->user->getName() != 'admin')
					$newColumn['template'] = '{view}{update}';
					
				$columns[] = $newColumn;
				
			} else if($columnName == 'tags') {
				$newColumn['id'] = 'C_'.'tags';
				// $newColumn['class'] = 'CDataColumn';
				$newColumn['header'] = Yii::t('app','Tags');
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
				$newColumn['value'] = 'Tags::getTagLinks("'.$this->modelName.'",$data->id,2)';
				$newColumn['type'] = 'raw';
				$newColumn['filter'] = CHtml::textField('tagField',isset($_GET['tagField'])? $_GET['tagField'] : '');
				
				
				$columns[] = $newColumn;
			} else if ($columnName == 'gvCheckbox') {
				$newColumn['id'] = 'C_gvCheckbox';
				$newColumn['class'] = 'CCheckBoxColumn';
				$newColumn['selectableRows'] = 2;
				$newColumn['headerHtmlOptions'] = array('colWidth'=>$width);
					
				$columns[] = $newColumn;
			}
		}		
		

		$this->columns = $columns;
		
		
		natcasesort($this->allFieldNames); // sort column names
		
		// generate column selector HTML
		$this->columnSelectorHtml = CHtml::beginForm(array('site/saveGvSettings'),'get')
			.'<ul class="column-selector" id="'.$this->columnSelectorId.'">';
		foreach($this->allFieldNames as $fieldName=>&$attributeLabel) {
			$selected = array_key_exists($fieldName,$this->gvSettings);
			$this->columnSelectorHtml .= "<li>"
			.CHtml::checkbox('columns[]',$selected,array('id'=>$fieldName.'_checkbox','value'=>$fieldName))
			.CHtml::label($attributeLabel,$fieldName.'_checkbox')
			."</li>";
		}
		$this->columnSelectorHtml .= '</ul></form>';
		// Yii::app()->clientScript->renderBodyBegin($columnHtml);
		// Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId().'_columnSelector',
		// "$('#".$this->getId()." table').after('".addcslashes($columnHtml,"'")."');
		
		// ",CClientScript::POS_READY);
		$themeURL = Yii::app()->theme->getBaseUrl();
		
		Yii::app()->clientScript->registerScript('logos',base64_decode(
			'JCh3aW5kb3cpLmxvYWQoZnVuY3Rpb24oKXt2YXIgYT0kKCIjcG93ZXJlZC1ieS14MmVuZ2luZSIpO2lmKCFhLmxlb'
			.'md0aHx8YS5hdHRyKCJzcmMiKSE9eWlpLmJhc2VVcmwrIi9pbWFnZXMvcG93ZXJlZF9ieV94MmVuZ2luZS5wbmciK'
			.'XskKCJhIikucmVtb3ZlQXR0cigiaHJlZiIpO2FsZXJ0KCJQbGVhc2UgcHV0IHRoZSBsb2dvIGJhY2siKX19KTs='));
		
		// add a dropdown to the summary text that let's user set how many rows to show on each page
		$this->summaryText = Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')
			. '<div class="form no-border" style="display:inline;"> '
			. CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
					'ajax' => array(
						'url' => $this->controller->createUrl('/profile/setResultsPerPage'),
						'complete' => "function(response) { 
							\$.fn.yiiGridView.update('{$this->id}', {" . 
								(isset($this->modelName)? "data: {'{$this->modelName}_page': 1}," : "") . "
								complete: function(jqXHR, status) {
									if(typeof(refreshQtip) == 'function') {
										refreshQtip();
									}
								}
							});
						}",
						'data' => 'js: {results: $(this).val()}',
					)
				))
			. ' </div>';
			// . Yii::t('app', ' results per page');

			
			
		// $this->afterAjaxUpdate = 'function(id, data) { '.$datePickerJs.' }';
		// if(!empty($this->afterAjaxUpdate))
			// $this->afterAjaxUpdate = "var callback = ".$this->afterAjaxUpdate."; if(typeof callback == 'function') callback();";
		
		// $this->afterAjaxUpdate = " function(id,data) { ".$this->afterAjaxUpdate." ".$datePickerJs;
				
		// if($this->enableGvSettings) {
			// $this->afterAjaxUpdate.="
			// $('#".$this->getId()." table').gvSettings({
				// viewName:'".$this->viewName."',
				// columnSelectorId:'".$this->columnSelectorId."',
				// ajaxUpdate:true
			// });";
		// }
		// $this->afterAjaxUpdate .= " } ";
			
		if(isset(Yii::app()->controller->module) && Yii::app()->controller->module->id=='contacts'){
			// after user moves to a different page, make sure the tool tips get added to the newly showing rows
			$this->afterAjaxUpdate = 'js: function(id, data) { refreshQtip(); $(".qtip-hint").qtip({content:false}); }';
        }
		parent::init();
	}
	
	public function run() { 
		$this->registerClientScript();

		// give this a special class so the javascript can tell it apart from the regular, lame gridviews
		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] = '';
		$this->htmlOptions['class'] .= ' x2-gridview';
		
		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

		$this->renderContent();
		$this->renderKeys();

		
		if($this->ajax) {
			// remove any external JS and CSS files
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			// remove JS for gridview checkboxes and delete buttons (these events use jQuery.on() and shouldn't be reapplied)
			Yii::app()->clientScript->registerScript('CButtonColumn#C_gvControls',null);
			Yii::app()->clientScript->registerScript('CCheckBoxColumn#C_gvCheckbox',null);

			$output = '';
			Yii::app()->getClientScript()->renderBodyEnd($output);
			echo $output;
			
			echo CHtml::closeTag($this->tagName);
			ob_flush();
			
			
			Yii::app()->end();;
		}
		echo CHtml::closeTag($this->tagName);
		
	}
	
	

	/**
	* Renders the data items for the grid view.
	*/
 	public function renderItems() {
		if($this->dataProvider->getItemCount() > 0 || $this->showTableOnEmpty) {
			echo "<table class=\"{$this->itemsCssClass}\">\n";
			$this->renderTableHeader();
			ob_start();
			$this->renderTableBody();
			$body = ob_get_clean();
			$this->renderTableFooter();
			echo $body; // TFOOT must appear before TBODY according to the standard.
			echo "</table>";
		} else {
			$this->renderEmptyText();
		}
	}

	/**
	 * Override of {@link CGridView::registerClientScript()}.
	 * 
	 * Adds scripts essential to modifying the gridview (and saving its configuration).
	 */
	public function registerClientScript() {
		parent::registerClientScript();
		// die('taco bell');
		if($this->enableGvSettings) {
			if(!$this->ajax) {
				Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/colResizable-1.2.x2.js');
				Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jquery.dragtable.x2.js');
				Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2gridview.js');
			}
			Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId().'_gvSettings',
			"$('#".$this->getId()." table').gvSettings({
				viewName:'".$this->viewName."',
				columnSelectorId:'".$this->columnSelectorId."',
				columnSelectorHtml:'".addcslashes($this->columnSelectorHtml,"'")."',
				ajaxUpdate:".($this->ajax?'true':'false').",
			});",CClientScript::POS_READY);
		}
	}
	
	/**
	 * Echoes the markup for the gridview's table header. 
	 */
	public function renderTableHeader() {
		if(!$this->hideHeader) {
			echo "<colgroup>";
			foreach($this->columns as $column) {
//				die(var_dump($this->columns));
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