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




class EmailInboxesPagination extends CPagination {

    /**
     * @var CDataProvider $dataProvider
     */
    public $dataProvider; 

    /**
     * @var int $messageCount
     */
    public $messageCount; 

    public function getPageCount () {
        return (int) (
            ($this->messageCount + EmailInboxes::OVERVIEW_PAGE_SIZE - 1) / 
            EmailInboxes::OVERVIEW_PAGE_SIZE);
    }

    public function addLastUidParam (&$params) {
        $currPage = $this->dataProvider->getData ();
        if (count ($currPage)) {
            $lastItem = array_pop ($currPage);
            $params['lastUid'] = $lastItem->uid;
        }
    }

    public function addFirstUidParam (&$params) {
        $currPage = $this->dataProvider->getData ();
        if (count ($currPage)) {
            $lastItem = array_shift ($currPage);
            $params['lastUid'] = $lastItem->uid;
        }
    }
    
    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/  
     */
    public function createPageUrl ($controller, $page/* x2modstart */, $next=false/* x2modend */) {
        $params=$this->params===null ? $_GET : $this->params;
        if($page>0) // page 0 is the default
            $params[$this->pageVar]=$page+1;
        else
            unset($params[$this->pageVar]);
        /* x2modstart */     
        if ($next)
            $this->addLastUidParam ($params);
        else
            $this->addFirstUidParam ($params);
        /* x2modend */ 
        return $controller->createUrl($this->route,$params);
    }

}

?>
