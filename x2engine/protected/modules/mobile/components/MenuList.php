<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * @package X2CRM.modules.mobile.components 
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
            $route = $this->getController()->createUrl($route,$params);
            Yii::trace('url|route='.$url.'|'.$route);
            $label = $this->linkLabelWrapper === null ? $item['label'] : '<' . $this->linkLabelWrapper . '>' . $item['label'] . '</' . $this->linkLabelWrapper . '>';
        if (isset($item['left'])) 
            return CHtml::link($label, $route, $this->leftOptions);
            else
            return CHtml::link($label, $route, $this->rightOptions);
        }
    }

}

?>