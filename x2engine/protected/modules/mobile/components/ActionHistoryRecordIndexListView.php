<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

Yii::import ('zii.widgets.CListView');

class ActionHistoryRecordIndexListView extends CListView {

    public function renderMoreButton () {
        $pager = Yii::createComponent (array (
            'class' => 
                Yii::app()->controller->pathAliasBase.'components.MobileRecordIndexPager',
            'pages' => $this->dataProvider->getPagination ()
        ));
        $currentPage = $pager->getCurrentPage (false);
        $pageCount = $pager->getPageCount ();
        //$href = $pager->createPageUrl ($currentPage + 1),
        //$href = UrlUtil::mergeParams (Yii::app()->request->url, array (
        //));
        if ($currentPage + 1 < $pageCount) {
            $trueUrl = $pager->createPageUrl ($currentPage + 1);
            $splitUrl =  explode("page=", $trueUrl);
            $pageNum = $splitUrl[1];
            $newUrl = Yii::app()->createAbsoluteUrl ('contacts/mobileView',
            array('id'=>$this->modelid,));
            $newUrl .= "?page=";
            $newUrl .= $pageNum;
            //$newUrl = $pager->createPageUrl ($currentPage + 1);
            $html = CHtml::openTag ('a', array (
                'href' => $newUrl,
                'class' => 'more-button record-list-item' 
            ));
            $html .= '<div class="record-list-item " >
                <div class="icon-container">
                    <div class="fa fa-ellipsis-h">
                    <div class="stacked-icon"></div></div>
                </div>
                <div class="history-item-content-container-outer">
                    <div class="history-item-content" > 
                 
                    </div>
                    <div class=" history-item-date-line"> '.
                        '<span>'.CHtml::encode (Yii::t('app', 'Next Page')).'</span>'.'
                    </div>
                    <div class="history-item-author" > 
                      
                    </div>
                </div>
        </div>';
            $html .= "</a>";
            echo $html;
        }
    }

}

?>
