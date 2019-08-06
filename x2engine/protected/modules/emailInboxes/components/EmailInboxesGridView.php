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




class EmailInboxesGridView extends X2GridViewGeneric {

    /**
     * @var int $emailCount
     */
    public $emailCount = 100000; 

    public $gridViewJSClass = 'emailInboxesGridSettings';
    public $enableResponsiveTitleBar = false;
    protected $_massActions = array (
        'MassEmailDelete', 'MassMoveToFolder', 'MassMarkAsRead', 'MassMarkAsUnread', 
        'MassAssociateEmails');
    public $pagerClass = 'EmailInboxesPager';

    public $disableHistory = false;

    /**
     * @var bool if true, grid will start hidden so message view can be displayed in its place
     */
    public $messageView = false;

    /**
     * @var EmailInboxes $mailbox 
     */
    public $mailbox;

    /**
     * @var bool $loadingMailbox set to true if messages are being loaded via ajax on page load
     */
    public $loadingMailbox = false; 

    public function renderEmptyText () {
        echo '<span class="empty-text-progress-bar"></span>';
        parent::renderEmptyText (); // uses position: absolute
        // uses position: static, a kludge to force the container to be the right size
        parent::renderEmptyText (); 
    }

    public function init () {
        if ($this->loadingMailbox) {
            $this->emptyText = Yii::t('emailInboxes', 'Loading messages...');
        }

        $this->columns = array_merge (array (
            array (
                'class' => 'X2CheckBoxColumn',
                'selectableRows' => 2,
                'headerCheckBoxHtmlOptions' => array (
                    'title' => CHtml::encode (Yii::t('emailInboxes', 'Check all')),
                ),
                'htmlOptions' => array (
                    'class' => 'check-box-cell',
                ),
                'checkBoxHtmlOptions' => array (
                    'id' => '"'.$this->namespacePrefix.'C_gvCheckbox_".$data->uid',
                ),
            ),
        ), $this->columns);
        parent::init ();
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'EmailInboxesQtipManager' => array(
                    'baseUrl' => X2WebModule::getAssetsUrlOfModule ('EmailInboxes'),
                    'js' => array(
                        'js/EmailInboxesQtipManager.js',
                    ),
                    'depends' => array ('X2GridViewQtipManager'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function registerClientScript() {
        parent::registerClientScript();
        if($this->enableGvSettings) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                X2WebModule::getAssetsUrlOfModule ('EmailInboxes').
                    '/js/emailInboxesGridSettings.js', CCLientScript::POS_END);
        }
    }

    public function getJSClassOptions () {
        $myInbox = EmailInboxes::model ()->getMyEmailInbox ();
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
                'messageView' => $this->messageView,
                'loadingMailbox' => $this->loadingMailbox,
                'myInboxId' => $myInbox ? $myInbox->id : null,
                'disableHistory' => $this->disableHistory,
                'translations' => array (
                    'tabSettingsDialogTitle' => Yii::t('emailInboxes', 'Inbox Tab Settings'),
                    'Cancel' => Yii::t('emailInboxes', 'Cancel'),
                    'Save' => Yii::t('emailInboxes', 'Save'),
                )
            ));
    }

    /**
     * Renders check all check box 
     */
    public function renderCheckAllButton () {
        $checkboxColumn = $this->columns[0];
        echo $checkboxColumn->getHeaderCellContent ();
    }

    /**
     * Renders back button for message view page 
     */
    public function renderBackButton () {
        echo 
            '<button class="x2-button mailbox-back-button fa fa-arrow-left fa-lg" 
              style="display: none;" 
              title="'.CHtml::encode (Yii::t('emailInboxes', 'Back to index')).'">
            </button>';
    }

    /**
     * Renders inbox refresh button 
     */
    public function renderRefreshButton () {
        echo 
            '<button class="x2-button mailbox-refresh-button fa fa-refresh fa-lg"
              title="'.CHtml::encode (Yii::t('emailInboxes', 'Refresh')).'">
            </button>';
    }

    /**
     * Renders mailbox controls (mass actions, results per page selector, search form, etc.) 
     */
    public function renderMailboxControls () {
        echo '<div class="mailbox-controls grid-top-bar">';
        echo '<div class="bs-row">';
        echo $this->mailbox->renderSearchForm ();
        echo '</div>
            <div class="bs-row">';
        echo $this->renderCheckAllButton ();
        echo $this->renderBackButton ();
        echo $this->renderRefreshButton ();
        echo $this->renderMassActionButtons ();
        echo $this->renderSummary ();
        echo $this->renderTopPager ();
        echo '</div>';
        echo '<div class="clearfix"></div>';
        echo '</div>';
    }

    /**
     * Renders controls for profile widget 
     */
    public function renderMailboxControlsCompact () {
        echo $this->renderCheckAllButton ();
        echo $this->renderBackButton ();
        echo $this->renderRefreshButton ();
        echo $this->renderMassActionButtons ();
        echo $this->renderSummary ();
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

        /* x2modstart */ // pager always rendered
        echo '<div class="'.$this->pagerCssClass.
            ($pager['pages']->getPageCount() <= 1 ? ' empty-pager' : '').'">';
        $this->widget($class,$pager);
        echo '</div>';
        /* x2modend */ 
	}

    public function renderMailboxTabs () {
        $visibleEmailInboxes = Yii::app()->params->profile->getEmailInboxes ();
        $tabOptions = EmailInboxes::model ()->getTabOptions ();
        echo "<ul id='email-inbox-tabs'>";
        if (count ($visibleEmailInboxes)) {
            foreach ($visibleEmailInboxes as $emailInboxName => $emailInbox) {
                $id = $emailInbox->id;
                $classes = 'email-inbox-tab';
                if ($id === $this->mailbox->id) {
                    $classes .= ' selected-tab';
                }
                $title = $emailInbox->credentials ?
                    $emailInbox->credentials->auth->email : '';
                echo "
                <li class='$classes' 
                 title='".CHtml::encode ($title)."'>
                    <a id='email-inbox-tab-$id' data-id='$id' href='#'>".
                        CHtml::encode ($emailInboxName).
                    "</a>
                </li>
                ";
            }
        } else {
        echo 
            "<li class='email-inbox-tab selected-tab'>
                <a id='email-inbox-tab-null' data-id='null' href='#'>".
                    CHtml::encode (Yii::t('emailInboxes', 'My Inbox')).
                "</a>
            </li>";
        }
        echo "
            <li class='email-inbox-tab' id='email-inbox-tab-plus' 
             title='".CHtml::encode (Yii::t('emailInboxes', 'Add an Inbox'))."'>
                <a href='#'>+</a>
            </li>
        </ul>";

        echo 
            "<div id='email-inbox-tab-settings-dialog' class='form' style='display: none;'>
                <label for='Profile[emailInboxes]'>".
                    CHtml::encode (Yii::t('emailInboxes', 'Visible tabs:')).
                "</label>".
                  CHtml::dropDownList(
                    'Profile[emailInboxes]',
                    array_map (function ($inbox) {
                        return $inbox->id;
                    }, $visibleEmailInboxes),
                    $tabOptions,
                    array(
                        'class'=>'email-inbox-tabs-multiselect',
                        'multiple'=>'multiple',
                        'size'=>8
                    ))."
            </div>";
    }

    public function setSummaryText () {

        /* add a dropdown to the summary text that let's user set how many rows to show on each 
           page */
        $this->summaryText =  Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{emailCount}</b>',
            array (
                '{emailCount}' => $this->emailCount,
            ));
        /*.'<div class="form no-border" style="display:inline;"> '
        .CHtml::dropDownList(
            'resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(),
            array(
                'ajax' => array(
                    'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                    'data' => 'js:{results:$(this).val()}',
                    'complete' => 'function(response) { 
                        $.fn.yiiGridView.update("'.$this->id.'"); 
                    }',
                ),
                'id' => 'resultsPerPage'.$this->id,
                'style' => 'margin: 0;',
                'class' => 'x2-select resultsPerPage',
            )
        ).'</div>';*/
    }

    public function setPager () {
        $this->pager = array (
            'class' => $this->pagerClass, 
            'header' => '',
            'htmlOptions' => array (
                'id' => $this->namespacePrefix . 'Pager'
            ),
            'firstPageCssClass' => '',
            'lastPageCssClass' => '',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'firstPageLabel' => '',
            'lastPageLabel' => '',
            'maxButtonCount' => 0,
        );
    }


}

?>
