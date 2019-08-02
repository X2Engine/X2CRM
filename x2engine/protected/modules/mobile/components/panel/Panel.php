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




Yii::import ('application.modules.mobile.components.panel.*');
Yii::import ('application.modules.mobile.components.*');

class Panel extends X2Widget {

    private $_packages;
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'PanelJS' => array(
                    'baseUrl' => Yii::app()->controller->assetsUrl,
                    'js' => array(
                        'js/Panel.js',
                    ),
                ),
            ));
        }
        return $this->_packages;
    }

    public function getModuleItems () {
        $modules = MobileModule::supportedModules ();
        $modules = array_filter ($modules, function ($module) {
             
            if ($module->name === 'charts') {
                $action = 'ReportsChartDashboard';
            } else if ($module->name === 'users') {
                $action = 'ProfileMobileIndex';
            } else {
                $action = ucfirst ($module->name).'Index';
            }
            $authItem = Yii::app()->authManager->getAuthItem ($action);
            return Yii::app()->params->isAdmin || 
                is_null ($authItem) || Yii::app()->user->checkAccess ($action);
	});

        $items = array_map (function ($module) {
            $item = new ModulePanelItem;
            $item->module = $module;
            return $item;
	}, $modules);

	

	return $items;
    }

    public function getRecentItems ($pageSize=1) {
        $dataProvider = MobileRecentItems::getDataProvider ($pageSize);
        $data = array_map (function ($record) {
            $item = new RecentItemPanelItem;
            $item->model = $record['model'];
            return $item;
        }, $dataProvider->getData ());
        if (count ($data) < count (MobileRecentItems::getRecentItems ())) {
            $item = new RecentItemPanelItem;
            $item->model = 'more';
            $data[] = $item;
        }
        return $data;
    }

    public function getPages () {
        return array (
            'modules' => $this->getModuleItems (),
            'recentItems' => $this->getRecentItems (),
	    'auxiliary' => array (
                Yii::createComponent (array (
                    'class' => 'PanelItem',
                    'title' => Yii::t('app', 'Settings'),
                    'id' => 'settings',
                    'isSelected' => preg_match (
                        '/mobile\/settings$/', Yii::app()->request->pathInfo),
                    'href' => Yii::app()->createAbsoluteUrl ('/mobile/settings'),
                )),
                Yii::createComponent (array (
                    'class' => 'PanelItem',
                    'title' => Yii::t('app', 'About'),
                    'id' => 'about',
                    'isSelected' => preg_match ('/mobile\/about$/', Yii::app()->request->pathInfo),
                    'href' => Yii::app()->createAbsoluteUrl ('/mobile/about'),
                )),
                Yii::createComponent (array (
                    'class' => 'PanelItem',
                    'title' => Yii::t('app', 'Log Out'),
                    //'linkHtmlOptions' => array (
                        //'rel' => 'external',
                    //),
                    'href' => Yii::app()->createAbsoluteUrl ('/mobile/logout'),
                    'id' => 'logOut',
                    'linkHtmlOptions' => array (
                        'class' => 'logout-button'
                    )
                )),
            )
        );
    }

    public function renderSectionTitle ($section) {
        $html = '';
        switch ($section) {
            case 'modules':
                break;
            case 'auxiliary':
                $html .= "<li data-role='list-divider'></li>";
                break;
            case 'recentItems':
                $html .= 
                    "<li data-role='list-divider'  
                      class='panel-recent-item ui-li-divider ui-bar-inherit'>".
                        CHtml::encode (Yii::t('app', 'RECENT')). 
                    "</li>";
                break;
        }
        return $html;
    }

    public function renderItems ($filter=null) {
        $pages = $this->getPages ();
        if ($filter) {
            $sections = array_filter (array_keys ($pages), $filter);
            $pages = array_intersect_key ($pages, array_flip ($sections));
        }

        $html = '';
        foreach ($pages as $section => $items) {
            $html .= $this->renderSectionTitle ($section);
            foreach ($items as $item) {
                $html .= $item->render ();
            }
        }
        return $html;
    }

    public function run () {
        parent::run ();
        $this->registerPackages (); 
        $this->render ('application.modules.mobile.views.mobile._panel');
    }

}

?>
