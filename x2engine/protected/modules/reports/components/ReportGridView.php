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




class ReportGridView extends X2GridViewGeneric {
    public $itemsCssClass = 'items grid-report-items';
    public $enableDbPersistentGvSettings = false;

    /**
     * @var array $reportButtons buttons displayed in reports title bar
     */
    public $reportButtons = array ('print', 'email', 'export'); 

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'ReportDataColumn'; 

    public function run () {
        if (isset ($this->htmlOptions['class']) && 
            !preg_match ('/grid-view/', $this->htmlOptions['class'])) {

            $this->htmlOptions['class'] .= ' grid-view';
        }
        parent::run ();
    }

	/**
	 * Renders the pager.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function renderPager()
	{
		if(!$this->enablePagination)
			return;

		$pager=array();
		$class='CLinkPager';
		if(is_string($this->pager))
			$class=$this->pager;
		elseif(is_array($this->pager))
		{
			$pager=$this->pager;
			if(isset($pager['class']))
			{
				$class=$pager['class'];
				unset($pager['class']);
			}
		}
		$pager['pages']=$this->dataProvider->getPagination();

		if($pager['pages']->getPageCount()>1)
		{
			echo '<div class="'.$this->pagerCssClass.'">';
			$this->widget($class,$pager);
			echo '</div>';
		}
		else {
            /* x2modstart */ 
			echo '<div class="'.$this->pagerCssClass.' empty-pager">';
			$this->widget($class,$pager);
			echo '</div>';
            /* x2modend */ 
        }
	}

    // public function renderExportButton () {
    //     echo '<a title="'.Yii::t('app', 'Export to CSV').'"
    //         class="x2-button report-export-button">'.Yii::t('reports', 'Export').'</a>';
    // }

    // public function renderPrintButton () {
    //     echo '<a title="'.Yii::t('app', 'Print Report').'"
    //         class="x2-button report-print-button">'.Yii::t('reports', 'Print').'</a>';
    // }

    // public function renderEmailButton () {
    //     echo '<a title="'.Yii::t('app', 'Email Report').'"
    //         class="x2-button report-email-button">'.Yii::t('reports', 'Email').'</a>';
    // }

    // public function renderReportButtons () {
    //     echo '<div class="x2-button-group">';
    //     foreach ($this->reportButtons as $button) {
    //         switch ($button) {
    //             case 'export':
    //                 $this->renderExportButton ();
    //                 break;
    //             case 'print':
    //                 $this->renderPrintButton ();
    //                 break;
    //             case 'email':
    //                 $this->renderEmailButton ();
    //                 break;                
    //         }
    //     }
    //     echo '</div>';
    // }

    protected function getJSClassOptions () {
        $currPageRawData = $this->dataProvider->getData ();
        $arrayData = array ();
        // convert associated array to non-associative array to prevent 
        // JSONification from changing ordering
        $i = 0;
        foreach ($currPageRawData as $row) {
            $arrayData[] = array ();
            foreach ($row as $key => $val) {
                if ($key === X2Report::HIDDEN_ID_ALIAS) continue;
                $arrayData[$i][] = array ($key, $val);
            }
            $i++;
        }

        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'currPageRawData' => $arrayData,
                'headers' => array_map (function ($col) {
                    return isset ($col['name']) ? $col['name'] : null;
                }, $this->columns),
            ));
    }


}
