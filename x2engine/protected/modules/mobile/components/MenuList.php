<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

class MenuList extends CWidget {

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
    private $leftOptions = array('data-role' => 'button', 'data-icon' => 'arrow-l', 'data-iconpos' => 'left', );
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
            if (!is_string($url))
                $url = $url[0];
            $route = $this->getController()->createUrl($url);
            $route=$route.'/';
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