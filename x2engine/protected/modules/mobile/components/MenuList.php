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




/**
 * @package application.modules.mobile.components 
 */
class MenuList extends X2Widget {

    /**
     * @var array list of menu items. Each menu item is specified as an array of name-value pairs.
     * Possible option names include the following:
     * <ul>
     * <li>label: string, optional, specifies the menu item label. When {@link encodeLabel} is true, the label
     * will be HTML-encoded. If the label is not specified, it defaults to an empty string.</li>
     * <li>url: string or array, optional, specifies the URL of the menu item. It is passed to {@link CHtml::normalizeUrl}
     * to generate a valid URL. If this is not set, the menu item will be rendered as a span text.</li>
     * <li>active: boolean, optional, whether this menu item is in active state (currently selected).
     * </ul>
     */
    public $items = array();

    /**
     * @var string the HTML element name that will be used to wrap the label of all menu links.
     * For example, if this property is set as 'span', a menu item may be rendered as
     * &lt;li&gt;&lt;a href="url"&gt;&lt;span&gt;label&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;
     * This is useful when implementing menu items using the sliding window technique.
     * Defaults to null, meaning no wrapper tag will be generated.
     * @since 0.6
     */
    public $linkLabelWrapper;
    private $rightOptions = array('data-role' => 'button', 'data-icon' => 'arrow-r', 'data-iconpos' => 'right', );
    private $leftOptions = array('data-role' => 'button', 'data-icon' => 'arrow-l', 'data-iconpos' => 'left', 'data-direction' => "reverse" );
    private $cs;

    public function init() {
        parent::init();
        $this->cs=Yii::app()->clientScript;
    }

    /**
     * Calls {@link renderMenu} to render the menu.
     */
    public function run() {
        $this->renderMenu($this->items);
    }

    /**
     * Renders the menu items.
     * @param array $items menu items. Each menu item will be an array with at least two elements: 'label' and 'url'.
     * It may have one other optional element: 'active'.
     * @since 0.6
     */
    protected function renderMenu($items) {
        if (count($items)) {
            echo CHtml::openTag('div') . "\n";
            $this->renderMenuRecursive($items);
            echo CHtml::closeTag('div');
        }
    }

    protected function renderMenuRecursive($items) {
        $n = count($items);
        foreach ($items as $item) {
            $active = isset($item['active']) ? $item['active'] : true;
            if ($active) {
                echo CHtml::openTag('p');
                $menu = $this->renderMenuItem($item);
                echo $menu;
                echo CHtml::closeTag('p') . "\n";
            }
        }
    }

    /**
     * Renders the content of a menu item.
     * Note that the container and the sub-menus are not rendered here.
     * @param array $item the menu item to be rendered. Please see {@link items} on what data might be in the item.
     * @since 0.6
     */
    protected function renderMenuItem($item) {
        if (isset($item['url'])) {
            $url = $item['url'];
            
            $params = array();
            if (!is_string($url)) {
                $route = $url[0];
                $params = array_splice($url,1);
            }
            // absolute url needed for phonegap app
            $route = $this->getController()->createAbsoluteUrl($route,$params);
            //Yii::trace('url|route='.(is_string ($url) ? $url : 'Array').'|'.$route); 
            $label = $this->linkLabelWrapper === null ? $item['label'] : '<' . $this->linkLabelWrapper . '>' . $item['label'] . '</' . $this->linkLabelWrapper . '>';
            $options = X2Html::mergeHtmlOptions (
                isset ($item['left']) ? $this->leftOptions : $this->rightOptions,
                isset ($item['linkOptions']) ? $item['linkOptions'] : array ());
            return CHtml::link($label, $route, $options);
        }
    }

}

?>
