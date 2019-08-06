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




class RowsAndColumnsReportSort extends CSort {

    /**
     * Names of attributes which are sorted and their sort direction
     * @var array $sortOrders
     */
    public $sortOrders; 

    public $multiSort = true; 

    /**
     * Allows sort links to be generated based on public sortOrders property, instead 
     * of GET parameters. {@link X2RowsAndColumnsReport} handles sorting, so sort order GET 
     * parameter is not set.
     */
    public function getPresetDirections () {
        $presetDirections = array ();
        foreach ($this->sortOrders as $attr => $direction) {
            $presetDirections[$attr] = $direction === 'desc' ? self::SORT_DESC : self::SORT_ASC;
        }
        return $presetDirections;
    }

	/**
	 * Generates a hyperlink that can be clicked to cause sorting.
	 * @param string $attribute the attribute name. This must be the actual attribute name, not 
     * alias.
	 * If it is an attribute of a related AR object, the name should be prefixed with
	 * the relation name (e.g. 'author.name', where 'author' is the relation name).
	 * @param string $label the link label. If null, the label will be determined according
	 * to the attribute (see {@link resolveLabel}).
	 * @param array $htmlOptions additional HTML attributes for the hyperlink tag
	 * @return string the generated hyperlink
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function link($attribute,$label=null,$htmlOptions=array())
	{
		if($label===null)
			$label=$this->resolveLabel($attribute);
		if(($definition=$this->resolveAttribute($attribute))===false)
			return $label;
        /* x2modstart */ 
		$directions=$this->getPresetDirections();
        /* x2modend */ 
		if(isset($directions[$attribute]))
		{
			$class=$directions[$attribute] ? 'desc' : 'asc';
			if(isset($htmlOptions['class']))
				$htmlOptions['class'].=' '.$class;
			else
				$htmlOptions['class']=$class;
			$descending=!$directions[$attribute];
			unset($directions[$attribute]);
		}
		elseif(is_array($definition) && isset($definition['default']))
			$descending=$definition['default']==='desc';
		else
			$descending=false;

		if($this->multiSort) {
            /* x2modstart */ 
            // switched order of arguments so that new sort order comes last
			$directions=array_merge($directions, array($attribute=>$descending));
            /* x2modend */ 
		} else {
			$directions=array($attribute=>$descending);
        }

		$url=$this->createUrl(Yii::app()->getController(),$directions);

		return $this->createLink($attribute,$label,$url,$htmlOptions);
	}

    /**
     * Parses sort order formatted with separators property
     * @return array attributes to sort on and direction
     */
    public static function parseSortOrders ($sortOrder, $separators) {
        $sortOrders = explode ($separators[0], $sortOrder);
        $parsed = array ();
        foreach ($sortOrders as $order) {
            $pieces = explode ($separators[1], $order);
            if (count ($pieces) === 1) $pieces[] = 'asc';
            $parsed[$pieces[0]] = $pieces[1];
        }
        return $parsed;
    }

}

?>
